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
			'description' => fake()->paragraph(),
			'name' => fake()->word(),
        ];
    }
}
