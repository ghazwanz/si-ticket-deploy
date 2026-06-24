<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\CancellationRequestStatus;
use App\Enums\EventStatus;
use App\Enums\PayoutStatus;
use App\Enums\UserRole;
use App\Models\CancellationRequest;
use App\Models\Event;
use App\Models\Order;
use App\Models\Payout;
use App\Models\User;
use App\Notifications\CancellationApprovedNotification;
use App\Notifications\CancellationRejectedNotification;
use App\Notifications\EventCancelledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class CancellationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can view the cancellation requests queue.
     */
    public function test_admin_can_view_cancellation_requests_queue(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create(['organizer_id' => $organizer->id, 'status' => 'awaiting_cancellation']);
        $cancellation = CancellationRequest::factory()->create([
            'event_id' => $event->id,
            'requested_by' => $organizer->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.cancellations.index'));

        $response->assertOk();
        $response->assertSee($cancellation->event->name);
        $response->assertSee($cancellation->requestedBy->name);
    }

    /**
     * Test admin can approve a cancellation request.
     */
    public function test_admin_can_approve_cancellation(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => 'awaiting_cancellation',
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '19:00:00',
        ]);

        $cancellation = CancellationRequest::factory()->create([
            'event_id' => $event->id,
            'requested_by' => $organizer->id,
            'status' => 'pending',
        ]);

        // Create a ticket holder who bought a ticket (status paid)
        $ticketHolder = User::factory()->create(['role' => UserRole::User]);
        Order::factory()->create([
            'event_id' => $event->id,
            'user_id' => $ticketHolder->id,
            'status' => 'paid',
        ]);

        // Create a pending payout to be voided
        $payout = Payout::factory()->create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.cancellations.approve', $cancellation));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Permohonan pembatalan acara berhasil disetujui.');

        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellation->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);

        $this->assertEquals(EventStatus::Cancelled, $event->fresh()->status);
        $this->assertEquals(PayoutStatus::Voided, $payout->fresh()->status);

        Notification::assertSentTo($ticketHolder, EventCancelledNotification::class);
        Notification::assertSentTo($organizer, CancellationApprovedNotification::class);
    }

    /**
     * Test admin can reject a cancellation request.
     */
    public function test_admin_can_reject_cancellation(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => 'awaiting_cancellation',
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '19:00:00',
        ]);

        $cancellation = CancellationRequest::factory()->create([
            'event_id' => $event->id,
            'requested_by' => $organizer->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.cancellations.reject', $cancellation), [
                'rejection_reason' => 'Alasan penolakan pembatalan ini valid dan panjang.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Permohonan pembatalan acara berhasil ditolak.');

        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellation->id,
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'rejection_reason' => 'Alasan penolakan pembatalan ini valid dan panjang.',
        ]);

        $this->assertEquals(EventStatus::Published, $event->fresh()->status);

        Notification::assertSentTo($organizer, CancellationRejectedNotification::class);
    }

    /**
     * Test rejection requires reason.
     */
    public function test_reject_requires_reason(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => 'awaiting_cancellation',
        ]);

        $cancellation = CancellationRequest::factory()->create([
            'event_id' => $event->id,
            'requested_by' => $organizer->id,
            'status' => 'pending',
        ]);

        // Scenario 1: Empty reason
        $response = $this->actingAs($admin)
            ->from(route('admin.cancellations.index'))
            ->put(route('admin.cancellations.reject', $cancellation), [
                'rejection_reason' => '',
            ]);

        $response->assertRedirect(route('admin.cancellations.index'));
        $response->assertSessionHasErrors('rejection_reason');
        $this->assertEquals(CancellationRequestStatus::Pending, $cancellation->fresh()->status);

        // Scenario 2: Too short reason (less than 10 chars)
        $response = $this->actingAs($admin)
            ->from(route('admin.cancellations.index'))
            ->put(route('admin.cancellations.reject', $cancellation), [
                'rejection_reason' => 'Short',
            ]);

        $response->assertRedirect(route('admin.cancellations.index'));
        $response->assertSessionHasErrors('rejection_reason');
        $this->assertEquals(CancellationRequestStatus::Pending, $cancellation->fresh()->status);
    }

    /**
     * Test hard cutoff blocks approval.
     */
    public function test_hard_cutoff_blocks_approval(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);

        // Event date is yesterday
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => 'awaiting_cancellation',
            'event_date' => now()->subDay()->format('Y-m-d'),
            'start_time' => '19:00:00',
        ]);

        $cancellation = CancellationRequest::factory()->create([
            'event_id' => $event->id,
            'requested_by' => $organizer->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.cancellations.approve', $cancellation));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Batas waktu pembatalan acara telah terlampaui.');

        $this->assertEquals(CancellationRequestStatus::Pending, $cancellation->fresh()->status);
        $this->assertEquals(EventStatus::AwaitingCancellation, $event->fresh()->status);
    }

    /**
     * Test hard cutoff blocks rejection.
     */
    public function test_hard_cutoff_blocks_rejection(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);

        // Event date is yesterday
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => 'awaiting_cancellation',
            'event_date' => now()->subDay()->format('Y-m-d'),
            'start_time' => '19:00:00',
        ]);

        $cancellation = CancellationRequest::factory()->create([
            'event_id' => $event->id,
            'requested_by' => $organizer->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.cancellations.reject', $cancellation), [
                'rejection_reason' => 'Alasan penolakan pembatalan ini valid dan panjang.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Batas waktu pembatalan acara telah terlampaui.');

        $this->assertEquals(CancellationRequestStatus::Pending, $cancellation->fresh()->status);
        $this->assertEquals(EventStatus::AwaitingCancellation, $event->fresh()->status);
    }

    /**
     * Test approving a cancellation request voids associated payouts.
     */
    public function test_approving_voids_associated_payouts(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);

        // 1. Pending payout
        $eventPending = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => 'awaiting_cancellation',
            'event_date' => now()->addDays(5)->format('Y-m-d'),
        ]);
        $cancellationPending = CancellationRequest::factory()->create([
            'event_id' => $eventPending->id,
            'requested_by' => $organizer->id,
            'status' => 'pending',
        ]);
        $payoutPending = Payout::factory()->create([
            'event_id' => $eventPending->id,
            'organizer_id' => $organizer->id,
            'status' => 'pending',
        ]);

        // 2. Processing payout
        $eventProcessing = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => 'awaiting_cancellation',
            'event_date' => now()->addDays(5)->format('Y-m-d'),
        ]);
        $cancellationProcessing = CancellationRequest::factory()->create([
            'event_id' => $eventProcessing->id,
            'requested_by' => $organizer->id,
            'status' => 'pending',
        ]);
        $payoutProcessing = Payout::factory()->create([
            'event_id' => $eventProcessing->id,
            'organizer_id' => $organizer->id,
            'status' => 'processing',
        ]);

        // 3. Completed payout (should NOT be voided)
        $eventCompleted = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => 'awaiting_cancellation',
            'event_date' => now()->addDays(5)->format('Y-m-d'),
        ]);
        $cancellationCompleted = CancellationRequest::factory()->create([
            'event_id' => $eventCompleted->id,
            'requested_by' => $organizer->id,
            'status' => 'pending',
        ]);
        $payoutCompleted = Payout::factory()->create([
            'event_id' => $eventCompleted->id,
            'organizer_id' => $organizer->id,
            'status' => 'completed',
        ]);

        // Approve all three
        $this->actingAs($admin)->put(route('admin.cancellations.approve', $cancellationPending));
        $this->actingAs($admin)->put(route('admin.cancellations.approve', $cancellationProcessing));
        $this->actingAs($admin)->put(route('admin.cancellations.approve', $cancellationCompleted));

        $this->assertEquals(PayoutStatus::Voided, $payoutPending->fresh()->status);
        $this->assertEquals(PayoutStatus::Voided, $payoutProcessing->fresh()->status);
        $this->assertEquals(PayoutStatus::Completed, $payoutCompleted->fresh()->status);
    }

    /**
     * Test organizer cannot access cancellation routes.
     */
    public function test_organizer_cannot_access_cancellation_queue(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $cancellation = CancellationRequest::factory()->create();

        $this->actingAs($organizer)
            ->get(route('admin.cancellations.index'))
            ->assertStatus(403);

        $this->actingAs($organizer)
            ->put(route('admin.cancellations.approve', $cancellation))
            ->assertStatus(403);

        $this->actingAs($organizer)
            ->put(route('admin.cancellations.reject', $cancellation), [
                'rejection_reason' => 'Alasan penolakan pembatalan ini valid dan panjang.',
            ])
            ->assertStatus(403);
    }

    /**
     * Test regular user cannot access cancellation routes.
     */
    public function test_regular_user_cannot_access_cancellation_queue(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $cancellation = CancellationRequest::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.cancellations.index'))
            ->assertStatus(403);

        $this->actingAs($user)
            ->put(route('admin.cancellations.approve', $cancellation))
            ->assertStatus(403);

        $this->actingAs($user)
            ->put(route('admin.cancellations.reject', $cancellation), [
                'rejection_reason' => 'Alasan penolakan pembatalan ini valid dan panjang.',
            ])
            ->assertStatus(403);
    }

    /**
     * Test guests are redirected to login when trying to access cancellation routes.
     */
    public function test_guest_cannot_access_cancellation_queue(): void
    {
        $cancellation = CancellationRequest::factory()->create();

        $this->get(route('admin.cancellations.index'))->assertRedirect(route('login'));
        $this->put(route('admin.cancellations.approve', $cancellation))->assertRedirect(route('login'));
        $this->put(route('admin.cancellations.reject', $cancellation))->assertRedirect(route('login'));
    }
}
