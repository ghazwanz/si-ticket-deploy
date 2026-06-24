<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Admin\PayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class PlatformFeeAndAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_platform_fee_configuration(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertEquals(5.00, SystemSetting::get('platform_fee_percent', 5.00));

        $response = $this->actingAs($admin)
            ->put(route('admin.settings.system'), [
                'platform_fee_percent' => 7.50,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(7.50, (float) SystemSetting::get('platform_fee_percent'));
    }

    public function test_admin_platform_fee_validation(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->put(route('admin.settings.system'), [
                'platform_fee_percent' => -1.00,
            ]);
        $response->assertSessionHasErrors(['platform_fee_percent']);

        $response = $this->actingAs($admin)
            ->put(route('admin.settings.system'), [
                'platform_fee_percent' => 101.00,
            ]);
        $response->assertSessionHasErrors(['platform_fee_percent']);
    }

    public function test_payout_uses_dynamic_platform_fee(): void
    {
        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();
        $organizer->organizerProfile()->create([
            'organization_name' => 'Org',
            'phone' => '08123',
            'bank_name' => 'BCA',
            'bank_account_number' => '123',
            'bank_account_name' => 'Org Name',
        ]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => EventStatus::Completed,
        ]);

        SystemSetting::set('platform_fee_percent', 10.00);

        Order::factory()->create([
            'event_id' => $event->id,
            'status' => 'paid',
            'total_amount' => 1000000,
        ]);

        $payoutService = app(PayoutService::class);
        $payout = $payoutService->initializeFinalPayout($event);

        $this->assertEquals(1000000, $payout->gross_amount);
        $this->assertEquals(100000, $payout->platform_fee);
        $this->assertEquals(900000, $payout->net_amount);
        $this->assertEquals(10.00, (float) $payout->fee_percentage);
    }

    public function test_admin_can_manually_sync_order_status(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->pending()->create([
            'midtrans_order_id' => 'MID-SYNC-123',
        ]);

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/MID-SYNC-123/status' => Http::response([
                'transaction_status' => 'settlement',
                'payment_type' => 'credit_card',
                'transaction_id' => 'TX-CC-123',
            ], 200),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.orders.sync', $order));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(OrderStatus::Paid, $order->fresh()->status);
    }

    public function test_admin_can_view_checkout_transaction_logs(): void
    {
        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
        ]);

        $order = Order::factory()->create([
            'event_id' => $event->id,
            'user_id' => $admin->id,
            'midtrans_order_id' => 'MID-TEST-LOG-999',
            'status' => OrderStatus::Paid,
            'total_amount' => 125000,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.payouts.audit-logs'));

        $response->assertStatus(200);
        $response->assertSee('Log Transaksi Checkout');
        $response->assertSee('MID-TEST-LOG-999');
    }
}
