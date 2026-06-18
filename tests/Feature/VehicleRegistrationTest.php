<?php

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleOwner;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can register a vehicle with owner', function () {
    $response = $this->get(route('vehicles.create'));
    $response->assertOk();

    Livewire::test('pages::vehicles.create')
        ->set('plate_number', 'ABC-1234')
        ->set('make', 'Toyota')
        ->set('model', 'Vios')
        ->set('year', '2020')
        ->set('color', 'White')
        ->set('type', 'Sedan')
        ->set('owner_name', 'John Doe')
        ->set('owner_phone', '09171234567')
        ->set('owner_email', 'john@example.com')
        ->call('save')
        ->assertRedirect(route('vehicles.index'));

    expect(VehicleOwner::count())->toBe(1)
        ->and(Vehicle::count())->toBe(1);

    $vehicle = Vehicle::first();
    expect($vehicle->plate_number)->toBe('ABC-1234')
        ->and($vehicle->owner->name)->toBe('John Doe');
});

it('validates required fields', function () {
    Livewire::test('pages::vehicles.create')
        ->call('save')
        ->assertHasErrors([
            'plate_number' => 'required',
            'make' => 'required',
            'model' => 'required',
            'owner_name' => 'required',
            'owner_phone' => 'required',
        ]);
});

it('prevents duplicate plate numbers', function () {
    $owner = VehicleOwner::factory()->create();
    $owner->vehicles()->create([
        'plate_number' => 'ABC-1234',
        'make' => 'Toyota',
        'model' => 'Vios',
    ]);

    Livewire::test('pages::vehicles.create')
        ->set('plate_number', 'ABC-1234')
        ->set('make', 'Honda')
        ->set('model', 'Civic')
        ->set('owner_name', 'Jane Doe')
        ->set('owner_phone', '09171234568')
        ->call('save')
        ->assertHasErrors(['plate_number' => 'unique']);
});

it('lists registered vehicles', function () {
    $vehicles = Vehicle::factory(3)->create();

    Livewire::test('pages::vehicles.index')
        ->assertSee($vehicles[0]->plate_number)
        ->assertSee($vehicles[1]->plate_number)
        ->assertSee($vehicles[2]->plate_number);
});
