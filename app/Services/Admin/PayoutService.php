<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\EventStatus;
use App\Enums\PayoutStatus;
use App\Enums\PayoutType;
use App\Models\Event;
use App\Models\Payout;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\AdvancePayoutApprovedNotification;
use App\Notifications\AdvancePayoutRejectedNotification;
use App\Notifications\FinalPayoutDisbursedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PayoutService
{
    /**
     * Get a paginated list of payouts with filters, search, and sorting.
     */
    public function getPaginatedPayouts(array $filters): LengthAwarePaginator
    {
        $status = $filters['status'] ?? null;
        $payoutType = $filters['payout_type'] ?? null;
        $search = $filters['search'] ?? null;
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';

        return Payout::with(['event', 'organizer.organizerProfile'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($payoutType, fn ($query) => $query->where('payout_type', $payoutType))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('event', function ($eq) use ($search) {
                        $eq->where('name', 'like', "%{$search}%");
                    })
                        ->orWhereHas('organizer', function ($oq) use ($search) {
                            $oq->where('name', 'like', "%{$search}%")
                                ->orWhereHas('organizerProfile', function ($opq) use ($search) {
                                    $opq->where('organization_name', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when(in_array($sort, ['gross_amount', 'net_amount', 'status', 'created_at']), function ($query) use ($sort, $order) {
                return $query->orderBy($sort, $order === 'asc' ? 'asc' : 'desc');
            }, function ($query) {
                return $query->latest();
            })
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Initialize final payout for a completed event.
     */
    public function initializeFinalPayout(Event $event): Payout
    {
        if ($event->status !== EventStatus::Completed && $event->status !== 'completed') {
            throw new InvalidArgumentException('Payout can only be initialized for completed events.');
        }

        // Check if final payout already exists
        $hasFinalPayout = $event->payouts()
            ->where('payout_type', PayoutType::Final)
            ->exists();
        if ($hasFinalPayout) {
            throw new InvalidArgumentException('A final payout record already exists for this event.');
        }

        return DB::transaction(function () use ($event) {
            // Calculate Gross Revenue (Sum of all paid orders)
            $grossAmount = $event->orders()->where('status', 'paid')->sum('total_amount');

            // Default platform fee percentage from database configuration
            $feePercentage = (float) SystemSetting::get('platform_fee_percent', 5.00);
            $platformFee = (int) round($grossAmount * ($feePercentage / 100));
            $netSales = $grossAmount - $platformFee;

            // Deduct completed advance payouts from net sales
            $completedAdvanceTotal = (int) $event->payouts()
                ->where('payout_type', PayoutType::Advance)
                ->where('status', PayoutStatus::Completed)
                ->sum('approved_amount');

            $netAmount = $netSales - $completedAdvanceTotal;
            if ($netAmount < 0) {
                $netAmount = 0;
            }

            // Snapshot Organizer's Bank Details
            $organizerProfile = $event->organizer->organizerProfile;
            $missingBankDetails = empty($organizerProfile?->bank_name) || empty($organizerProfile?->bank_account_number);

            // If net_amount is 0 or less, auto-complete the payout as nothing is left to disburse
            $status = $netAmount <= 0 ? PayoutStatus::Completed : PayoutStatus::Pending;
            $transferReference = $netAmount <= 0 ? 'AUTO_DEDUCTED_BY_ADVANCE' : null;

            $payout = Payout::create([
                'event_id' => $event->id,
                'organizer_id' => $event->organizer_id,
                'payout_type' => PayoutType::Final,
                'gross_amount' => $grossAmount,
                'platform_fee' => $platformFee,
                'net_amount' => $netAmount,
                'fee_percentage' => $feePercentage,
                'payout_bank_name' => $organizerProfile?->bank_name,
                'payout_account_number' => $organizerProfile?->bank_account_number,
                'payout_account_holder' => $organizerProfile?->bank_account_name,
                'missing_bank_details' => $missingBankDetails,
                'status' => $status,
                'transfer_reference' => $transferReference,
                'disbursed_at' => $netAmount <= 0 ? now() : null,
            ]);

            return $payout;
        });
    }

    /**
     * Approve advance payout (Step 1).
     */
    public function approveAdvancePayout(Payout $payout, User $admin, int $approvedAmount): Payout
    {
        if ($payout->payout_type !== PayoutType::Advance) {
            throw new InvalidArgumentException('Only advance payouts can be approved via this method.');
        }

        if ($payout->status !== PayoutStatus::Pending) {
            throw new InvalidArgumentException('Only pending advance payouts can be approved.');
        }

        $summary = $this->getAdvanceSummary($payout->event);
        if ($approvedAmount > $summary['available_advance_amount']) {
            throw new InvalidArgumentException('Approved amount exceeds the maximum available advance payout limit.');
        }

        return DB::transaction(function () use ($payout, $admin, $approvedAmount) {
            $payout->update([
                'status' => PayoutStatus::Processing,
                'approved_amount' => $approvedAmount,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            // Notify Organizer
            $payout->organizer->notify(new AdvancePayoutApprovedNotification($payout));

            return $payout;
        });
    }

    /**
     * Reject advance payout.
     */
    public function rejectAdvancePayout(Payout $payout, User $admin, string $rejectionReason): Payout
    {
        if ($payout->payout_type !== PayoutType::Advance) {
            throw new InvalidArgumentException('Only advance payouts can be rejected via this method.');
        }

        if ($payout->status !== PayoutStatus::Pending) {
            throw new InvalidArgumentException('Only pending advance payouts can be rejected.');
        }

        $payout->update([
            'status' => PayoutStatus::Rejected,
            'rejection_reason' => $rejectionReason,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // Notify Organizer
        $payout->organizer->notify(new AdvancePayoutRejectedNotification($payout));

        return $payout;
    }

    /**
     * Get financial and advance payout summary for an event.
     */
    public function getAdvanceSummary(Event $event): array
    {
        $feePercentage = (float) SystemSetting::get('platform_fee_percent', 5.00);
        $advanceLimitPercent = config('payouts.advance_limit_percent', 40.00);

        $grossSales = (int) $event->orders()->where('status', 'paid')->sum('total_amount');
        $estimatedPlatformFee = (int) round($grossSales * ($feePercentage / 100));
        $estimatedNetSales = $grossSales - $estimatedPlatformFee;

        $maxAdvanceTotal = (int) round($estimatedNetSales * ($advanceLimitPercent / 100));

        $completedAdvanceTotal = (int) $event->payouts()
            ->where('payout_type', PayoutType::Advance)
            ->where('status', PayoutStatus::Completed)
            ->sum('approved_amount');

        $availableAdvanceAmount = $maxAdvanceTotal - $completedAdvanceTotal;
        if ($availableAdvanceAmount < 0) {
            $availableAdvanceAmount = 0;
        }

        $latestAdvance = $event->payouts()
            ->where('payout_type', PayoutType::Advance)
            ->latest()
            ->first();

        return [
            'gross_sales' => $grossSales,
            'estimated_platform_fee' => $estimatedPlatformFee,
            'estimated_net_sales' => $estimatedNetSales,
            'max_advance_limit' => $maxAdvanceTotal,
            'completed_advance_total' => $completedAdvanceTotal,
            'available_advance_amount' => $availableAdvanceAmount,
            'fee_percentage' => $feePercentage,
            'advance_limit_percent' => $advanceLimitPercent,
            'latest_advance' => $latestAdvance,
        ];
    }

    /**
     * Step 1: Approve Final Payout for Disbursement.
     */
    public function approvePayout(Payout $payout, User $admin): Payout
    {
        if ($payout->status !== PayoutStatus::Pending) {
            throw new InvalidArgumentException('Only pending payouts can be approved.');
        }

        if ($payout->missing_bank_details) {
            throw new InvalidArgumentException('Cannot approve payout with missing bank details.');
        }

        return DB::transaction(function () use ($payout, $admin) {
            $payout->update([
                'status' => PayoutStatus::Processing,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            return $payout;
        });
    }

    /**
     * Step 2: Disburse Payout (Confirm completed with manual proof upload).
     */
    public function disbursePayout(Payout $payout, User $admin, UploadedFile $proofPhoto, string $reference): Payout
    {
        if ($payout->status !== PayoutStatus::Processing) {
            throw new InvalidArgumentException('Hanya payout dengan status processing yang dapat dicairkan.');
        }

        return DB::transaction(function () use ($payout, $admin, $proofPhoto, $reference) {
            // Store proof photo on private local disk
            $path = $proofPhoto->store('payouts/proofs', 'local');

            $payout->update([
                'status' => PayoutStatus::Completed,
                'transfer_reference' => $reference,
                'proof_photo' => $path,
                'disbursed_by' => $admin->id,
                'disbursed_at' => now(),
            ]);

            if ($payout->isFinal()) {
                $payout->organizer->notify(new FinalPayoutDisbursedNotification($payout));
            }

            return $payout;
        });
    }
}
