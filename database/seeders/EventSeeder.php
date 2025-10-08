<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\House;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $houses = House::all();

        foreach ($houses as $house) {
            // Create 2-3 events for each house
            Event::factory()->count(rand(2, 3))->create([
                'house_id' => $house->id,
            ]);
        }
    }
}
