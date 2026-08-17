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
                [
                    'success' => true,
                    'message' => 'Car and license plate detected successfully.',
                    'car_found' => true,
                    'plate_found' => true,
                    'license_plate_number' => 'M EV332E',
                    'output_image' => [
                        'type' => 'base64',
                        'value' => base64_encode("\xFF\xD8\xFFfake-annotated-jpeg"),
                    ],
                ],
            ],
            'profiler_trace' => [],
        ]),
    ]);

    $service = OcrService::fromConfig();

    $image = UploadedFile::fake()->image('plate.jpg');
    $result = $service->recognize($image);

    expect($result)->toHaveKeys(['plate_number', 'confidence', 'image_path', 'annotated_image_path', 'car_found', 'plate_found', 'message'])
        ->and($result['plate_number'])->toBe('MEV332E')
        ->and($result['confidence'])->toBe(99.99)
        ->and($result['car_found'])->toBeTrue()
        ->and($result['plate_found'])->toBeTrue()
        ->and($result['annotated_image_path'])->toEndWith('.jpg');

    Storage::disk('public')->assertExists($result['image_path']);
    Storage::disk('public')->assertExists($result['annotated_image_path']);
});

it('extracts the plate from the real roboflow workflow response shape', function () {
    Storage::fake('public');
    Http::fake([
        config('taxify.ocr.roboflow.endpoint') => Http::response([
            'outputs' => [
                [
                    'output_image' => [
                        'type' => 'base64',
                        'value' => base64_encode("\x89PNG\r\n\x1A\nfake-annotated-png"),
                    ],
                    'license_plate_number' => 'M EV332E',
                    'success' => true,
                    'message' => 'Car and license plate detected successfully.',
                    'car_found' => true,
                    'plate_found' => true,
                ],
            ],
            'profiler_trace' => [],
        ]),
    ]);

    $service = OcrService::fromConfig();

    $image = UploadedFile::fake()->image('plate.jpg');
    $result = $service->recognize($image);

    expect($result['plate_number'])->toBe('MEV332E')
        ->and($result['car_found'])->toBeTrue()
        ->and($result['plate_found'])->toBeTrue()
        ->and($result['annotated_image_path'])->toEndWith('.png');
});

it('returns null plate and message when no car is detected', function () {
    Storage::fake('public');
    Http::fake([
        config('taxify.ocr.roboflow.endpoint') => Http::response([
            'outputs' => [
                [
                    'success' => true,
                    'message' => 'No car was detected in the image. Please upload a clear photo containing a visible car.',
                    'car_found' => false,
                    'plate_found' => false,
                    'license_plate_number' => null,
                ],
            ],
            'profiler_trace' => [],
        ]),
    ]);

    $service = OcrService::fromConfig();

    $image = UploadedFile::fake()->image('plate.jpg');
    $result = $service->recognize($image);

    expect($result['plate_number'])->toBeNull()
        ->and($result['confidence'])->toBe(0)
        ->and($result['car_found'])->toBeFalse()
        ->and($result['plate_found'])->toBeFalse()
        ->and($result['message'])->toBe('No car was detected in the image. Please upload a clear photo containing a visible car.')
        ->and($result['annotated_image_path'])->toBeNull();

    Storage::disk('public')->assertExists($result['image_path']);
});

it('returns null plate when car detected but plate not recognized', function () {
    Storage::fake('public');
    Http::fake([
        config('taxify.ocr.roboflow.endpoint') => Http::response([
            'outputs' => [
                [
                    'success' => true,
                    'message' => 'Car detected but no license plate could be read.',
                    'car_found' => true,
                    'plate_found' => false,
                    'license_plate_number' => null,
                ],
            ],
            'profiler_trace' => [],
        ]),
    ]);

    $service = OcrService::fromConfig();

    $image = UploadedFile::fake()->image('plate.jpg');
    $result = $service->recognize($image);

    expect($result['plate_number'])->toBeNull()
        ->and($result['confidence'])->toBe(0)
        ->and($result['car_found'])->toBeTrue()
        ->and($result['plate_found'])->toBeFalse();

    Storage::disk('public')->assertExists($result['image_path']);
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
        ->and($result['confidence'])->toBe(0)
        ->and($result['car_found'])->toBeFalse()
        ->and($result['plate_found'])->toBeFalse()
        ->and($result['message'])->toBeNull();

    Storage::disk('public')->assertExists($result['image_path']);
});
