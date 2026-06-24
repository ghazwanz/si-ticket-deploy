<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderTicket;
use App\Models\TicketCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderTicketFactory extends Factory
{
    protected $model = OrderTicket::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'ticket_category_id' => TicketCategory::factory(),
            'qr_token' => (string) Str::uuid(),
            'holder_name' => $this->faker->name(),
            'unit_price' => 0,
            'is_checked_in' => false,
            'checked_in_at' => null,
        ];
    }
}
