<?php

namespace Database\Factories;

use App\Models\MerchandiseVariant;
use App\Models\Order;
use App\Models\OrderMerchandise;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderMerchandiseFactory extends Factory
{
    protected $model = OrderMerchandise::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'merchandise_variant_id' => MerchandiseVariant::factory(),
            'merch_token' => Str::uuid(),
            'quantity' => $this->faker->numberBetween(1, 3),
            'unit_price' => 0, // Should be set based on variant
            'is_picked_up' => false,
            'picked_up_at' => null,
        ];
    }
}
