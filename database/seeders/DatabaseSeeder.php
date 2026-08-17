<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleOwner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@taxify.com',
            'password' => Hash::make('password'),
        ]);

        $owners = VehicleOwner::factory()->count(15)->create();

        foreach ($owners as $owner) {
            Vehicle::factory()->create([
                'owner_id' => $owner->id,
            ]);
        }
    }
}
