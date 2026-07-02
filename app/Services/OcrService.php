<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OcrService
{
    public function __construct(
        private readonly string $endpoint,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            endpoint: config('taxify.ocr.roboflow.endpoint'),
        );
    }

    public function recognize(UploadedFile $image): array
    {
        $path = $image->store('plate-captures', 'public');
        $base64 = base64_encode(Storage::disk('public')->get($path));

        Log::info('OcrService: sending image to Roboflow', [
            'image_path' => $path,
            'endpoint' => $this->endpoint,
            'image_size' => strlen($base64),
        ]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->endpoint, [
                'api_key' => config('services.roboflow_key'),
                'inputs' => [
                    'image' => [
                        'type' => 'base64',
                        'value' => $base64,
                    ],
                ],
            ]);

            Log::info('OcrService: Roboflow response received', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $response->throw();

            $data = $response->json();
            $plateText = $data['outputs'][0]['license_plate_number'] ?? null;

            Log::info('OcrService: plate extracted', [
                'raw_text' => $plateText,
                'normalized' => $this->normalizePlateNumber($plateText),
            ]);

            $plateNumber = $this->normalizePlateNumber($plateText);

            return [
                'plate_number' => $plateNumber,
                'confidence' => $plateNumber !== null ? 99.99 : 0,
                'image_path' => $path,
            ];
        } catch (\Throwable $e) {
            Log::error('OcrService: Roboflow request failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'plate_number' => null,
                'confidence' => 0,
                'image_path' => $path,
            ];
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
}
