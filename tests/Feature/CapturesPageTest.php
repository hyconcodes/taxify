<?php

use App\Models\PlateCapture;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function fakeRoboflow(array $output): void
{
    Http::fake([
        config('taxify.ocr.roboflow.endpoint') => Http::response([
            'outputs' => [$output],
            'profiler_trace' => [],
        ]),
    ]);
}

function uploadPlateImage(): UploadedFile
{
    $image = UploadedFile::fake()->image('plate.jpg');

    return $image;
}

it('shows the annotated result immediately after a successful capture', function () {
    Storage::fake('public');
    fakeRoboflow([
        'success' => true,
        'message' => 'Car and license plate detected successfully.',
        'car_found' => true,
        'plate_found' => true,
        'license_plate_number' => 'M EV332E',
        'output_image' => [
            'type' => 'base64',
            'value' => base64_encode("\xFF\xD8\xFFfake-annotated-jpeg"),
        ],
    ]);

    $component = Livewire::test('pages::captures.index')
        ->set('image', uploadPlateImage())
        ->call('capture');

    $component
        ->assertSet('result.plate_number', 'MEV332E')
        ->assertSet('result.confidence', 99.99)
        ->assertSet('result.annotated_image_path', fn ($path) => str_ends_with($path, '.jpg'))
        ->assertSee('MEV332E')
        ->assertSee('Recognition Result');
});

it('does not show a result when no plate is recognized', function () {
    Storage::fake('public');
    fakeRoboflow([
        'success' => true,
        'message' => 'No car was detected in the image. Please upload a clear photo containing a visible car.',
        'car_found' => false,
        'plate_found' => false,
        'license_plate_number' => null,
    ]);

    $component = Livewire::test('pages::captures.index')
        ->set('image', uploadPlateImage())
        ->call('capture');

    $component
        ->assertSet('result', null)
        ->assertDontSee('Recognition Result');
});

it('serves the capture image through the web route', function () {
    Storage::fake('public');

    $capture = PlateCapture::factory()->create([
        'image_path' => 'plate-captures/original.jpg',
    ]);

    Storage::disk('public')->put($capture->image_path, 'fake-image-bytes');

    $this->get(route('captures.image', $capture))
        ->assertOk();
});

it('serves the annotated image when available', function () {
    Storage::fake('public');

    $capture = PlateCapture::factory()->create([
        'image_path' => 'plate-captures/original.jpg',
        'annotated_image_path' => 'plate-captures/original-annotated.jpg',
    ]);

    Storage::disk('public')->put($capture->annotated_image_path, 'fake-annotated-bytes');

    $this->get(route('captures.image', $capture))
        ->assertOk();
});

it('returns 404 for manual-entry captures', function () {
    Storage::fake('public');

    $capture = PlateCapture::factory()->create([
        'image_path' => 'manual-entry',
    ]);

    $this->get(route('captures.image', $capture))
        ->assertNotFound();
});
