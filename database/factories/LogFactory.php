<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Log>
 */
class LogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'route_id' => \App\Models\Route::factory(),
            'type' => fake()->randomElement(['work', 'flash', 'view', 'tentative']),
            'way' => fake()->randomElement(['top-rope', 'lead', 'bouldering']),
            'grade' => fake()->numberBetween(300, 900),
            'comment' => fake()->optional()->sentence(),
            'video_url' => fake()->optional()->url(),
            'is_public' => function (array $attributes) {
                return $attributes['type'] !== 'tentative';
            },
        ];
    }
}
