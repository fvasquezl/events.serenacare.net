<?php

namespace Database\Factories;

use App\Models\House;
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
            'house_id' => House::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'image_path' => null,
            'start_datetime' => $startDate,
            'end_datetime' => $endDate,
            'is_active' => fake()->boolean(80),
        ];
    }
}
