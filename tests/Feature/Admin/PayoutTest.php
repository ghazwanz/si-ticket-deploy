<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\EventStatus;
use App\Enums\PayoutStatus;
use App\Enums\PayoutType;
use App\Models\CancellationRequest;
use App\Models\Event;
use App\Models\Order;
use App\Models\Payout;
use App\Models\TicketCategory;
use App\Models\User;
use App\Notifications\AdvancePayoutApprovedNotification;
use App\Notifications\AdvancePayoutRejectedNotification;
use App\Notifications\FinalPayoutDisbursedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_payout_deducts_completed_advances(): void
    {
        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();

        // Create organizer profile with bank details
        $organizer->organizerProfile()->create([
            'organization_name' => 'JoinFest Org',
            'phone' => '08123456789',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'JoinFest Org Account',
        ]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Completed,
        ]);

        // Create ticket category
        $ticketCategory = TicketCategory::factory()->create([
            'event_id' => $event->id,
            'price' => 100000,
            'quota' => 100,
            'sold_count' => 10,
        ]);

        // Create paid order of Rp 1.000.000 (10 * 100.000)
        Order::factory()->create([
            'event_id' => $event->id,
            'user_id' => User::factory()->create()->id,
            'status' => 'paid',
            'total_amount' => 1000000,
        ]);

        // Create a completed advance payout of Rp 200.000
        Payout::factory()->completed()->create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'payout_type' => PayoutType::Advance,
            'requested_amount' => 2000000, // Rp 2.000.000 dummy
            'approved_amount' => 200000,   // Rp 200.000 actual
        ]);

        // Initialize final payout
        $response = $this->actingAs($admin)
            ->post(route('admin.payouts.initialize', $event));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Net sales: Rp 1.000.000 - 5% fee (Rp 50.000) = Rp 950.000
        // Net final payout: Rp 950.000 - Rp 200.000 (advance) = Rp 750.000
        $finalPayout = Payout::where('event_id', $event->id)
            ->where('payout_type', PayoutType::Final)
            ->first();

        $this->assertNotNull($finalPayout);
        $this->assertEquals(1000000, $finalPayout->gross_amount);
        $this->assertEquals(50000, $finalPayout->platform_fee);
        $this->assertEquals(750000, $finalPayout->net_amount);
        $this->assertEquals(PayoutStatus::Pending, $finalPayout->status);
    }

    public function test_admin_can_approve_advance_payout(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
        ]);

        // Create paid order of Rp 1.000.000
        Order::factory()->create([
            'event_id' => $event->id,
            'status' => 'paid',
            'total_amount' => 1000000,
        ]);

        // Net sales: Rp 1.000.000 - 5% fee = Rp 950.000
        // Max advance limit (40%): Rp 380.000
        $payout = Payout::factory()->pending()->create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'payout_type' => PayoutType::Advance,
            'requested_amount' => 300000,
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.payouts.approve-advance', $payout), [
                'approved_amount' => 250000,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payout->refresh();
        $this->assertEquals(PayoutStatus::Processing, $payout->status);
        $this->assertEquals(250000, $payout->approved_amount);
        $this->assertEquals($admin->id, $payout->reviewed_by);
        $this->assertNotNull($payout->reviewed_at);

        Notification::assertSentTo($organizer, AdvancePayoutApprovedNotification::class);
    }

    public function test_admin_can_reject_advance_payout(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();

        $payout = Payout::factory()->pending()->create([
            'organizer_id' => $organizer->id,
            'payout_type' => PayoutType::Advance,
            'requested_amount' => 300000,
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.payouts.reject-advance', $payout), [
                'rejection_reason' => 'Alasan penolakan pengujian',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payout->refresh();
        $this->assertEquals(PayoutStatus::Rejected, $payout->status);
        $this->assertEquals('Alasan penolakan pengujian', $payout->rejection_reason);
        $this->assertEquals($admin->id, $payout->reviewed_by);
        $this->assertNotNull($payout->reviewed_at);

        Notification::assertSentTo($organizer, AdvancePayoutRejectedNotification::class);
    }

    public function test_cancellation_with_completed_advances(): void
    {
        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5),
            'start_time' => '10:00:00',
        ]);

        // Create completed advance payout
        $completedAdvance = Payout::factory()->completed()->create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'payout_type' => PayoutType::Advance,
            'approved_amount' => 200000,
        ]);

        // Create pending advance payout
        $pendingAdvance = Payout::factory()->pending()->create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'payout_type' => PayoutType::Advance,
        ]);

        // Create cancellation request
        $cancellationRequest = CancellationRequest::create([
            'event_id' => $event->id,
            'requested_by' => $organizer->id,
            'reason' => 'Alasan pembatalan karena force majeure.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.cancellations.approve', $cancellationRequest));

        $response->assertRedirect();

        $event->refresh();
        $this->assertEquals(EventStatus::Cancelled, $event->status);
        $this->assertTrue($event->manual_settlement_required);

        $completedAdvance->refresh();
        $this->assertEquals(PayoutStatus::Completed, $completedAdvance->status);
        $this->assertTrue($completedAdvance->manual_settlement_required);

        $pendingAdvance->refresh();
        $this->assertEquals(PayoutStatus::Voided, $pendingAdvance->status);
    }

    public function test_two_step_final_payout_flow(): void
    {
        Storage::fake('local');
        Notification::fake();

        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();

        $payout = Payout::factory()->pending()->create([
            'organizer_id' => $organizer->id,
            'payout_type' => PayoutType::Final,
        ]);

        // Step 1: Approve for disbursement
        $response = $this->actingAs($admin1)
            ->put(route('admin.payouts.approve', $payout));

        $response->assertRedirect();
        $payout->refresh();
        $this->assertEquals(PayoutStatus::Processing, $payout->status);
        $this->assertEquals($admin1->id, $payout->reviewed_by);

        // Step 2: Confirm disbursement completed (by different admin)
        $file = UploadedFile::fake()->image('proof.jpg');
        $response2 = $this->actingAs($admin2)
            ->post(route('admin.payouts.disburse', $payout), [
                'proof_photo' => $file,
                'transfer_reference' => 'TRF-12345',
            ]);

        $response2->assertRedirect();
        $response2->assertSessionHas('success');

        $payout->refresh();
        $this->assertEquals(PayoutStatus::Completed, $payout->status);
        $this->assertEquals('TRF-12345', $payout->transfer_reference);
        $this->assertNotNull($payout->proof_photo);
        Storage::disk('local')->assertExists($payout->proof_photo);

        Notification::assertSentTo($organizer, FinalPayoutDisbursedNotification::class);
    }

    public function test_same_admin_can_disburse_payout(): void
    {
        Storage::fake('local');

        $admin1 = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();

        $payout = Payout::factory()->pending()->create([
            'organizer_id' => $organizer->id,
            'payout_type' => PayoutType::Final,
        ]);

        // Step 1: Approve for disbursement
        $this->actingAs($admin1)
            ->put(route('admin.payouts.approve', $payout));

        $payout->refresh();

        // Step 2: Disburse by same admin (should succeed now that 4-eyes is removed)
        $file = UploadedFile::fake()->image('proof.jpg');
        $response = $this->actingAs($admin1)
            ->post(route('admin.payouts.disburse', $payout), [
                'proof_photo' => $file,
                'transfer_reference' => 'TRF-12345',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $payout->refresh();
        $this->assertEquals(PayoutStatus::Completed, $payout->status);
        $this->assertNotNull($payout->proof_photo);
        $this->assertEquals($admin1->id, $payout->disbursed_by);
    }
}
