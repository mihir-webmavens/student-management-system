<?php

namespace Database\Factories;

use App\Models\Standard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Standard>
 */
class StandardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Standard '.fake()->unique()->numberBetween(1, 1000),
            'sort_order' => fake()->numberBetween(1, 12),
        ];
    }
}
