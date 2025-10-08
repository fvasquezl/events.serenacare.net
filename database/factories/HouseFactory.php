<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\House>
 */
class HouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Tijuana', 'Rosarito', 'Cuesta Blanca', 'Ensenada', 'Tecate']);

        return [
            'name' => "Casa {$name}",
            'slug' => \Illuminate\Support\Str::slug($name),
            'location' => fake()->city().', '.fake()->state(),
            'default_image_path' => null,
        ];
    }
}
