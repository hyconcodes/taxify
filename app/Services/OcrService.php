<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrService
{
    public function __construct(
        private readonly string $binary = 'tesseract',
        private readonly string $lang = 'eng',
        private readonly int $psm = 7,
        private readonly bool $fallback = true,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            binary: config('taxify.ocr.tesseract.binary', 'tesseract'),
            lang: config('taxify.ocr.tesseract.lang', 'eng'),
            psm: config('taxify.ocr.tesseract.psm', 7),
            fallback: config('taxify.ocr.fallback', true),
        );
    }

    public function recognize(UploadedFile $image): array
    {
        $path = $image->store('plate-captures', 'public');
        $fullPath = storage_path("app/public/{$path}");

        $text = $this->processImage($fullPath, $image->getClientOriginalName());

        $plateNumber = $this->normalizePlateNumber($text);

        return [
            'plate_number' => $plateNumber,
            'confidence' => $plateNumber !== null ? $this->estimateConfidence($text) : 0,
            'image_path' => $path,
        ];
    }

    private function processImage(string $imagePath, ?string $originalName = null): string
    {
        try {
            return (new TesseractOCR($imagePath))
                ->executable($this->binary)
                ->lang($this->lang)
                ->psm($this->psm)
                ->run();
        } catch (\Throwable $e) {
            if ($this->fallback) {
                return pathinfo($originalName ?? $imagePath, PATHINFO_FILENAME);
            }

            throw $e;
        }
    }

    public function normalizePlateNumber(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $cleaned = preg_replace('/[^A-Za-z0-9]/', '', $text);

        $result = strtoupper($cleaned);

        return $result !== '' ? $result : null;
    }

    private function estimateConfidence(string $rawText): float
    {
        $length = strlen(trim($rawText));

        if ($length === 0) {
            return 0;
        }

        $alphanumeric = preg_match_all('/[A-Za-z0-9]/', $rawText);
        $ratio = $alphanumeric / max($length, 1);

        return round(min(99.99, $ratio * 100), 2);
    }
}
