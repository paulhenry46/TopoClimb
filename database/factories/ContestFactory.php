<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contest>
 */
class ContestFactory extends Factory
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
            'description' => fake()->paragraph(),
            'start_date' => fake()->dateTimeBetween('now', '+1 month'),
            'end_date' => fake()->dateTimeBetween('+1 month', '+2 months'),
            'mode' => fake()->randomElement(['free', 'official']),
            'site_id' => \App\Models\Site::factory(),
            'use_dynamic_points' => fake()->boolean(),
            'team_mode' => fake()->boolean(),
            'team_points_mode' => 'unique',
        ];
    }
}
