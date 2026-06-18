<?php

use App\Services\OcrService;

beforeEach(function () {
    $this->service = new OcrService(fallback: true);
});

it('normalizes plate numbers to uppercase', function () {
    expect($this->service->normalizePlateNumber('abc-1234'))->toBe('ABC1234')
        ->and($this->service->normalizePlateNumber('xyz  789'))->toBe('XYZ789')
        ->and($this->service->normalizePlateNumber('abc.123'))->toBe('ABC123')
        ->and($this->service->normalizePlateNumber(''))->toBeNull()
        ->and($this->service->normalizePlateNumber(null))->toBeNull();
});

it('handles empty or null input', function () {
    expect($this->service->normalizePlateNumber(''))->toBeNull()
        ->and($this->service->normalizePlateNumber('   '))->toBeNull();
});

it('estimates confidence based on alphanumeric ratio', function () {
    $reflection = new ReflectionMethod($this->service, 'estimateConfidence');

    expect($reflection->invoke($this->service, 'ABC123'))->toBe(99.99)
        ->and($reflection->invoke($this->service, ''))->toBe(0.0)
        ->and($reflection->invoke($this->service, '---'))->toBe(0.0);
});
