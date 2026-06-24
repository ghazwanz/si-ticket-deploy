<?php

namespace Tests\Feature\Organizer;

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerEventFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $organizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizer = User::factory()->create(['role' => UserRole::Organizer]);
    }

    public function test_event_index_shows_filter_toolbar(): void
    {
        $this->actingAs($this->organizer)
            ->get(route('organizer.events.index'))
            ->assertStatus(200)
            ->assertSee('x-model="filters.search"', false)
            ->assertSee('x-model="filters.status"', false)
            ->assertSee('x-model="filters.category"', false)
            ->assertSee('x-model="filters.date_from"', false)
            ->assertSee('x-model="filters.sort"', false);
    }

    public function test_event_index_filters_results_by_status(): void
    {
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'name' => 'Published Event',
            'status' => EventStatus::Published,
        ]);

        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'name' => 'Draft Event',
            'status' => EventStatus::Draft,
        ]);

        $this->actingAs($this->organizer)
            ->get(route('organizer.events.index', ['status' => 'published']))
            ->assertStatus(200)
            ->assertSee('Published Event')
            ->assertDontSee('Draft Event');
    }

    public function test_event_index_searches_events(): void
    {
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'name' => 'Rock Concert',
        ]);

        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'name' => 'Jazz Night',
        ]);

        $this->actingAs($this->organizer)
            ->get(route('organizer.events.index', ['search' => 'Rock']))
            ->assertStatus(200)
            ->assertSee('Rock Concert')
            ->assertDontSee('Jazz Night');
    }

    public function test_event_index_sorts_by_occupancy(): void
    {
        $eventHigh = Event::factory()->create(['organizer_id' => $this->organizer->id, 'name' => 'High Occupancy']);
        TicketCategory::factory()->create(['event_id' => $eventHigh->id, 'quota' => 100, 'sold_count' => 90]);

        $eventLow = Event::factory()->create(['organizer_id' => $this->organizer->id, 'name' => 'Low Occupancy']);
        TicketCategory::factory()->create(['event_id' => $eventLow->id, 'quota' => 100, 'sold_count' => 10]);

        $response = $this->actingAs($this->organizer)
            ->get(route('organizer.events.index', ['sort' => 'occupancy', 'order' => 'desc']))
            ->assertStatus(200);

        $response->assertSeeInOrder(['High Occupancy', 'Low Occupancy']);
    }

    public function test_event_index_shows_real_occupancy(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'name' => 'Test Real Occupancy',
        ]);

        TicketCategory::factory()->create([
            'event_id' => $event->id,
            'quota' => 200,
            'sold_count' => 50,
        ]); // 50 / 200 = 25%

        $this->actingAs($this->organizer)
            ->get(route('organizer.events.index'))
            ->assertStatus(200)
            ->assertSee('50/200 (25%)');
    }
}
