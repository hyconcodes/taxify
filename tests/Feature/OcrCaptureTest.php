<?php

use App\Models\User;
use App\Services\OcrService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractNotFoundException;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('falls back to filename when tesseract is missing', function () {
    Storage::fake('public');

    $service = new OcrService(
        binary: 'non-existent-tesseract-binary',
        fallback: true,
    );

    $image = UploadedFile::fake()->image('ABC1234.jpg');
    $result = $service->recognize($image);

    expect($result)->toHaveKeys(['plate_number', 'confidence', 'image_path'])
        ->and($result['plate_number'])->toBe('ABC1234');
    Storage::disk('public')->assertExists($result['image_path']);
});

it('throws when tesseract is missing and fallback disabled', function () {
    Storage::fake('public');

    $service = new OcrService(
        binary: 'non-existent-tesseract-binary',
        fallback: false,
    );

    $image = UploadedFile::fake()->image('test.jpg');

    expect(fn () => $service->recognize($image))
        ->toThrow(TesseractNotFoundException::class);
});

it('can be created from config defaults', function () {
    $service = OcrService::fromConfig();

    expect($service)->toBeInstanceOf(OcrService::class);
});
