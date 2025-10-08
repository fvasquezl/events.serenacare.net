<?php

namespace Database\Seeders;

use App\Models\House;
use Illuminate\Database\Seeder;

class HouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $houses = [
            [
                'name' => 'Casa Tijuana',
                'slug' => 'tijuana',
                'location' => 'Tijuana, Baja California',
            ],
            [
                'name' => 'Casa Rosarito',
                'slug' => 'rosarito',
                'location' => 'Rosarito, Baja California',
            ],
            [
                'name' => 'Casa Cuesta Blanca',
                'slug' => 'cuesta-blanca',
                'location' => 'Cuesta Blanca, Baja California',
            ],
        ];

        foreach ($houses as $house) {
            House::create($house);
        }
    }
}
