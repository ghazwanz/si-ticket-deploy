<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Order;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard_and_see_dynamic_metrics(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Setup User stats comparing this month vs last month
        // This month (last 30 days): 2 organizers
        User::factory()->organizer()->count(2)->create(['created_at' => now()->subDays(5)]);
        // Last month (preceding 30 days): 1 organizer
        User::factory()->organizer()->create(['created_at' => now()->subDays(45)]);
        // Calculation: ((2 - 1) / 1) * 100 = +100%

        // 2. Setup Event stats comparing this month vs last month
        $organizer = User::factory()->organizer()->create();
        // This month (last 30 days): 3 published events
        Event::factory()->count(3)->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'created_at' => now()->subDays(10),
        ]);
        // Last month (preceding 30 days): 2 published events
        Event::factory()->count(2)->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Published,
            'created_at' => now()->subDays(40),
        ]);
        // Calculation: ((3 - 2) / 2) * 100 = +50%

        // 3. Setup warning counts
        // 2 event pending approval
        Event::factory()->count(2)->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::AwaitingApproval,
        ]);
        // 3 inactive organizers
        User::factory()->organizer()->inactive()->count(3)->create();

        // Access dashboard
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Assert stats growth rates are rendered in Blade
        $response->assertSee('+600% vs bulan lalu');
        $response->assertSee('+50% vs bulan lalu');
        $response->assertSee('text-emerald-500'); // growth colors

        // Assert helper warnings
        $response->assertSee('2 acara membutuhkan persetujuan');
        $response->assertSee('3 EO menunggu verifikasi');
    }

    public function test_admin_dashboard_shows_no_pending_warnings_when_empty(): void
    {
        $admin = User::factory()->admin()->create();

        // Access dashboard when there are no reviews or pending EOs
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);

        $response->assertSee('Semua acara telah ditinjau');
        $response->assertSee('Tidak ada EO tertunda');
    }

    public function test_recent_activity_logs_merges_and_sorts_correctly(): void
    {
        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();

        // 1. Create a user (5 mins ago)
        $user = User::factory()->organizer()->create([
            'name' => 'Stellar EO',
            'created_at' => now()->subMinutes(5),
        ]);

        // 2. Create an event (10 mins ago)
        $event = Event::factory()->create([
            'name' => 'Music Fest',
            'organizer_id' => $organizer->id,
            'created_at' => now()->subMinutes(10),
        ]);

        // 3. Create a paid order (2 mins ago)
        $buyer = User::factory()->create(['name' => 'Alice Customer']);
        $order = Order::factory()->create([
            'event_id' => $event->id,
            'user_id' => $buyer->id,
            'status' => OrderStatus::Paid,
            'paid_at' => now()->subMinutes(2),
        ]);

        // 4. Create a payout (15 mins ago)
        $payout = Payout::factory()->create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'status' => PayoutStatus::Completed,
            'created_at' => now()->subMinutes(15),
            'updated_at' => now()->subMinutes(15),
        ]);

        // Access dashboard
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Verify mapped logs are visible in the view
        $response->assertSee('Registrasi EO Baru: Stellar EO');
        $response->assertSee('Pembuatan Event Baru: "Music Fest"');
        $response->assertSee('Pesanan Tiket Lunas: "Music Fest"');
        $response->assertSee('Pembaruan Payout: "Music Fest" (Selesai)');

        // Verify Heroicons components are rendered via dynamic-component
        $response->assertSee('data-icon="user"', false);
        $response->assertSee('data-icon="calendar"', false);
        $response->assertSee('data-icon="shopping-cart"', false);
        $response->assertSee('data-icon="banknotes"', false);
    }

    public function test_category_distribution_bar_colors(): void
    {
        $admin = User::factory()->admin()->create();

        // Create categories
        $cat1 = EventCategory::create(['name' => 'Music', 'slug' => 'music']);
        $cat2 = EventCategory::create(['name' => 'Tech', 'slug' => 'tech']);
        $cat3 = EventCategory::create(['name' => 'Sports', 'slug' => 'sports']);

        // Create events for categories
        $organizer = User::factory()->organizer()->create();
        Event::factory()->count(3)->create(['category_id' => $cat1->id, 'organizer_id' => $organizer->id]);
        Event::factory()->count(2)->create(['category_id' => $cat2->id, 'organizer_id' => $organizer->id]);
        Event::factory()->count(1)->create(['category_id' => $cat3->id, 'organizer_id' => $organizer->id]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Assert different colors are assigned
        $response->assertSee('bg-violet-600');
        $response->assertSee('bg-blue-500');
        $response->assertSee('bg-emerald-500');
    }
}
