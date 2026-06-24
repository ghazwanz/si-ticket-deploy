<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_list_with_created_at_column(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'created_at' => '2026-06-01 10:00:00',
        ]);

        $organizer = User::factory()->organizer()->create([
            'name' => 'Organizer Alpha',
            'created_at' => '2026-06-05 15:30:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);

        // Verify column header is present
        $response->assertSee('Tanggal Registrasi');

        // Verify dates are formatted in Indonesian locale (translatedFormat)
        // 01 Jun 2026, 10:00
        $response->assertSee('01 Jun 2026, 10:00 WIB');
        // 05 Jun 2026, 15:30
        $response->assertSee('05 Jun 2026, 15:30 WIB');
    }

    public function test_admin_can_sort_users_by_created_at_descending_and_ascending(): void
    {
        $admin = User::factory()->admin()->create();

        $userOld = User::factory()->create([
            'name' => 'Oldest User',
            'created_at' => now()->subDays(10),
        ]);

        $userNew = User::factory()->create([
            'name' => 'Newest User',
            'created_at' => now()->subDays(1),
        ]);

        // 1. Sort Descending
        $responseDesc = $this->actingAs($admin)->get(route('admin.users.index', [
            'sort' => 'created_at',
            'order' => 'desc',
        ]));

        $responseDesc->assertStatus(200);
        $contentDesc = $responseDesc->getContent();

        // Newest should appear before Oldest in descending order
        $this->assertTrue(
            strpos($contentDesc, 'Newest User') < strpos($contentDesc, 'Oldest User'),
            'Newest user should be displayed before the oldest user when sorting by registration date descending.'
        );

        // 2. Sort Ascending
        $responseAsc = $this->actingAs($admin)->get(route('admin.users.index', [
            'sort' => 'created_at',
            'order' => 'asc',
        ]));

        $responseAsc->assertStatus(200);
        $contentAsc = $responseAsc->getContent();

        // Oldest should appear before Newest in ascending order
        $this->assertTrue(
            strpos($contentAsc, 'Oldest User') < strpos($contentAsc, 'Newest User'),
            'Oldest user should be displayed before the newest user when sorting by registration date ascending.'
        );
    }

    public function test_admin_can_sort_users_by_name(): void
    {
        $admin = User::factory()->admin()->create();

        $userB = User::factory()->create(['name' => 'Baker User']);
        $userA = User::factory()->create(['name' => 'Abel User']);

        // Sort Ascending by name
        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'sort' => 'name',
            'order' => 'asc',
        ]));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertTrue(
            strpos($content, 'Abel User') < strpos($content, 'Baker User'),
            'Abel User should be displayed before Baker User when sorting by name ascending.'
        );
    }
}
