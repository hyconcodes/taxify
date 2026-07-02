<?php

use App\Services\OcrService;

beforeEach(function () {
    $this->service = new OcrService(endpoint: 'https://example.com/test');
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
