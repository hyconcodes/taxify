<?php

use App\Actions\Plate\MatchPlateAction;
use App\Models\PlateAlert;
use App\Models\PlateCapture;
use App\Models\User;
use App\Models\VehicleOwner;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('matches a capture to an existing vehicle', function () {
    $owner = VehicleOwner::factory()->create();
    $vehicle = $owner->vehicles()->create([
        'plate_number' => 'ABC1234',
        'make' => 'Toyota',
        'model' => 'Vios',
    ]);

    $capture = PlateCapture::factory()->create([
        'plate_number' => 'ABC1234',
        'is_matched' => false,
        'captured_by' => User::factory(),
    ]);

    $action = app(MatchPlateAction::class);
    $result = $action->execute($capture);

    expect($result->is_matched)->toBeTrue();
    expect(PlateAlert::count())->toBe(0);
});

it('creates an alert when no vehicle matches', function () {
    $capture = PlateCapture::factory()->create([
        'plate_number' => 'UNKNOWN',
        'is_matched' => false,
        'captured_by' => User::factory(),
    ]);

    $action = app(MatchPlateAction::class);
    $result = $action->execute($capture);

    expect($result->is_matched)->toBeFalse();
    expect(PlateAlert::count())->toBe(1);

    $alert = PlateAlert::first();
    expect($alert->status)->toBe('alert')
        ->and($alert->plate_capture_id)->toBe($capture->id);
});

it('does not create an alert when no plate was read', function () {
    $capture = PlateCapture::factory()->create([
        'plate_number' => null,
        'is_matched' => false,
        'captured_by' => User::factory(),
    ]);

    $action = app(MatchPlateAction::class);
    $result = $action->execute($capture);

    expect($result->is_matched)->toBeFalse();
    expect(PlateAlert::count())->toBe(0);
});
