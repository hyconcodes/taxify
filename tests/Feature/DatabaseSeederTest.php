<?php

use App\Models\Vehicle;
use App\Models\VehicleOwner;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds fifteen vehicle owners and vehicles', function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder'])
        ->assertOk();

    expect(VehicleOwner::query()->count())->toBe(15)
        ->and(Vehicle::query()->count())->toBe(15);
});
