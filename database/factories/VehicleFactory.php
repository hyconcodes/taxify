<?php

namespace Database\Factories;

use App\Enums\VehicleInsuranceStatus;
use App\Models\Vehicle;
use App\Models\VehicleOwner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plate_number' => strtoupper(fake()->bothify('???-####')),
            'vin_number' => strtoupper(fake()->bothify('################')),
            'make' => fake()->randomElement(['Toyota', 'Honda', 'Mitsubishi', 'Nissan', 'Suzuki', 'Ford']),
            'model' => fake()->randomElement(['Vios', 'Civic', 'Xpander', 'Navara', 'Ertiga', 'Ranger']),
            'year' => fake()->year(),
            'registration_date' => fake()->date(),
            'color' => fake()->randomElement(['White', 'Black', 'Silver', 'Red', 'Blue', 'Gray']),
            'type' => fake()->randomElement(['Sedan', 'SUV', 'Truck', 'Van', 'Motorcycle']),
            'insurance_status' => fake()->randomElement(VehicleInsuranceStatus::cases()),
            'owner_id' => VehicleOwner::factory(),
        ];
    }
}
