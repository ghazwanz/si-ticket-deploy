<?php

namespace Database\Factories;

use App\Enums\OrganizerStatus;
use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizerProfile>
 */
class OrganizerProfileFactory extends Factory
{
    protected $model = OrganizerProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->organizer(),
            'organization_name' => $this->faker->company(),
            'phone' => $this->faker->phoneNumber(),
            'bank_name' => $this->faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB Niaga']),
            'bank_account_number' => $this->faker->bankAccountNumber(),
            'bank_account_name' => $this->faker->name(),
            'organization_address' => $this->faker->address(),
            'official_contact' => $this->faker->companyEmail(),
            'legality_document' => 'legality_documents/fake.pdf',
            'status' => OrganizerStatus::Pending,
        ];
    }

    /**
     * State for organizers with missing bank details.
     */
    public function missingBankDetails(): static
    {
        return $this->state(fn (array $attributes) => [
            'bank_name' => null,
            'bank_account_number' => null,
            'bank_account_name' => null,
        ]);
    }
}
