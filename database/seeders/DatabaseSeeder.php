<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ShieldSeeder::class,
            HouseSeeder::class,
            //  EventSeeder::class,
        ]);

        $users = [
            ['name' => 'Faustino Vasquez', 'email' => 'vasquez.limon@gmail.com'],
            ['name' => 'Miguel Torres', 'email' => 'mtorres@serenacare.net'],
            ['name' => 'Carolina Molina', 'email' => 'cmolina@serenacare.net'],
        ];

        collect($users)->each(function ($user) {
            User::factory()->create($user);
        });

        User::find(1)->assignRole('super_admin');
        User::find(2)->assignRole('super_admin');
        User::find(3)->assignRole('super_admin');
    }
}
