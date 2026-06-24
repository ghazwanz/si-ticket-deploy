<?php

namespace Tests\Feature\Organizer;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\MerchandiseItem;
use App\Models\MerchandiseVariant;
use App\Models\Order;
use App\Models\OrderMerchandise;
use App\Models\OrderTicket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ScannerTest extends TestCase
{
    use RefreshDatabase;

    private User $organizer;

    private Event $event;

    private TicketCategory $ticketCategory;

    private MerchandiseItem $merchItem;

    private MerchandiseVariant $merchVariant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizer = User::factory()->create(['role' => UserRole::Organizer]);

        $this->event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => EventStatus::Published,
        ]);

        $this->ticketCategory = TicketCategory::factory()->create([
            'event_id' => $this->event->id,
            'price' => 100000,
            'quota' => 100,
        ]);

        $this->merchItem = MerchandiseItem::factory()->create([
            'event_id' => $this->event->id,
            'is_available' => true,
        ]);

        $this->merchVariant = MerchandiseVariant::factory()->create([
            'merchandise_item_id' => $this->merchItem->id,
            'stock' => 50,
        ]);
    }

    /**
     * Test organizer can access scanner index page and sees selection when multiple events exist.
     */
    public function test_organizer_can_access_scanner_page_with_multiple_events(): void
    {
        // Create a second event to prevent auto-selection
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => EventStatus::Published,
        ]);

        $response = $this->actingAs($this->organizer)->get(route('organizer.scanner.index'));
        $response->assertOk();
        $response->assertSee('Pilih Acara Terlebih Dahulu');
    }

    /**
     * Test organizer index page auto-selects event if exactly one active event exists.
     */
    public function test_organizer_with_single_event_is_auto_selected(): void
    {
        $response = $this->actingAs($this->organizer)->get(route('organizer.scanner.index'));
        $response->assertOk();
        $response->assertSessionHas('active_event_id', $this->event->id);
        $response->assertSee($this->event->name);
    }

    /**
     * Test organizer can select active event context.
     */
    public function test_organizer_can_select_event_context(): void
    {
        // Create a second event so selection works
        $event2 = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => EventStatus::Published,
        ]);

        $response = $this->actingAs($this->organizer)->post(route('organizer.scanner.select'), [
            'event_id' => $event2->id,
        ]);

        $response->assertRedirect(route('organizer.scanner.index'));
        $response->assertSessionHas('active_event_id', $event2->id);

        // Accessing the page now should show the active event details
        $response2 = $this->actingAs($this->organizer)
            ->withSession(['active_event_id' => $event2->id])
            ->get(route('organizer.scanner.index'));
        $response2->assertOk();
        $response2->assertSee($event2->name);
        $response2->assertSee('Pindai Tiket Masuk');
    }

    /**
     * Test selectEvent supports AJAX.
     */
    public function test_select_event_supports_ajax(): void
    {
        $response = $this->actingAs($this->organizer)->postJson(route('organizer.scanner.select'), [
            'event_id' => $this->event->id,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertSessionHas('active_event_id', $this->event->id);
    }

    /**
     * Test scan validation requires event context in session.
     */
    public function test_validation_requires_event_context(): void
    {
        $response = $this->actingAs($this->organizer)->postJson(route('organizer.scanner.validate'), [
            'mode' => 'gate',
            'token' => 'dummy-token',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Silakan pilih event terlebih dahulu.',
        ]);
    }

    /**
     * Test scan validation fails if event is cancelled.
     */
    public function test_scan_validation_fails_if_event_cancelled(): void
    {
        // Cancel the event
        $this->event->update(['status' => EventStatus::Cancelled]);

        // Put in session
        $response = $this->actingAs($this->organizer)
            ->withSession(['active_event_id' => $this->event->id])
            ->postJson(route('organizer.scanner.validate'), [
                'mode' => 'gate',
                'token' => 'dummy-token',
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Event ini telah dibatalkan. Pemindaian tidak diizinkan.',
        ]);
    }

    /**
     * Test gate check-in processes valid paid tickets with confirmation step.
     */
    public function test_gate_checkin_validates_and_marks_checked_in(): void
    {
        $buyer = User::factory()->create([
            'role' => UserRole::User,
            'name' => 'Buyer Name',
        ]);

        // Paid Order
        $order = Order::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $buyer->id,
            'status' => OrderStatus::Paid,
        ]);

        $ticket = OrderTicket::factory()->create([
            'order_id' => $order->id,
            'ticket_category_id' => $this->ticketCategory->id,
            'qr_token' => (string) Str::uuid(),
            'is_checked_in' => false,
        ]);

        // Step 1: Read-Only Verification
        $response1 = $this->actingAs($this->organizer)
            ->withSession(['active_event_id' => $this->event->id])
            ->postJson(route('organizer.scanner.validate'), [
                'mode' => 'gate',
                'token' => $ticket->qr_token,
                'confirm' => false,
            ]);

        $response1->assertOk();
        $response1->assertJson([
            'success' => true,
            'status' => 'pending_confirmation',
            'name' => $ticket->holder_name,
            'detail' => $this->ticketCategory->name,
        ]);

        $this->assertFalse($ticket->fresh()->is_checked_in);

        // Step 2: Confirm check-in
        $response2 = $this->actingAs($this->organizer)
            ->withSession(['active_event_id' => $this->event->id])
            ->postJson(route('organizer.scanner.validate'), [
                'mode' => 'gate',
                'token' => $ticket->qr_token,
                'confirm' => true,
            ]);

        $response2->assertOk();
        $response2->assertJson([
            'success' => true,
            'status' => 'confirmed',
            'message' => 'Check-in berhasil!',
            'name' => $ticket->holder_name,
            'detail' => $this->ticketCategory->name,
        ]);

        $this->assertTrue($ticket->fresh()->is_checked_in);
        $this->assertNotNull($ticket->fresh()->checked_in_at);
    }

    /**
     * Test check-in fails if order is unpaid.
     */
    public function test_gate_checkin_fails_if_unpaid(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::User]);

        $order = Order::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $buyer->id,
            'status' => OrderStatus::Pending,
        ]);

        $ticket = OrderTicket::factory()->create([
            'order_id' => $order->id,
            'ticket_category_id' => $this->ticketCategory->id,
            'qr_token' => (string) Str::uuid(),
            'is_checked_in' => false,
        ]);

        $response = $this->actingAs($this->organizer)
            ->withSession(['active_event_id' => $this->event->id])
            ->postJson(route('organizer.scanner.validate'), [
                'mode' => 'gate',
                'token' => $ticket->qr_token,
                'confirm' => true,
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Pembayaran order tiket ini belum lunas.',
        ]);
    }

    /**
     * Test double scan checkin fails.
     */
    public function test_gate_checkin_fails_if_already_scanned(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::User]);

        $order = Order::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $buyer->id,
            'status' => OrderStatus::Paid,
        ]);

        $ticket = OrderTicket::factory()->create([
            'order_id' => $order->id,
            'ticket_category_id' => $this->ticketCategory->id,
            'qr_token' => (string) Str::uuid(),
            'is_checked_in' => true,
            'checked_in_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($this->organizer)
            ->withSession(['active_event_id' => $this->event->id])
            ->postJson(route('organizer.scanner.validate'), [
                'mode' => 'gate',
                'token' => $ticket->qr_token,
                'confirm' => true,
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('Tiket sudah digunakan', $response->json('message'));
    }

    /**
     * Test wrong event returns metadata for organizer owned events.
     */
    public function test_wrong_event_returns_metadata_for_organizer_events(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::User]);

        $event2 = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => EventStatus::Published,
        ]);

        $ticketCategory2 = TicketCategory::factory()->create([
            'event_id' => $event2->id,
            'price' => 100000,
            'quota' => 100,
        ]);

        $order = Order::factory()->create([
            'event_id' => $event2->id,
            'user_id' => $buyer->id,
            'status' => OrderStatus::Paid,
        ]);

        $ticket = OrderTicket::factory()->create([
            'order_id' => $order->id,
            'ticket_category_id' => $ticketCategory2->id,
            'qr_token' => (string) Str::uuid(),
            'is_checked_in' => false,
        ]);

        $response = $this->actingAs($this->organizer)
            ->withSession(['active_event_id' => $this->event->id])
            ->postJson(route('organizer.scanner.validate'), [
                'mode' => 'gate',
                'token' => $ticket->qr_token,
                'confirm' => false,
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Tiket ini terdaftar untuk event lain milik Anda.',
            'wrong_event' => true,
            'target_event_id' => $event2->id,
            'target_event_name' => $event2->name,
        ]);
    }

    /**
     * Test merchandise claim redemption.
     */
    public function test_merchandise_redemption_succeeds(): void
    {
        $buyer = User::factory()->create([
            'role' => UserRole::User,
            'name' => 'Buyer Name',
        ]);

        $order = Order::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $buyer->id,
            'status' => OrderStatus::Paid,
        ]);

        $merch = OrderMerchandise::factory()->create([
            'order_id' => $order->id,
            'merchandise_variant_id' => $this->merchVariant->id,
            'merch_token' => (string) Str::uuid(),
            'quantity' => 2,
            'is_picked_up' => false,
        ]);

        // Step 1: Verification
        $response1 = $this->actingAs($this->organizer)
            ->withSession(['active_event_id' => $this->event->id])
            ->postJson(route('organizer.scanner.validate'), [
                'mode' => 'merchandise',
                'token' => $merch->merch_token,
                'confirm' => false,
            ]);

        $response1->assertOk();
        $response1->assertJson([
            'success' => true,
            'status' => 'pending_confirmation',
            'name' => $buyer->name,
            'detail' => $this->merchItem->name.' ('.$this->merchVariant->name.') × 2',
        ]);

        $this->assertFalse($merch->fresh()->is_picked_up);

        // Step 2: Confirm
        $response2 = $this->actingAs($this->organizer)
            ->withSession(['active_event_id' => $this->event->id])
            ->postJson(route('organizer.scanner.validate'), [
                'mode' => 'merchandise',
                'token' => $merch->merch_token,
                'confirm' => true,
            ]);

        $response2->assertOk();
        $response2->assertJson([
            'success' => true,
            'status' => 'confirmed',
            'message' => 'Merchandise berhasil diklaim!',
            'name' => $buyer->name,
            'detail' => $this->merchItem->name.' ('.$this->merchVariant->name.') × 2',
        ]);

        $this->assertTrue($merch->fresh()->is_picked_up);
        $this->assertNotNull($merch->fresh()->picked_up_at);
    }
}
