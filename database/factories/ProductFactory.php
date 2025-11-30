<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $initial = 10;
        return [
            'title' => $this->faker->word(),
            'initial_stock' => $initial,
            'available_stock' => $initial,
            'price' => $this->faker->numberBetween(1000, 10000),
        ];
    }
}