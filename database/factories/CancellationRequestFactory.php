<?php

namespace Database\Factories;

use App\Enums\CancellationRequestStatus;
use App\Models\CancellationRequest;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CancellationRequest>
 */
class CancellationRequestFactory extends Factory
{
    protected $model = CancellationRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'requested_by' => User::factory()->organizer(),
            'reason' => $this->faker->paragraph(2),
            'status' => CancellationRequestStatus::Pending,
            'reviewed_by' => null,
            'rejection_reason' => null,
            'reviewed_at' => null,
        ];
    }

    /**
     * State for approved cancellation requests.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CancellationRequestStatus::Approved,
            'reviewed_by' => User::factory()->admin(),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * State for rejected cancellation requests.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CancellationRequestStatus::Rejected,
            'reviewed_by' => User::factory()->admin(),
            'rejection_reason' => $this->faker->sentence(10),
            'reviewed_at' => now(),
        ]);
    }
}
