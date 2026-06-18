<?php

use App\Services\OcrService;

it('normalizes plate numbers to uppercase', function () {
    $service = new OcrService;

    $reflection = new ReflectionMethod($service, 'normalizePlateNumber');

    expect($reflection->invoke($service, 'abc-1234'))->toBe('ABC1234')
        ->and($reflection->invoke($service, 'xyz  789'))->toBe('XYZ789')
        ->and($reflection->invoke($service, ''))->toBeNull()
        ->and($reflection->invoke($service, null))->toBeNull();
});

it('handles empty or null input', function () {
    $service = new OcrService;

    $reflection = new ReflectionMethod($service, 'normalizePlateNumber');

    expect($reflection->invoke($service, ''))->toBeNull()
        ->and($reflection->invoke($service, '   '))->toBeNull();
});
