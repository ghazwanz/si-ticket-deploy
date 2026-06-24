<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketCategoryFactory extends Factory
{
    protected $model = TicketCategory::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => $this->faker->randomElement(['VIP', 'Early Bird', 'General Admission', 'Presale 1', 'Presale 2']),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomElement([150000, 250000, 500000, 750000, 1000000]),
            'quota' => $this->faker->numberBetween(50, 500),
            'sold_count' => 0,
            'sale_start_at' => now()->subDays(5),
            'sale_end_at' => now()->addMonths(1),
            'is_active' => true,
            'max_per_user' => 4,
        ];
    }

    /**
     * Set the ticket category as sold out.
     */
    public function soldOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'sold_count' => $attributes['quota'] ?? 100,
        ]);
    }

    /**
     * Set the ticket category as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific max_per_user cap.
     */
    public function withMaxPerUser(int $cap): static
    {
        return $this->state(fn (array $attributes) => [
            'max_per_user' => $cap,
        ]);
    }

    /**
     * Remove the per-user cap (unlimited).
     */
    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_per_user' => null,
        ]);
    }
}
