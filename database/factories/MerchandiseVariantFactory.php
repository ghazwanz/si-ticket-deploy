<?php

namespace Database\Factories;

use App\Models\MerchandiseItem;
use App\Models\MerchandiseVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class MerchandiseVariantFactory extends Factory
{
    protected $model = MerchandiseVariant::class;

    public function definition(): array
    {
        return [
            'merchandise_item_id' => MerchandiseItem::factory(),
            'variant_group' => 'Size',
            'variant_value' => $this->faker->word().' '.$this->faker->randomNumber(5),
            'price_adjustment' => $this->faker->randomElement([0, 0, 0, 10000, 20000]),
            'stock' => $this->faker->numberBetween(10, 100),
        ];
    }

    /**
     * Set the variant as out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }
}
