<?php

namespace Database\Factories;

use App\Models\VehicleOwner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleOwner>
 */
class VehicleOwnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'national_id' => fake()->unique()->numerify('##########'),
        ];
    }
}
