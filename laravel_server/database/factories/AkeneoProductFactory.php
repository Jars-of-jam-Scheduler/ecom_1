<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AkeneoProduct>
 */
class AkeneoProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'reference' => Str::random(5),
            'code' => fake()->unique()->lexify('REF-????'),
			'description' => fake()->optional()->paragraph(),
			'name' => fake()->word(),
			'price_with_taxes' => fake()->randomFloat(2, 5, 10000),
			'type' => fake()->randomElement(['simple_product', 'service'])
        ];
    }
}
