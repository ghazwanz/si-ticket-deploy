<?php

declare(strict_types=1);

namespace Tests\Feature\Organizer;

use App\Enums\EventStatus;
use App\Enums\OrganizerStatus;
use App\Enums\PayoutStatus;
use App\Enums\PayoutType;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Order;
use App\Models\Payout;
use App\Models\User;
use App\Notifications\AdvancePayoutRequestedNotification;
use App\Notifications\FinalPayoutRequestedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class AdvancePayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_can_request_advance_for_eligible_event(): void
    {
        Notification::fake();

        $organizer = User::factory()->organizer()->create();
        User::factory()->admin()->create();

        // Setup bank details on organizer profile
        $organizer->organizerProfile()->create([
            'organization_name' => 'JoinFest Org',
            'phone' => '08123456789',
            'organization_address' => 'Jl. Test No. 123',
            'official_contact' => 'test@joinfest.com',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'JoinFest Org Account',
            'status' => OrganizerStatus::Approved,
        ]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5),
            'start_time' => '10:00:00',
        ]);

        // Create paid order of Rp 1.000.000
        Order::factory()->create([
            'event_id' => $event->id,
            'status' => 'paid',
            'total_amount' => 1000000,
        ]);

        // Net sales: Rp 1.000.000 - 5% = Rp 950.000
        // Max limit (40%): Rp 380.000
        $response = $this->actingAs($organizer)
            ->post(route('organizer.payouts.request', $event), [
                'amount' => 300000,
                'reason' => 'Untuk biaya operasional sewa soundsystem.',
            ]);

        $response->assertRedirect(route('organizer.payouts.show', $event));
        $response->assertSessionHas('success');

        $payout = Payout::where('event_id', $event->id)->first();
        $this->assertNotNull($payout);
        $this->assertEquals(PayoutType::Advance, $payout->payout_type);
        $this->assertEquals(300000, $payout->requested_amount);
        $this->assertEquals(PayoutStatus::Pending, $payout->status);
        $this->assertEquals('Untuk biaya operasional sewa soundsystem.', $payout->reason);

        // Check snapshot bank details
        $this->assertEquals('BCA', $payout->payout_bank_name);
        $this->assertEquals('1234567890', $payout->payout_account_number);
        $this->assertEquals('JoinFest Org Account', $payout->payout_account_holder);

        Notification::assertSentTo(
            User::where('role', UserRole::Admin)->get(),
            AdvancePayoutRequestedNotification::class
        );
    }

    public function test_organizer_cannot_request_advance_exceeding_limit(): void
    {
        $organizer = User::factory()->organizer()->create();
        $organizer->organizerProfile()->create([
            'organization_name' => 'JoinFest Org',
            'phone' => '08123456789',
            'organization_address' => 'Jl. Test No. 123',
            'official_contact' => 'test@joinfest.com',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'JoinFest Org Account',
            'status' => OrganizerStatus::Approved,
        ]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5),
            'start_time' => '10:00:00',
        ]);

        Order::factory()->create([
            'event_id' => $event->id,
            'status' => 'paid',
            'total_amount' => 1000000,
        ]);

        // Net sales: Rp 950.000, Max limit: Rp 380.000. Request Rp 390.000
        $response = $this->actingAs($organizer)
            ->post(route('organizer.payouts.request', $event), [
                'amount' => 390000,
                'reason' => 'Untuk biaya operasional sewa soundsystem.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNull(Payout::where('event_id', $event->id)->first());
    }

    public function test_organizer_cannot_request_when_no_sales(): void
    {
        $organizer = User::factory()->organizer()->create();
        $organizer->organizerProfile()->create([
            'organization_name' => 'JoinFest Org',
            'phone' => '08123456789',
            'organization_address' => 'Jl. Test No. 123',
            'official_contact' => 'test@joinfest.com',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'JoinFest Org Account',
            'status' => OrganizerStatus::Approved,
        ]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5),
            'start_time' => '10:00:00',
        ]);

        // No paid orders, request advance
        $response = $this->actingAs($organizer)
            ->post(route('organizer.payouts.request', $event), [
                'amount' => 100000,
                'reason' => 'Untuk biaya operasional sewa soundsystem.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_organizer_cannot_request_when_validation_fails(): void
    {
        $organizer = User::factory()->organizer()->create();
        $organizer->organizerProfile()->create([
            'organization_name' => 'JoinFest Org',
            'phone' => '08123456789',
            'organization_address' => 'Jl. Test No. 123',
            'official_contact' => 'test@joinfest.com',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'JoinFest Org Account',
            'status' => OrganizerStatus::Approved,
        ]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5),
            'start_time' => '10:00:00',
        ]);

        Order::factory()->create([
            'event_id' => $event->id,
            'status' => 'paid',
            'total_amount' => 1000000,
        ]);

        // Reason is less than 20 characters
        $response = $this->actingAs($organizer)
            ->post(route('organizer.payouts.request', $event), [
                'amount' => 100000,
                'reason' => 'Sewa sound',
            ]);

        $response->assertSessionHasErrors(['reason']);
    }

    public function test_updating_bank_details_in_settings_clears_missing_bank_details_flag_and_updates_payouts(): void
    {
        $organizer = User::factory()->organizer()->create();

        // Start with missing bank details in profile
        $organizer->organizerProfile()->create([
            'organization_name' => 'JoinFest Org',
            'phone' => '08123456789',
            'organization_address' => 'Jl. Test No. 123',
            'official_contact' => 'test@joinfest.com',
            'bank_name' => '',
            'bank_account_number' => '',
            'bank_account_name' => '',
            'status' => OrganizerStatus::Approved,
        ]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Completed,
        ]);

        // Create a final payout which has missing_bank_details = true
        $payout = Payout::factory()->pending()->create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'payout_type' => PayoutType::Final,
            'missing_bank_details' => true,
            'payout_bank_name' => null,
            'payout_account_number' => null,
            'payout_account_holder' => null,
        ]);

        // Request updating settings
        $response = $this->actingAs($organizer)
            ->put(route('organizer.settings.profile'), [
                'name' => $organizer->name,
                'email' => $organizer->email,
                'organization_name' => 'New JoinFest Org',
                'phone' => '08123456789',
                'organization_address' => 'Jl. Baru No. 456',
                'official_contact' => 'new@joinfest.com',
                'bank_name' => 'BCA',
                'bank_account_number' => '987654321',
                'bank_account_name' => 'New Org Bank Acc',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Check payout is updated
        $payout->refresh();
        $this->assertFalse($payout->missing_bank_details);
        $this->assertEquals('BCA', $payout->payout_bank_name);
        $this->assertEquals('987654321', $payout->payout_account_number);
        $this->assertEquals('New Org Bank Acc', $payout->payout_account_holder);
    }

    public function test_organizer_can_request_final_payout_for_completed_event(): void
    {
        Notification::fake();

        $organizer = User::factory()->organizer()->create();
        User::factory()->admin()->create();

        $organizer->organizerProfile()->create([
            'organization_name' => 'JoinFest Org',
            'phone' => '08123456789',
            'organization_address' => 'Jl. Test No. 123',
            'official_contact' => 'test@joinfest.com',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'JoinFest Org Account',
            'status' => OrganizerStatus::Approved,
        ]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Completed,
        ]);

        // Create paid order of Rp 1.000.000
        Order::factory()->create([
            'event_id' => $event->id,
            'status' => 'paid',
            'total_amount' => 1000000,
        ]);

        // Net sales: Rp 1.000.000 - 5% = Rp 950.000
        $response = $this->actingAs($organizer)
            ->post(route('organizer.payouts.request', $event));

        $response->assertRedirect(route('organizer.payouts.show', $event));
        $response->assertSessionHas('success');

        $payout = Payout::where('event_id', $event->id)->first();
        $this->assertNotNull($payout);
        $this->assertEquals(PayoutType::Final, $payout->payout_type);
        $this->assertEquals(950000, $payout->net_amount);
        $this->assertEquals(PayoutStatus::Pending, $payout->status);

        // Check snapshot bank details
        $this->assertEquals('BCA', $payout->payout_bank_name);
        $this->assertEquals('1234567890', $payout->payout_account_number);
        $this->assertEquals('JoinFest Org Account', $payout->payout_account_holder);

        Notification::assertSentTo(
            User::where('role', UserRole::Admin)->get(),
            FinalPayoutRequestedNotification::class
        );
    }

    public function test_organizer_cannot_request_final_payout_if_already_exists(): void
    {
        $organizer = User::factory()->organizer()->create();

        $organizer->organizerProfile()->create([
            'organization_name' => 'JoinFest Org',
            'phone' => '08123456789',
            'organization_address' => 'Jl. Test No. 123',
            'official_contact' => 'test@joinfest.com',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'JoinFest Org Account',
            'status' => OrganizerStatus::Approved,
        ]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Completed,
        ]);

        // Create an existing pending final payout
        Payout::factory()->pending()->create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'payout_type' => PayoutType::Final,
        ]);

        $response = $this->actingAs($organizer)
            ->post(route('organizer.payouts.request', $event));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_organizer_cannot_request_final_payout_with_missing_bank_details(): void
    {
        $organizer = User::factory()->organizer()->create();

        $organizer->organizerProfile()->create([
            'organization_name' => 'JoinFest Org',
            'phone' => '08123456789',
            'organization_address' => 'Jl. Test No. 123',
            'official_contact' => 'test@joinfest.com',
            'bank_name' => '',
            'bank_account_number' => '',
            'bank_account_name' => '',
            'status' => OrganizerStatus::Approved,
        ]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Completed,
        ]);

        $response = $this->actingAs($organizer)
            ->post(route('organizer.payouts.request', $event));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
