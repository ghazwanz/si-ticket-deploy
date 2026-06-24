<?php

declare(strict_types=1);

namespace Tests\Feature\Organizer;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Order;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Feature tests for organizer event cancellation logic.
 */
final class EventCancellationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test organizer can cancel an event directly when there are no transactions.
     */
    public function test_organizer_can_cancel_event_directly_without_transactions(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '19:00:00',
        ]);

        $response = $this->actingAs($organizer)
            ->post(route('organizer.events.cancel', $event));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Acara berhasil dibatalkan.');
        $this->assertEquals(EventStatus::Cancelled, $event->fresh()->status);
    }

    /**
     * Test organizer cannot cancel an event directly when ticket transactions exist.
     */
    public function test_organizer_cannot_cancel_event_directly_with_ticket_transactions(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '19:00:00',
        ]);

        TicketCategory::factory()->create([
            'event_id' => $event->id,
            'sold_count' => 1,
        ]);

        $response = $this->actingAs($organizer)
            ->post(route('organizer.events.cancel', $event));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
        $this->assertEquals(EventStatus::Published, $event->fresh()->status);
    }

    /**
     * Test organizer cannot cancel an event directly when merchandise transactions exist.
     */
    public function test_organizer_cannot_cancel_event_directly_with_merchandise_transactions(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '19:00:00',
        ]);

        // Create a paid order (transaction) for the event (merchandise or tickets)
        $buyer = User::factory()->create(['role' => UserRole::User]);
        Order::factory()->create([
            'event_id' => $event->id,
            'user_id' => $buyer->id,
            'status' => OrderStatus::Paid,
        ]);

        $response = $this->actingAs($organizer)
            ->post(route('organizer.events.cancel', $event));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
        $this->assertEquals(EventStatus::Published, $event->fresh()->status);
    }

    /**
     * Test organizer can request cancellation when transactions exist.
     */
    public function test_organizer_can_request_cancellation_with_transactions(): void
    {
        Notification::fake();

        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '19:00:00',
        ]);

        TicketCategory::factory()->create([
            'event_id' => $event->id,
            'sold_count' => 1,
        ]);

        $response = $this->actingAs($organizer)
            ->post(route('organizer.events.request-cancellation', $event), [
                'reason' => 'Alasan pembatalan ini valid dan panjang lebih dari dua puluh karakter.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Pengajuan pembatalan acara berhasil dikirim. Menunggu persetujuan admin.');
        $this->assertEquals(EventStatus::AwaitingCancellation, $event->fresh()->status);

        $this->assertDatabaseHas('cancellation_requests', [
            'event_id' => $event->id,
            'requested_by' => $organizer->id,
            'status' => 'pending',
            'reason' => 'Alasan pembatalan ini valid dan panjang lebih dari dua puluh karakter.',
        ]);
    }

    /**
     * Test organizer cannot request cancellation when no transactions exist.
     */
    public function test_organizer_cannot_request_cancellation_without_transactions(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '19:00:00',
        ]);

        $response = $this->actingAs($organizer)
            ->post(route('organizer.events.request-cancellation', $event), [
                'reason' => 'Alasan pembatalan ini valid dan panjang lebih dari dua puluh karakter.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
        $this->assertEquals(EventStatus::Published, $event->fresh()->status);
        $this->assertDatabaseMissing('cancellation_requests', [
            'event_id' => $event->id,
        ]);
    }

    /**
     * Test validation requires reason.
     */
    public function test_request_cancellation_requires_reason(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '19:00:00',
        ]);

        TicketCategory::factory()->create([
            'event_id' => $event->id,
            'sold_count' => 1,
        ]);

        $response = $this->actingAs($organizer)
            ->post(route('organizer.events.request-cancellation', $event), [
                'reason' => 'Short',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['reason']);
    }

    /**
     * Test cancellation is blocked after hard cutoff has passed.
     */
    public function test_cancellation_is_blocked_after_cutoff(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->subDay()->format('Y-m-d'),
            'start_time' => '19:00:00',
        ]);

        // Scenario 1: Direct cancel attempts
        $response = $this->actingAs($organizer)
            ->post(route('organizer.events.cancel', $event));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);

        // Scenario 2: Request cancellation attempts (even with tickets sold)
        TicketCategory::factory()->create([
            'event_id' => $event->id,
            'sold_count' => 1,
        ]);

        $response = $this->actingAs($organizer)
            ->post(route('organizer.events.request-cancellation', $event), [
                'reason' => 'Alasan pembatalan ini valid dan panjang lebih dari dua puluh karakter.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
    }

    /**
     * Test organizer events index page renders successfully with statistics.
     */
    public function test_organizer_events_index_page_renders_with_statistics(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        TicketCategory::factory()->create([
            'event_id' => $event->id,
            'sold_count' => 12,
        ]);

        $buyer = User::factory()->create(['role' => UserRole::User]);
        Order::factory()->create([
            'event_id' => $event->id,
            'user_id' => $buyer->id,
            'status' => OrderStatus::Paid,
            'total_amount' => 120000,
        ]);

        $response = $this->actingAs($organizer)
            ->get(route('organizer.events.index'));

        $response->assertOk();
        $response->assertSee('Total Acara');
        $response->assertSee('1'); // Total events count
        $response->assertSee('Tiket Terjual');
        $response->assertSee('12'); // Sold count
        $response->assertSee('Pendapatan Kotor');
        $response->assertSee('Rp 120.000'); // Revenue
        $response->assertSee('Acara Mendatang');
    }

    /**
     * Test organizer cannot delete a published event with ticket sales.
     */
    public function test_organizer_cannot_delete_published_event_with_sales(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        TicketCategory::factory()->create([
            'event_id' => $event->id,
            'sold_count' => 1,
        ]);

        $response = $this->actingAs($organizer)
            ->delete(route('organizer.events.destroy', $event));

        $response->assertRedirect(route('organizer.events.index'));
        $response->assertSessionHasErrors(['error']);
        $this->assertNull($event->fresh()->deleted_at);
    }

    /**
     * Test organizer can delete a completed or cancelled event with ticket sales.
     */
    public function test_organizer_can_delete_completed_or_cancelled_event_with_sales(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);

        // Scenario 1: Completed
        $completedEvent = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Completed,
            'event_date' => now()->subDays(2)->format('Y-m-d'),
        ]);

        TicketCategory::factory()->create([
            'event_id' => $completedEvent->id,
            'sold_count' => 5,
        ]);

        $response1 = $this->actingAs($organizer)
            ->delete(route('organizer.events.destroy', $completedEvent));

        $response1->assertRedirect(route('organizer.events.index'));
        $response1->assertSessionHas('status', 'Acara berhasil dihapus dari daftar aktif.');
        $this->assertNotNull($completedEvent->fresh()->deleted_at);

        // Scenario 2: Cancelled
        $cancelledEvent = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Cancelled,
            'event_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        TicketCategory::factory()->create([
            'event_id' => $cancelledEvent->id,
            'sold_count' => 3,
        ]);

        $response2 = $this->actingAs($organizer)
            ->delete(route('organizer.events.destroy', $cancelledEvent));

        $response2->assertRedirect(route('organizer.events.index'));
        $response2->assertSessionHas('status', 'Acara berhasil dihapus dari daftar aktif.');
        $this->assertNotNull($cancelledEvent->fresh()->deleted_at);
    }

    /**
     * Test organizer can delete a published event if it has no ticket sales.
     */
    public function test_organizer_can_delete_published_event_without_sales(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'event_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        TicketCategory::factory()->create([
            'event_id' => $event->id,
            'sold_count' => 0,
        ]);

        $response = $this->actingAs($organizer)
            ->delete(route('organizer.events.destroy', $event));

        $response->assertRedirect(route('organizer.events.index'));
        $response->assertSessionHas('status', 'Acara berhasil dihapus dari daftar aktif.');
        $this->assertNotNull($event->fresh()->deleted_at);
    }

    /**
     * Test organizer cannot edit a completed event.
     */
    public function test_organizer_cannot_edit_completed_event(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Completed,
        ]);

        $response = $this->actingAs($organizer)
            ->get(route('organizer.events.edit', $event));

        $response->assertRedirect(route('organizer.events.index'));
        $response->assertSessionHasErrors(['error' => 'Acara yang telah selesai tidak dapat diubah kembali.']);
    }

    /**
     * Test organizer cannot update a completed event.
     */
    public function test_organizer_cannot_update_completed_event(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Completed,
        ]);

        $response = $this->actingAs($organizer)
            ->put(route('organizer.events.update', $event), [
                'name' => 'Updated Event Name',
                'status' => 'completed',
            ]);

        $response->assertRedirect(route('organizer.events.index'));
        $response->assertSessionHasErrors(['error' => 'Acara yang telah selesai tidak dapat diubah kembali.']);
    }

    /**
     * Test organizer cannot cancel a completed event directly.
     */
    public function test_organizer_cannot_cancel_completed_event_directly(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Completed,
        ]);

        $response = $this->actingAs($organizer)
            ->post(route('organizer.events.cancel', $event));

        $response->assertRedirect(route('organizer.events.index'));
        $response->assertSessionHasErrors(['error' => 'Acara yang telah selesai tidak dapat dibatalkan.']);
    }

    /**
     * Test organizer cannot request cancellation of a completed event.
     */
    public function test_organizer_cannot_request_cancellation_of_completed_event(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Completed,
        ]);

        $response = $this->actingAs($organizer)
            ->post(route('organizer.events.request-cancellation', $event), [
                'reason' => 'Alasan pembatalan ini valid dan panjang lebih dari dua puluh karakter.',
            ]);

        $response->assertRedirect(route('organizer.events.index'));
        $response->assertSessionHasErrors(['error' => 'Acara yang telah selesai tidak dapat dibatalkan.']);
    }

    /**
     * Test organizer cannot see 'Lihat' button for completed event on the index page.
     */
    public function test_organizer_cannot_see_lihat_button_for_completed_event(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);

        $completedEvent = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Completed,
        ]);

        $cancelledEvent = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Cancelled,
        ]);

        $response = $this->actingAs($organizer)
            ->get(route('organizer.events.index'));

        $response->assertOk();

        // Assert we see 'Lihat' for the cancelled event
        $editUrlCancelled = route('organizer.events.edit', $cancelledEvent);
        $response->assertSee($editUrlCancelled);

        // Assert we do not see the edit link ('Lihat') for the completed event
        $editUrlCompleted = route('organizer.events.edit', $completedEvent);
        $response->assertDontSee($editUrlCompleted);
    }
}
