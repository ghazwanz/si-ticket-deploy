<?php

namespace Tests\Feature\Api\Admin;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_event_categories(): void
    {
        $admin = User::factory()->admin()->create();
        EventCategory::factory()->count(2)->create();

        $response = $this->actingAs($admin)->getJson(route('api.admin.event-categories.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['id', 'name', 'slug', 'image', 'color', 'image_url'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_admin_can_create_event_category_via_api(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson(route('api.admin.event-categories.store'), [
            'name' => 'Musik',
            'color' => 'violet',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Musik')
            ->assertJsonPath('data.slug', 'musik');

        $this->assertDatabaseHas('event_categories', [
            'name' => 'Musik',
            'slug' => 'musik',
        ]);
    }
}