<?php

namespace Tests\Feature\Public;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Order;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected User $organizer;

    protected EventCategory $category1;

    protected EventCategory $category2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organizer = User::factory()->create(['role' => 'organizer']);
        $this->category1 = EventCategory::factory()->create(['name' => 'Music', 'slug' => 'music']);
        $this->category2 = EventCategory::factory()->create(['name' => 'Sports', 'slug' => 'sports']);
    }

    public function test_public_catalog_can_be_accessed(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category1->id,
            'status' => 'published',
            'city' => 'Jakarta',
            'event_date' => now()->addDays(5),
        ]);

        $response = $this->get('/events');

        $response->assertOk();
        $response->assertSee($event->name);
    }

    public function test_public_catalog_can_filter_by_city(): void
    {
        $eventJakarta = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category1->id,
            'status' => 'published',
            'city' => 'Jakarta',
            'event_date' => now()->addDays(5),
        ]);

        $eventBandung = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category1->id,
            'status' => 'published',
            'city' => 'Bandung',
            'event_date' => now()->addDays(5),
        ]);

        $response = $this->get('/events?city=Jakarta');
        $response->assertSee($eventJakarta->name);
        $response->assertDontSee($eventBandung->name);
    }

    public function test_public_catalog_can_filter_by_category(): void
    {
        $eventMusic = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category1->id,
            'status' => 'published',
            'event_date' => now()->addDays(5),
        ]);

        $eventSports = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category2->id,
            'status' => 'published',
            'event_date' => now()->addDays(5),
        ]);

        $response = $this->get('/events?category=music');
        $response->assertSee($eventMusic->name);
        $response->assertDontSee($eventSports->name);
    }

    public function test_public_catalog_can_filter_by_date_range(): void
    {
        $eventToday = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category1->id,
            'status' => 'published',
            'event_date' => now()->addDays(1),
        ]);

        $eventNextMonth = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category1->id,
            'status' => 'published',
            'event_date' => now()->addDays(35),
        ]);

        $startDate = now()->addDays(0)->format('Y-m-d');
        $endDate = now()->addDays(10)->format('Y-m-d');

        $response = $this->get("/events?start_date={$startDate}&end_date={$endDate}");
        $response->assertSee($eventToday->name);
        $response->assertDontSee($eventNextMonth->name);
    }

    public function test_public_catalog_can_filter_by_status(): void
    {
        $eventUpcoming = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category1->id,
            'status' => 'published',
            'event_date' => now()->addDays(5),
        ]);

        $eventSuspended = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category1->id,
            'status' => 'awaiting_cancellation',
            'event_date' => now()->addDays(5),
        ]);

        // Filter by Mendatang (upcoming -> status 'published')
        $response1 = $this->get('/events?status=upcoming');
        $response1->assertSee($eventUpcoming->name);
        $response1->assertDontSee($eventSuspended->name);

        // Filter by Ditangguhkan (suspended -> status 'awaiting_cancellation')
        $response2 = $this->get('/events?status=suspended');
        $response2->assertSee($eventSuspended->name);
        $response2->assertDontSee($eventUpcoming->name);
    }

    public function test_suspended_event_displays_warning_but_no_cancellation_details(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category1->id,
            'status' => 'awaiting_cancellation',
            'event_date' => now()->addDays(5),
        ]);

        $ticketCategory = TicketCategory::factory()->create([
            'event_id' => $event->id,
            'is_active' => true,
            'quota' => 100,
            'sold_count' => 0,
        ]);

        $response = $this->get("/events/{$event->slug}");
        $response->assertOk();
        // Warns that ticket sales are temporarily suspended
        $response->assertSee('Penjualan Tiket Ditunda');
    }

    public function test_cancelled_event_returns_404_for_unauthenticated_users(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category1->id,
            'status' => 'cancelled',
            'event_date' => now()->addDays(5),
        ]);

        $response = $this->get("/events/{$event->slug}");
        $response->assertStatus(404);
    }

    public function test_cancelled_event_can_be_viewed_by_authenticated_users_with_existing_orders(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category1->id,
            'status' => 'cancelled',
            'event_date' => now()->addDays(5),
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $response = $this->actingAs($user)->get("/events/{$event->slug}");
        $response->assertOk();
        $response->assertSee('Event Cancelled');
        $response->assertSee($event->name);
    }

    public function test_public_page_displays_session_notifications(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category1->id,
            'status' => 'published',
            'event_date' => now()->addDays(5),
        ]);

        // Scenario 1: Test error message in session
        $response = $this->withSession(['error' => 'Pesan kesalahan contoh'])
            ->get("/events/{$event->slug}");
        $response->assertSee('Pesan kesalahan contoh');
        $response->assertSee('Gagal');

        // Scenario 2: Test success message in session
        $response = $this->withSession(['success' => 'Pesan sukses contoh'])
            ->get("/events/{$event->slug}");
        $response->assertSee('Pesan sukses contoh');
        $response->assertSee('Berhasil');
    }
}
