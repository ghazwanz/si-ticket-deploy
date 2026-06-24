<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(OrderStatus::cases());

        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'status' => $status,
            'total_amount' => $this->faker->numberBetween(100000, 5000000),
            'payment_type' => $this->faker->randomElement(['gopay', 'shopeepay', 'bank_transfer', 'credit_card']),
            'snap_retry_count' => 0,
            'failed_at' => $status === OrderStatus::Failed ? now() : null,
            'cancelled_at' => $status === OrderStatus::Cancelled ? now() : null,
            'midtrans_order_id' => 'JF-'.strtoupper(Str::random(10)),
            'midtrans_transaction_id' => $status === OrderStatus::Paid ? Str::random(12) : null,
            'snap_token' => $status === OrderStatus::Pending ? Str::random(20) : null,
            'stock_reserved_until' => $status === OrderStatus::Pending ? now()->addMinutes(15) : null,
            'paid_at' => $status === OrderStatus::Paid ? now() : null,
        ];
    }

    /**
     * Set order as pending with active reservation.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Pending,
            'paid_at' => null,
            'failed_at' => null,
            'cancelled_at' => null,
            'snap_token' => Str::random(20),
            'stock_reserved_until' => now()->addMinutes(15),
        ]);
    }

    /**
     * Set order as paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
            'failed_at' => null,
            'cancelled_at' => null,
            'snap_token' => null,
            'stock_reserved_until' => null,
        ]);
    }

    /**
     * Set order as failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Failed,
            'paid_at' => null,
            'failed_at' => now(),
            'cancelled_at' => null,
            'snap_token' => null,
            'stock_reserved_until' => null,
        ]);
    }

    /**
     * Set order as cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled,
            'paid_at' => null,
            'failed_at' => null,
            'cancelled_at' => now(),
            'snap_token' => null,
            'stock_reserved_until' => null,
        ]);
    }

    /**
     * Set order as expired (pending with past reservation).
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Pending,
            'paid_at' => null,
            'failed_at' => null,
            'cancelled_at' => null,
            'snap_token' => Str::random(20),
            'stock_reserved_until' => now()->subMinutes(5),
        ]);
    }
}
