<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_archives_user_without_hard_deleting_record(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        $user = User::factory()->create([
            'role' => UserRole::User,
        ]);

        $response = $this
            ->actingAs($admin)
            ->delete(route('admin.users.destroy', $user));

        $response->assertSessionHas('status', 'User berhasil diarsipkan');
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertNotNull(User::withTrashed()->find($user->id));
    }

    public function test_deleted_user_filter_is_exclusive_to_archived_records(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        User::factory()->create([
            'name' => 'Active Customer',
            'role' => UserRole::User,
        ]);
        $archivedUser = User::factory()->create([
            'name' => 'Archived Customer',
            'role' => UserRole::User,
        ]);
        $archivedUser->delete();

        $activeResponse = $this
            ->actingAs($admin)
            ->get(route('admin.users.index'));

        $activeResponse->assertOk();
        $activeResponse->assertSee('Active Customer');
        $activeResponse->assertDontSee('Archived Customer');

        $deletedResponse = $this
            ->actingAs($admin)
            ->get(route('admin.users.index', ['status' => 'deleted']));

        $deletedResponse->assertOk();
        $deletedResponse->assertSee('Archived Customer');
        $deletedResponse->assertDontSee('Active Customer');
    }

    public function test_admin_archives_event_category_without_hard_deleting_record(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        $category = EventCategory::query()->create([
            'name' => 'Workshop',
            'slug' => 'workshop',
        ]);

        $response = $this
            ->actingAs($admin)
            ->delete(route('admin.event-categories.destroy', $category));

        $response->assertSessionHas('status', 'Kategori event berhasil diarsipkan.');
        $this->assertSoftDeleted('event_categories', ['id' => $category->id]);
        $this->assertNotNull(EventCategory::withTrashed()->find($category->id));
    }

    public function test_deleted_category_filter_is_exclusive_to_archived_records(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        EventCategory::query()->create([
            'name' => 'Active Taxonomy Row',
            'slug' => 'active-taxonomy-row',
        ]);
        $archivedCategory = EventCategory::query()->create([
            'name' => 'Archived Expo',
            'slug' => 'archived-expo',
        ]);
        $archivedCategory->delete();

        $activeResponse = $this
            ->actingAs($admin)
            ->get(route('admin.event-categories.index'));

        $activeResponse->assertOk();
        $activeResponse->assertSee('Active Taxonomy Row');
        $activeResponse->assertDontSee('Archived Expo');

        $deletedResponse = $this
            ->actingAs($admin)
            ->get(route('admin.event-categories.index', ['status' => 'deleted']));

        $deletedResponse->assertOk();
        $deletedResponse->assertSee('Archived Expo');
        $deletedResponse->assertDontSee('Active Taxonomy Row');
    }
}
