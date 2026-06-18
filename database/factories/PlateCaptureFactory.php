<?php

namespace Database\Factories;

use App\Models\PlateCapture;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlateCapture>
 */
class PlateCaptureFactory extends Factory
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
            'image_path' => 'plate-captures/test.jpg',
            'confidence' => fake()->randomFloat(2, 70, 99),
            'is_matched' => false,
            'captured_by' => User::factory(),
            'captured_at' => now(),
        ];
    }
}
