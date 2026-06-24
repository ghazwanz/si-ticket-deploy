<?php

namespace Tests\Feature\Services\Organizer;

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\Organizer\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EventService $eventService;

    protected User $organizer;

    protected User $otherOrganizer;

    protected EventCategory $category1;

    protected EventCategory $category2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventService = new EventService;

        $this->organizer = User::factory()->create(['role' => UserRole::Organizer]);
        $this->otherOrganizer = User::factory()->create(['role' => UserRole::Organizer]);

        $this->category1 = EventCategory::factory()->create(['name' => 'Music']);
        $this->category2 = EventCategory::factory()->create(['name' => 'Sports']);
    }

    public function test_it_returns_only_organizer_events(): void
    {
        Event::factory()->create(['organizer_id' => $this->organizer->id]);
        Event::factory()->create(['organizer_id' => $this->otherOrganizer->id]);

        $events = $this->eventService->getPaginatedEventsForOrganizer($this->organizer->id, []);

        $this->assertCount(1, $events);
        $this->assertEquals($this->organizer->id, $events->first()->organizer_id);
    }

    public function test_it_filters_events_by_status(): void
    {
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => EventStatus::Published,
        ]);
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => EventStatus::Draft,
        ]);

        $events = $this->eventService->getPaginatedEventsForOrganizer($this->organizer->id, ['status' => 'published']);

        $this->assertCount(1, $events);
        $this->assertEquals(EventStatus::Published, $events->first()->status);
    }

    public function test_it_filters_events_by_category(): void
    {
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category1->id,
        ]);
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category2->id,
        ]);

        $events = $this->eventService->getPaginatedEventsForOrganizer($this->organizer->id, ['category' => $this->category1->id]);

        $this->assertCount(1, $events);
        $this->assertEquals($this->category1->id, $events->first()->category_id);
    }

    public function test_it_filters_events_by_date_range(): void
    {
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'event_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
        ]);
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'event_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
        ]);

        $events = $this->eventService->getPaginatedEventsForOrganizer($this->organizer->id, [
            'date_from' => Carbon::now()->format('Y-m-d'),
            'date_to' => Carbon::now()->addDays(10)->format('Y-m-d'),
        ]);

        $this->assertCount(1, $events);
    }

    public function test_it_searches_by_name_venue_city(): void
    {
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'name' => 'Jazz Night',
            'venue_name' => 'Grand Stage',
            'city' => 'Jakarta',
        ]);
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'name' => 'Rock Fest',
            'venue_name' => 'Stadium',
            'city' => 'Bandung',
        ]);

        // Search by name
        $events = $this->eventService->getPaginatedEventsForOrganizer($this->organizer->id, ['search' => 'Jazz']);
        $this->assertCount(1, $events);

        // Search by venue
        $events = $this->eventService->getPaginatedEventsForOrganizer($this->organizer->id, ['search' => 'Stadium']);
        $this->assertCount(1, $events);

        // Search by city
        $events = $this->eventService->getPaginatedEventsForOrganizer($this->organizer->id, ['search' => 'Jakarta']);
        $this->assertCount(1, $events);
    }

    public function test_it_sorts_by_name(): void
    {
        Event::factory()->create(['organizer_id' => $this->organizer->id, 'name' => 'Zebra']);
        Event::factory()->create(['organizer_id' => $this->organizer->id, 'name' => 'Apple']);

        $events = $this->eventService->getPaginatedEventsForOrganizer($this->organizer->id, [
            'sort' => 'name',
            'order' => 'asc',
        ]);

        $this->assertEquals('Apple', $events->first()->name);
    }

    public function test_it_sorts_by_event_date(): void
    {
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'event_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        ]);
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'event_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
        ]);

        $events = $this->eventService->getPaginatedEventsForOrganizer($this->organizer->id, [
            'sort' => 'event_date',
            'order' => 'asc',
        ]);

        $this->assertEquals(Carbon::now()->addDays(5)->format('Y-m-d'), $events->first()->event_date->format('Y-m-d'));
    }

    public function test_it_eager_loads_occupancy_aggregates_and_sorts_by_occupancy(): void
    {
        $eventHigh = Event::factory()->create(['organizer_id' => $this->organizer->id]);
        TicketCategory::factory()->create([
            'event_id' => $eventHigh->id,
            'quota' => 100,
            'sold_count' => 90, // 90%
        ]);

        $eventLow = Event::factory()->create(['organizer_id' => $this->organizer->id]);
        TicketCategory::factory()->create([
            'event_id' => $eventLow->id,
            'quota' => 100,
            'sold_count' => 10, // 10%
        ]);

        $eventEmpty = Event::factory()->create(['organizer_id' => $this->organizer->id]); // 0%

        $events = $this->eventService->getPaginatedEventsForOrganizer($this->organizer->id, [
            'sort' => 'occupancy',
            'order' => 'desc',
        ]);

        $this->assertCount(3, $events);

        // Assert eager loaded aggregates exist
        $this->assertNotNull($events->first()->total_quota);
        $this->assertNotNull($events->first()->total_sold);

        // Assert sorting order
        $this->assertEquals($eventHigh->id, $events[0]->id);
        $this->assertEquals($eventLow->id, $events[1]->id);
        $this->assertEquals($eventEmpty->id, $events[2]->id);
    }
}
