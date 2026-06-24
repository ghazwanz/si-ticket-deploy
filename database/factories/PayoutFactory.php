<?php

namespace Database\Factories;

use App\Enums\PayoutStatus;
use App\Enums\PayoutType;
use App\Models\Event;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayoutFactory extends Factory
{
    protected $model = Payout::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(PayoutStatus::cases());
        $grossAmount = $this->faker->numberBetween(5000000, 50000000);
        $feePercentage = 10.00;
        $platformFee = (int) ($grossAmount * $feePercentage / 100);

        return [
            'event_id' => Event::factory(),
            'organizer_id' => User::factory()->organizer(),
            'payout_type' => PayoutType::Final,
            'advance_sequence' => null,
            'gross_amount' => $grossAmount,
            'fee_percentage' => $feePercentage,
            'platform_fee' => $platformFee,
            'net_amount' => $grossAmount - $platformFee,
            'requested_amount' => null,
            'approved_amount' => null,
            'reason' => null,
            'rejection_reason' => null,
            'status' => $status,
            'payout_bank_name' => $this->faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'payout_account_holder' => $this->faker->name(),
            'payout_account_number' => $this->faker->bankAccountNumber(),
            'missing_bank_details' => false,
            'manual_settlement_required' => false,
            'reviewed_by' => ! in_array($status, [PayoutStatus::Pending]) ? User::factory()->admin() : null,
            'reviewed_at' => ! in_array($status, [PayoutStatus::Pending]) ? now() : null,
            'disbursed_by' => $status === PayoutStatus::Completed ? User::factory()->admin() : null,
            'disbursed_at' => $status === PayoutStatus::Completed ? now() : null,
            'transfer_reference' => $status === PayoutStatus::Completed ? 'REF-'.rand(1000, 9999) : null,
            'proof_photo' => $status === PayoutStatus::Completed ? 'payouts/proofs/dummy.jpg' : null,
        ];
    }

    /**
     * Set payout as pending review.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PayoutStatus::Pending,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'disbursed_by' => null,
            'disbursed_at' => null,
            'transfer_reference' => null,
            'proof_photo' => null,
        ]);
    }

    /**
     * Set payout as processing (reviewed, awaiting disbursement).
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PayoutStatus::Processing,
            'reviewed_by' => User::factory()->admin(),
            'reviewed_at' => now(),
            'disbursed_by' => null,
            'disbursed_at' => null,
            'transfer_reference' => null,
            'proof_photo' => null,
        ]);
    }

    /**
     * Set payout as completed (fully disbursed).
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PayoutStatus::Completed,
            'reviewed_by' => User::factory()->admin(),
            'reviewed_at' => now()->subDay(),
            'disbursed_by' => User::factory()->admin(),
            'disbursed_at' => now(),
            'transfer_reference' => 'REF-'.rand(1000, 9999),
            'proof_photo' => 'payouts/proofs/dummy.jpg',
        ]);
    }

    /**
     * Set payout as voided (event cancelled).
     */
    public function voided(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PayoutStatus::Voided,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'disbursed_by' => null,
            'disbursed_at' => null,
            'transfer_reference' => null,
            'proof_photo' => null,
        ]);
    }

    /**
     * Set payout as advance payout.
     */
    public function advance(): static
    {
        return $this->state(fn (array $attributes) => [
            'payout_type' => PayoutType::Advance,
            'advance_sequence' => 1,
            'requested_amount' => 2000000,
            'reason' => 'Untuk biaya operasional sewa soundsystem.',
        ]);
    }

    /**
     * Set payout as rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PayoutStatus::Rejected,
            'rejection_reason' => 'Dokumen pendukung pengajuan kurang lengkap.',
            'reviewed_by' => User::factory()->admin(),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Set payout as manual settlement required.
     */
    public function manualSettlement(): static
    {
        return $this->state(fn (array $attributes) => [
            'manual_settlement_required' => true,
        ]);
    }
}
