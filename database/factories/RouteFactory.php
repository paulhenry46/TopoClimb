<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Route>
 */
class RouteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->slug(),
            'local_id' => fake()->numberBetween(1, 100),
            'line_id' => \App\Models\Line::factory(),
            'grade' => fake()->numberBetween(1, 20),
            'color' => fake()->hexColor(),
            'comment' => fake()->sentence(),
        ];
    }
}
