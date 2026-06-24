<?php

declare(strict_types=1);

namespace Tests\Feature;

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
use Illuminate\Support\Str;
use Tests\TestCase;

class SendETicketNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test SendETicketNotification renders correct mail content.
     */
    public function test_e_ticket_notification_mail_renders_correctly(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $event = Event::factory()->create(['status' => 'published']);
        $ticketCategory = TicketCategory::factory()->create([
            'event_id' => $event->id,
            'price' => 100000,
        ]);

        $order = Order::factory()->paid()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'total_amount' => 120000,
        ]);

        $ticket = OrderTicket::create([
            'id' => (string) Str::uuid(),
            'order_id' => $order->id,
            'ticket_category_id' => $ticketCategory->id,
            'holder_name' => 'John Doe',
            'unit_price' => 100000,
            'qr_token' => 'TICKET-QR-123456',
        ]);

        $merchItem = MerchandiseItem::factory()->create([
            'event_id' => $event->id,
            'is_available' => true,
        ]);
        $merchVariant = MerchandiseVariant::factory()->create([
            'merchandise_item_id' => $merchItem->id,
            'stock' => 10,
        ]);

        $merch = OrderMerchandise::create([
            'id' => (string) Str::uuid(),
            'order_id' => $order->id,
            'merchandise_variant_id' => $merchVariant->id,
            'quantity' => 1,
            'unit_price' => 20000,
            'merch_token' => 'MERCH-QR-654321',
        ]);

        $notification = new SendETicketNotification($order);
        $mailMessage = $notification->toMail($user);

        // Inject a mock message object to simulate the MailChannel sending context
        $mailMessage->viewData['message'] = new class
        {
            public function embedData($data, $name, $contentType)
            {
                return 'cid:mocked-qr-code-cid';
            }
        };

        // Render the email markdown/view
        $html = (string) $mailMessage->render();

        // Check text content
        $this->assertStringContainsString('E-Tiket Acara:', $html);
        $this->assertStringContainsString($event->name, $html);
        $this->assertStringContainsString('John Doe', $html);
        $this->assertStringContainsString('TICKET-QR-123456', $html);
        $this->assertStringContainsString('MERCH-QR-654321', $html);

        // Check that raw HTML code block tags like "</div>" are not rendered as raw text, and that the CID image sources exist
        $this->assertStringNotContainsString('&lt;/div&gt;', $html);
        $this->assertStringNotContainsString('pre class="shiki"', $html); // Shiki / Code highlight checking
        $this->assertStringContainsString('src="cid:mocked-qr-code-cid"', $html);
        $this->assertStringContainsString('alt="QR Code"', $html);
    }
}
