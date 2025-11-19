<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+30 days');
        $endDate = fake()->dateTimeBetween($startDate, '+60 days');

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'start_datetime' => $startDate,
            'end_datetime' => $endDate,
            'is_active' => fake()->boolean(80),
        ];
    }
}
