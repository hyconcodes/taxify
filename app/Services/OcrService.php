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
        private readonly int $timeout = 120,
        private readonly int $connectTimeout = 10,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            endpoint: config('taxify.ocr.roboflow.endpoint'),
            timeout: (int) config('taxify.ocr.timeout', 120),
            connectTimeout: (int) config('taxify.ocr.connect_timeout', 10),
        );
    }

    /**
     * @return array{
     *     plate_number: string|null,
     *     confidence: float,
     *     image_path: string,
     *     annotated_image_path: string|null,
     *     car_found: bool,
     *     plate_found: bool,
     *     message: string|null,
     * }
     */
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
            ])
                ->connectTimeout($this->connectTimeout)
                ->timeout($this->timeout)
                ->post($this->endpoint, [
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
            $output = $data['outputs'][0] ?? null;

            if (! is_array($output)) {
                $output = (is_array($data) && array_is_list($data)) ? ($data[0] ?? []) : $data;
            }

            $success = (bool) ($output['success'] ?? false);
            $carFound = (bool) ($output['car_found'] ?? false);
            $plateFound = (bool) ($output['plate_found'] ?? false);
            $message = $output['message'] ?? null;
            $plateNumber = $this->normalizePlateNumber($output['license_plate_number'] ?? null);
            $annotatedImagePath = $this->storeAnnotatedImage($output, $path);

            Log::info('OcrService: plate extracted', [
                'success' => $success,
                'car_found' => $carFound,
                'plate_found' => $plateFound,
                'message' => $message,
                'raw_text' => $output['license_plate_number'] ?? null,
                'normalized' => $plateNumber,
            ]);

            return [
                'plate_number' => $plateNumber,
                'confidence' => $success && $plateFound && $plateNumber !== null ? 99.99 : 0,
                'image_path' => $path,
                'annotated_image_path' => $annotatedImagePath,
                'car_found' => $carFound,
                'plate_found' => $plateFound,
                'message' => $message,
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
                'annotated_image_path' => null,
                'car_found' => false,
                'plate_found' => false,
                'message' => null,
            ];
        }
    }

    private function storeAnnotatedImage(array $data, string $originalPath): ?string
    {
        $annotated = $data['output_image']['value'] ?? null;

        if (! is_string($annotated) || $annotated === '') {
            return null;
        }

        $decoded = base64_decode($annotated, strict: true);

        if ($decoded === false) {
            return null;
        }

        $name = pathinfo($originalPath, PATHINFO_FILENAME).'-annotated'.$this->detectImageExtension($decoded);

        Storage::disk('public')->put('plate-captures/'.$name, $decoded);

        return 'plate-captures/'.$name;
    }

    private function detectImageExtension(string $data): string
    {
        $prefix = substr($data, 0, 12);

        if (str_starts_with($prefix, "\xFF\xD8\xFF")) {
            return '.jpg';
        }

        if (str_starts_with($prefix, "\x89PNG")) {
            return '.png';
        }

        if (str_starts_with($prefix, 'RIFF') && substr($prefix, 8, 4) === 'WEBP') {
            return '.webp';
        }

        return '.png';
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
