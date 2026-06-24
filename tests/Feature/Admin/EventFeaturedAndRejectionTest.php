<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\EventStatus;
use App\Enums\PayoutStatus;
use App\Models\Event;
use App\Models\Payout;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EventFeaturedAndRejectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_toggle_is_featured_on_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->published()->create(['is_featured' => false]);

        // 1. Set to Featured
        $response = $this->actingAs($admin)->patch(route('admin.events.toggle-featured', $event));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Acara berhasil ditandai sebagai Unggulan.');
        $this->assertTrue($event->refresh()->is_featured);

        // 2. Remove from Featured
        $response = $this->actingAs($admin)->patch(route('admin.events.toggle-featured', $event));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Acara berhasil dihapus dari Unggulan.');
        $this->assertFalse($event->refresh()->is_featured);
    }

    public function test_admin_cannot_toggle_featured_on_non_published_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->draft()->create(['is_featured' => false]);

        $response = $this->actingAs($admin)->patch(route('admin.events.toggle-featured', $event));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
        $this->assertFalse($event->refresh()->is_featured);
    }

    public function test_admin_cannot_toggle_featured_if_not_admin(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->published()->create(['is_featured' => false]);

        $response = $this->actingAs($user)->patch(route('admin.events.toggle-featured', $event));

        $response->assertStatus(403);
        $this->assertFalse($event->refresh()->is_featured);
    }

    public function test_featured_status_is_reset_when_event_status_changes_from_published(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->published()->create(['is_featured' => true]);

        // Change status to completed
        $response = $this->actingAs($admin)->put(route('admin.events.update-status', $event), [
            'status' => 'completed',
        ]);

        $response->assertRedirect();
        $event->refresh();
        $this->assertEquals(EventStatus::Completed, $event->status);
        $this->assertFalse($event->is_featured);
    }

    public function test_admin_can_reject_event_with_rejection_message(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->awaitingApproval()->create();

        $response = $this->actingAs($admin)->put(route('admin.events.update-status', $event), [
            'status' => 'reject',
            'rejection_message' => 'Alasan penolakan: Kualitas gambar banner buruk dan deskripsi kurang lengkap.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $event->refresh();
        $this->assertEquals(EventStatus::Draft, $event->status);
        $this->assertEquals('Alasan penolakan: Kualitas gambar banner buruk dan deskripsi kurang lengkap.', $event->rejection_message);
    }

    public function test_admin_cannot_reject_event_without_rejection_message(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->awaitingApproval()->create();

        $response = $this->actingAs($admin)->from(route('admin.events.index'))->put(route('admin.events.update-status', $event), [
            'status' => 'reject',
            'rejection_message' => '',
        ]);

        $response->assertRedirect(route('admin.events.index'));
        $response->assertSessionHasErrors(['rejection_message']);

        $this->assertEquals(EventStatus::AwaitingApproval, $event->refresh()->status);
    }

    public function test_rejection_message_is_cleared_when_organizer_resubmits_event(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Draft,
            'rejection_message' => 'Some rejection reason',
        ]);

        // Create a ticket category for the event because ticket rules require at least 1 ticket
        TicketCategory::factory()->create([
            'event_id' => $event->id,
            'quota' => 100,
        ]);

        $this->actingAs($organizer);

        $response = $this->put(route('organizer.events.update', $event), [
            'name' => $event->name,
            'category_id' => $event->category_id,
            'description' => 'Updated Description',
            'venue_name' => $event->venue_name,
            'address' => $event->address,
            'city' => $event->city,
            'event_date' => $event->event_date->format('Y-m-d'),
            'start_time' => '19:00',
            'end_time' => '22:00',
            'status' => 'awaiting_approval',
            'tickets' => [
                [
                    'id' => $event->ticketCategories->first()->id,
                    'name' => 'General Admission',
                    'price' => 50000,
                    'quota' => 150,
                ],
            ],
        ]);

        $response->assertRedirect();

        $event->refresh();
        $this->assertEquals(EventStatus::AwaitingApproval, $event->status);
        $this->assertNull($event->rejection_message);
    }

    public function test_rejection_message_is_cleared_when_admin_approves_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->awaitingApproval()->create([
            'rejection_message' => 'Old rejection message',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.events.update-status', $event), [
            'status' => 'published',
        ]);

        $response->assertRedirect();

        $event->refresh();
        $this->assertEquals(EventStatus::Published, $event->status);
        $this->assertNull($event->rejection_message);
    }

    public function test_landing_page_orders_featured_events_first(): void
    {
        // Event A: not featured, sooner date
        $eventA = Event::factory()->published()->create([
            'name' => 'Event A',
            'is_featured' => false,
            'event_date' => '2026-06-10',
        ]);

        // Event B: featured, later date
        $eventB = Event::factory()->published()->create([
            'name' => 'Event B',
            'is_featured' => true,
            'event_date' => '2026-06-20',
        ]);

        // Event C: not featured, latest date
        $eventC = Event::factory()->published()->create([
            'name' => 'Event C',
            'is_featured' => false,
            'event_date' => '2026-06-30',
        ]);

        // Setup ticket categories so they are considered active/available
        TicketCategory::factory()->create(['event_id' => $eventA->id, 'is_active' => true]);
        TicketCategory::factory()->create(['event_id' => $eventB->id, 'is_active' => true]);
        TicketCategory::factory()->create(['event_id' => $eventC->id, 'is_active' => true]);

        $response = $this->get(route('landing'));

        $response->assertStatus(200);

        // Assert the order of popularEvents in the view context
        $popularEvents = $response->viewData('popularEvents');

        $this->assertCount(3, $popularEvents);

        // Event B (featured) must be first
        $this->assertEquals($eventB->id, $popularEvents->first()->id);
        // Event A (not featured, sooner) must be second
        $this->assertEquals($eventA->id, $popularEvents->get(1)->id);
        // Event C (not featured, later) must be third
        $this->assertEquals($eventC->id, $popularEvents->get(2)->id);
    }

    public function test_admin_can_cancel_published_event_without_sales_with_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->published()->create();

        $response = $this->actingAs($admin)->put(route('admin.events.update-status', $event), [
            'status' => 'cancelled',
            'rejection_message' => 'Acara dibatalkan karena EO mengundurkan diri dan belum ada penjualan.',
        ]);

        $response->assertRedirect();
        $event->refresh();
        $this->assertEquals(EventStatus::Cancelled, $event->status);
        $this->assertEquals('Acara dibatalkan karena EO mengundurkan diri dan belum ada penjualan.', $event->rejection_message);
    }

    public function test_admin_cannot_cancel_published_event_without_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->published()->create();

        $response = $this->actingAs($admin)->from(route('admin.events.index'))->put(route('admin.events.update-status', $event), [
            'status' => 'cancelled',
            'rejection_message' => '',
        ]);

        $response->assertRedirect(route('admin.events.index'));
        $response->assertSessionHasErrors(['rejection_message']);
        $this->assertEquals(EventStatus::Published, $event->refresh()->status);
    }

    public function test_admin_cannot_cancel_published_event_with_sales(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->published()->create();
        TicketCategory::factory()->create([
            'event_id' => $event->id,
            'sold_count' => 5,
        ]);

        // Expect Exception because EventService throws InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);

        // Disable Exception Handling to let the exception bubble up to the test
        $this->withoutExceptionHandling();

        $this->actingAs($admin)->put(route('admin.events.update-status', $event), [
            'status' => 'cancelled',
            'rejection_message' => 'Membatalkan paksa acara.',
        ]);
    }

    public function test_admin_can_filter_events_by_awaiting_cancellation(): void
    {
        $admin = User::factory()->admin()->create();

        $eventAwaiting = Event::factory()->awaitingCancellation()->create(['name' => 'Awaiting Cancellation Event']);
        $eventPublished = Event::factory()->published()->create(['name' => 'Published Event']);

        $response = $this->actingAs($admin)->get(route('admin.events.index', ['status' => 'awaiting_cancellation']));

        $response->assertStatus(200);
        $response->assertSee('Awaiting Cancellation Event');
        $response->assertDontSee('Published Event');
    }

    public function test_admin_payout_index_page_loads_with_soft_deleted_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $payout = Payout::factory()->create([
            'event_id' => $event->id,
            'organizer_id' => $event->organizer_id,
            'status' => PayoutStatus::Pending,
        ]);

        $event->delete(); // soft delete

        $response = $this->actingAs($admin)->get(route('admin.payouts.index', ['payout_type' => 'final']));

        $response->assertStatus(200);
        $response->assertSee($event->name);
    }
}
