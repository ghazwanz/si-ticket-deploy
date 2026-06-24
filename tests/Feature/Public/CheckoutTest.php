<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\OrderStatus;
use App\Models\Event;
use App\Models\MerchandiseItem;
use App\Models\MerchandiseVariant;
use App\Models\Order;
use App\Models\OrderMerchandise;
use App\Models\OrderTicket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Notifications\SendETicketNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Event $event;

    private TicketCategory $ticketCategory;

    private MerchandiseItem $merchItem;

    private MerchandiseVariant $merchVariant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->event = Event::factory()->create(['status' => 'published']);
        $this->ticketCategory = TicketCategory::factory()->create([
            'event_id' => $this->event->id,
            'price' => 100000,
            'quota' => 100,
            'sold_count' => 0,
            'max_per_user' => 5,
        ]);
        $this->merchItem = MerchandiseItem::factory()->create([
            'event_id' => $this->event->id,
            'is_available' => true,
        ]);
        $this->merchVariant = MerchandiseVariant::factory()->create([
            'merchandise_item_id' => $this->merchItem->id,
            'stock' => 50,
            'price_adjustment' => 0,
        ]);
    }

    /**
     * Test checkout page loads successfully.
     */
    public function test_checkout_page_loads_with_valid_parameters(): void
    {
        $response = $this->actingAs($this->user)->get(route('checkout.index', [
            'tickets' => [$this->ticketCategory->id => 2],
            'merchandise' => [$this->merchVariant->id => 1],
        ]));

        $response->assertOk();
        $response->assertSee($this->event->name);
        $response->assertSee($this->ticketCategory->name);
    }

    /**
     * Test checkout page redirects to slug if event is not published.
     */
    public function test_checkout_page_redirects_to_slug_if_event_not_published(): void
    {
        $this->event->update(['status' => 'cancelled']);

        $response = $this->actingAs($this->user)->get(route('checkout.index', [
            'tickets' => [$this->ticketCategory->id => 2],
        ]));

        $response->assertRedirect(route('events.show', $this->event->slug));
        $response->assertSessionHas('error');
    }

    /**
     * Test checkout page redirects if no tickets selected.
     */
    public function test_checkout_page_redirects_if_empty_cart(): void
    {
        $response = $this->actingAs($this->user)->get(route('checkout.index'));

        $response->assertRedirect(route('events.index'));
        $response->assertSessionHas('error');
    }

    /**
     * Test order creation validates max_per_user.
     */
    public function test_checkout_fails_if_exceeding_max_per_user(): void
    {
        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'event_id' => $this->event->id,
            'nama_lengkap' => 'Buyer Name',
            'email' => 'buyer@example.com',
            'no_telepon' => '081234567890',
            'tickets' => [$this->ticketCategory->id => 6], // Max per user is 5
            'holder_names' => [
                $this->ticketCategory->id => ['H1', 'H2', 'H3', 'H4', 'H5', 'H6'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseEmpty('orders');
    }

    /**
     * Test successful checkout creation and inventory deduction.
     */
    public function test_checkout_creates_order_and_reserves_stock(): void
    {
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['token' => 'mock-snap-token'], 200),
        ]);

        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'event_id' => $this->event->id,
            'nama_lengkap' => 'Buyer Name',
            'email' => 'buyer@example.com',
            'no_telepon' => '081234567890',
            'tickets' => [$this->ticketCategory->id => 2],
            'merchandise' => [$this->merchVariant->id => 1],
            'holder_names' => [
                $this->ticketCategory->id => ['Holder One', 'Holder Two'],
            ],
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('pesanan.show', $order->id));

        $this->assertEquals(OrderStatus::Pending, $order->status);
        $this->assertEquals('mock-snap-token', $order->snap_token);

        // Check stock/quota changes
        $this->assertEquals(2, $this->ticketCategory->fresh()->sold_count);
        $this->assertEquals(49, $this->merchVariant->fresh()->stock);

        // Check child models
        $this->assertCount(2, $order->tickets);
        $this->assertCount(1, $order->merchandise);
    }

    /**
     * Test checkout fails under cancellation guard.
     */
    public function test_checkout_fails_if_event_not_published(): void
    {
        $this->event->update(['status' => 'cancelled']);

        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'event_id' => $this->event->id,
            'nama_lengkap' => 'Buyer Name',
            'email' => 'buyer@example.com',
            'no_telepon' => '081234567890',
            'tickets' => [$this->ticketCategory->id => 2],
            'holder_names' => [
                $this->ticketCategory->id => ['Holder One', 'Holder Two'],
            ],
        ]);

        $response->assertRedirect(route('events.show', $this->event->slug));
        $response->assertSessionHas('error');
        $this->assertDatabaseEmpty('orders');
    }

    /**
     * Test webhook handles paid callback.
     */
    public function test_webhook_transitions_order_to_paid_and_notifies(): void
    {
        Notification::fake();

        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'total_amount' => 100000,
        ]);

        $serverKey = config('services.midtrans.server_key');
        $signatureKey = hash('sha512', $order->midtrans_order_id.'200'.'100000'.$serverKey);

        $payload = [
            'order_id' => $order->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '100000',
            'signature_key' => $signatureKey,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'mock-trans-id-999',
        ];

        $response = $this->postJson(route('payment.callback'), $payload);

        $response->assertOk();
        $this->assertEquals(OrderStatus::Paid, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);

        Notification::assertSentTo($this->user, SendETicketNotification::class);
    }

    /**
     * Test webhook extends stock reservation for pending VA payments.
     */
    public function test_webhook_extends_pending_bank_transfer_reservation(): void
    {
        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'total_amount' => 100000,
        ]);

        $serverKey = config('services.midtrans.server_key');
        $signatureKey = hash('sha512', $order->midtrans_order_id.'201'.'100000'.$serverKey);

        $payload = [
            'order_id' => $order->midtrans_order_id,
            'status_code' => '201',
            'gross_amount' => '100000',
            'signature_key' => $signatureKey,
            'transaction_status' => 'pending',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'mock-trans-id-999',
        ];

        $response = $this->postJson(route('payment.callback'), $payload);

        $response->assertOk();
        $this->assertEquals(OrderStatus::Pending, $order->fresh()->status);
        $this->assertEquals(
            $order->created_at->addHours(24)->toDateTimeString(),
            $order->fresh()->stock_reserved_until->toDateTimeString()
        );
    }

    /**
     * Test webhook releases stock on deny/expire/cancel.
     */
    public function test_webhook_releases_stock_on_failure_callback(): void
    {
        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'total_amount' => 100000,
        ]);

        // Create order ticket and merchandise to check stock release
        $orderTicket = OrderTicket::create([
            'id' => (string) Str::uuid(),
            'order_id' => $order->id,
            'ticket_category_id' => $this->ticketCategory->id,
            'holder_name' => 'Name',
            'unit_price' => 100000,
            'qr_token' => 't1',
        ]);
        $this->ticketCategory->increment('sold_count', 1);

        $orderMerch = OrderMerchandise::create([
            'id' => (string) Str::uuid(),
            'order_id' => $order->id,
            'merchandise_variant_id' => $this->merchVariant->id,
            'quantity' => 2,
            'unit_price' => 20000,
            'merch_token' => 'm1',
        ]);
        $this->merchVariant->decrement('stock', 2);

        $serverKey = config('services.midtrans.server_key');
        $signatureKey = hash('sha512', $order->midtrans_order_id.'407'.'100000'.$serverKey);

        $payload = [
            'order_id' => $order->midtrans_order_id,
            'status_code' => '407',
            'gross_amount' => '100000',
            'signature_key' => $signatureKey,
            'transaction_status' => 'expire',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'mock-trans-id-999',
        ];

        // Stock counts before callback:
        $this->assertEquals(1, $this->ticketCategory->fresh()->sold_count);
        $this->assertEquals(48, $this->merchVariant->fresh()->stock);

        $response = $this->postJson(route('payment.callback'), $payload);

        $response->assertOk();
        $this->assertEquals(OrderStatus::Cancelled, $order->fresh()->status);

        // Stock counts after callback should be restored:
        $this->assertEquals(0, $this->ticketCategory->fresh()->sold_count);
        $this->assertEquals(50, $this->merchVariant->fresh()->stock);
    }

    /**
     * Test payment retry logic.
     */
    public function test_payment_retry_works_up_to_three_times(): void
    {
        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'snap_retry_count' => 0,
            'stock_reserved_until' => now()->addMinutes(15),
        ]);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['token' => 'new-snap-token'], 200),
        ]);

        // Attempt 1
        $response = $this->actingAs($this->user)->put(route('pesanan.retry', $order->id));
        $response->assertRedirect();
        $this->assertEquals(1, $order->fresh()->snap_retry_count);
        $this->assertEquals('new-snap-token', $order->fresh()->snap_token);

        // Attempt 2
        $response = $this->actingAs($this->user)->put(route('pesanan.retry', $order->id));
        $response->assertRedirect();
        $this->assertEquals(2, $order->fresh()->snap_retry_count);

        // Attempt 3
        $response = $this->actingAs($this->user)->put(route('pesanan.retry', $order->id));
        $response->assertRedirect();
        $this->assertEquals(3, $order->fresh()->snap_retry_count);

        // Attempt 4 should fail (cap is 3)
        $response = $this->actingAs($this->user)->put(route('pesanan.retry', $order->id));
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(3, $order->fresh()->snap_retry_count);
    }

    /**
     * Test checkout store rate limit.
     */
    public function test_checkout_store_is_rate_limited(): void
    {
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['token' => 'mock-snap-token'], 200),
        ]);

        $payload = [
            'event_id' => $this->event->id,
            'nama_lengkap' => 'Buyer Name',
            'email' => 'buyer@example.com',
            'no_telepon' => '081234567890',
            'tickets' => [$this->ticketCategory->id => 1],
            'holder_names' => [
                $this->ticketCategory->id => ['Holder One'],
            ],
        ];

        // Send 5 successful checkouts within a minute
        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($this->user)->post(route('checkout.store'), $payload);
            $response->assertRedirect();
        }

        // 6th checkout should trigger rate limit (429)
        $response = $this->actingAs($this->user)->post(route('checkout.store'), $payload);
        $response->assertStatus(429);
    }

    /**
     * Test active polling updates order to paid on show page.
     */
    public function test_detail_page_polls_midtrans_status_and_updates_order(): void
    {
        Notification::fake();

        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'total_amount' => 100000,
        ]);

        Http::fake([
            "https://api.sandbox.midtrans.com/v2/{$order->midtrans_order_id}/status" => Http::response([
                'transaction_status' => 'settlement',
                'payment_type' => 'credit_card',
                'transaction_id' => 'mock-trans-id-888',
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->get(route('pesanan.show', $order->id));

        $response->assertOk();
        $this->assertEquals(OrderStatus::Paid, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertEquals('credit_card', $order->fresh()->payment_type);
        $this->assertEquals('mock-trans-id-888', $order->fresh()->midtrans_transaction_id);

        Notification::assertSentTo($this->user, SendETicketNotification::class);
    }
}
