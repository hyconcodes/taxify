<?php

use App\Models\User;
use App\Services\OcrService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('recognizes a plate via roboflow api', function () {
    Storage::fake('public');
    Http::fake([
        config('taxify.ocr.roboflow.endpoint') => Http::response([
            'outputs' => [
                ['license_plate_number' => 'ABC-1234'],
            ],
        ]),
    ]);

    $service = OcrService::fromConfig();

    $image = UploadedFile::fake()->image('plate.jpg');
    $result = $service->recognize($image);

    expect($result)->toHaveKeys(['plate_number', 'confidence', 'image_path'])
        ->and($result['plate_number'])->toBe('ABC1234')
        ->and($result['confidence'])->toBe(99.99);
    Storage::disk('public')->assertExists($result['image_path']);
});

it('handles empty roboflow response gracefully', function () {
    Storage::fake('public');
    Http::fake([
        config('taxify.ocr.roboflow.endpoint') => Http::response([
            'outputs' => [
                ['license_plate_number' => null],
            ],
        ]),
    ]);

    $service = OcrService::fromConfig();

    $image = UploadedFile::fake()->image('plate.jpg');
    $result = $service->recognize($image);

    expect($result['plate_number'])->toBeNull()
        ->and($result['confidence'])->toBe(0);
});

it('can be created from config defaults', function () {
    $service = OcrService::fromConfig();

    expect($service)->toBeInstanceOf(OcrService::class);
});

it('returns null plate on roboflow connection failure', function () {
    Storage::fake('public');
    Http::fake([
        config('taxify.ocr.roboflow.endpoint') => Http::response('Server Error', 500),
    ]);

    $service = OcrService::fromConfig();

    $image = UploadedFile::fake()->image('plate.jpg');
    $result = $service->recognize($image);

    expect($result['plate_number'])->toBeNull()
        ->and($result['confidence'])->toBe(0);
    Storage::disk('public')->assertExists($result['image_path']);
});
