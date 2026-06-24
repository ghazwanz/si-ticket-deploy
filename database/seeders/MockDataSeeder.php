<?php

namespace Database\Seeders;

use App\Enums\CancellationRequestStatus;
use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Enums\PayoutType;
use App\Models\CancellationRequest;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\MerchandiseItem;
use App\Models\MerchandiseVariant;
use App\Models\Order;
use App\Models\OrderMerchandise;
use App\Models\OrderTicket;
use App\Models\Payout;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class MockDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure core categories and users exist
        $this->call(EventCategorySeeder::class);
        $categories = EventCategory::all();

        $admin = User::where('role', 'admin')->first() ?? User::factory()->admin()->create();
        $organizers = User::where('role', 'organizer')->get();
        if ($organizers->isEmpty()) {
            $organizers = collect([
                User::factory()->organizer()->create(),
                User::factory()->organizer()->create(),
                User::factory()->organizer()->create(),
            ]);
        }

        // Ensure organizer profiles exist
        foreach ($organizers as $org) {
            if (! $org->organizerProfile) {
                $org->organizerProfile()->create([
                    'organization_name' => 'Org '.$org->name,
                    'phone' => '0812'.rand(10000000, 99999999),
                    'bank_name' => 'BCA',
                    'bank_account_number' => '123'.rand(100000, 999999),
                    'bank_account_name' => 'Acc '.$org->name,
                ]);
            }
        }

        $customers = User::where('role', 'user')->get();
        if ($customers->count() < 10) {
            $customers = User::factory()->count(15)->create(['role' => 'user']);
        }

        // 2. Generate 10 events if we don't have enough
        $events = Event::all();
        if ($events->count() < 10) {
            $statuses = [EventStatus::Published, EventStatus::Completed, EventStatus::AwaitingCancellation, EventStatus::Cancelled];
            for ($i = 0; $i < 10; $i++) {
                $event = Event::factory()->create([
                    'organizer_id' => $organizers->random()->id,
                    'category_id' => $categories->random()->id,
                    'status' => $statuses[$i % count($statuses)],
                ]);

                // Create ticket categories
                $tCat1 = TicketCategory::factory()->create([
                    'event_id' => $event->id,
                    'name' => 'Regular',
                    'price' => rand(50000, 150000),
                ]);
                $tCat2 = TicketCategory::factory()->create([
                    'event_id' => $event->id,
                    'name' => 'VIP',
                    'price' => rand(200000, 500000),
                ]);

                // Create merchandise
                $mItem = MerchandiseItem::factory()->create([
                    'event_id' => $event->id,
                    'name' => 'Kaos Event '.$i,
                    'base_price' => rand(100000, 200000),
                ]);
                MerchandiseVariant::factory()->create([
                    'merchandise_item_id' => $mItem->id,
                    'variant_group' => 'Size',
                    'variant_value' => 'M',
                ]);
                MerchandiseVariant::factory()->create([
                    'merchandise_item_id' => $mItem->id,
                    'variant_group' => 'Size',
                    'variant_value' => 'L',
                ]);
            }
            $events = Event::all();
        }

        // 3. Generate 50 Checkout Orders
        $orderStatuses = OrderStatus::cases();
        for ($i = 0; $i < 50; $i++) {
            $event = $events->random();
            $customer = $customers->random();
            $status = $orderStatuses[$i % count($orderStatuses)];

            $order = Order::factory()->create([
                'user_id' => $customer->id,
                'event_id' => $event->id,
                'status' => $status,
                'paid_at' => $status === OrderStatus::Paid ? now()->subDays(rand(1, 10)) : null,
                'failed_at' => $status === OrderStatus::Failed ? now()->subDays(rand(1, 10)) : null,
                'cancelled_at' => $status === OrderStatus::Cancelled ? now()->subDays(rand(1, 10)) : null,
            ]);

            // Add 1-2 tickets
            $tCats = $event->ticketCategories;
            if ($tCats->isNotEmpty()) {
                $cat = $tCats->random();
                OrderTicket::factory()->create([
                    'order_id' => $order->id,
                    'ticket_category_id' => $cat->id,
                    'unit_price' => $cat->price,
                ]);
            }

            // Add 1-2 merch items
            $mVariants = MerchandiseVariant::whereHas('item', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })->get();

            if ($mVariants->isNotEmpty()) {
                $variant = $mVariants->random();
                OrderMerchandise::factory()->create([
                    'order_id' => $order->id,
                    'merchandise_variant_id' => $variant->id,
                    'unit_price' => $variant->item->base_price,
                    'quantity' => rand(1, 2),
                ]);
            }

            // Update order total amount
            $ticketsTotal = $order->tickets()->sum('unit_price');
            $merchTotal = $order->merchandise()->sum(\DB::raw('unit_price * quantity'));
            $order->update(['total_amount' => $ticketsTotal + $merchTotal]);
        }

        // 4. Generate 50 Payouts
        $payoutStatuses = PayoutStatus::cases();
        $payoutTypes = PayoutType::cases();
        for ($i = 0; $i < 50; $i++) {
            $event = $events->random();
            $organizer = $event->organizer;
            $status = $payoutStatuses[$i % count($payoutStatuses)];
            $type = $payoutTypes[$i % count($payoutTypes)];

            $payout = Payout::factory()->create([
                'event_id' => $event->id,
                'organizer_id' => $organizer->id,
                'payout_type' => $type,
                'status' => $status,
                'reviewed_by' => ! in_array($status, [PayoutStatus::Pending]) ? $admin->id : null,
                'reviewed_at' => ! in_array($status, [PayoutStatus::Pending]) ? now()->subDays(rand(1, 5)) : null,
                'disbursed_by' => $status === PayoutStatus::Completed ? $admin->id : null,
                'disbursed_at' => $status === PayoutStatus::Completed ? now()->subDays(rand(1, 3)) : null,
            ]);
        }

        // 5. Generate 50 Cancellation Requests
        $cancellationStatuses = CancellationRequestStatus::cases();
        for ($i = 0; $i < 50; $i++) {
            $event = $events->random();
            $organizer = $event->organizer;
            $status = $cancellationStatuses[$i % count($cancellationStatuses)];

            CancellationRequest::factory()->create([
                'event_id' => $event->id,
                'requested_by' => $organizer->id,
                'status' => $status,
                'reviewed_by' => ! in_array($status, [CancellationRequestStatus::Pending]) ? $admin->id : null,
                'reviewed_at' => ! in_array($status, [CancellationRequestStatus::Pending]) ? now()->subDays(rand(1, 5)) : null,
                'rejection_reason' => $status === CancellationRequestStatus::Rejected ? 'Event schedule conflict or inadequate explanation.' : null,
            ]);
        }
    }
}
