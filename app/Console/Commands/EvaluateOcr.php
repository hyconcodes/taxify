<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EvaluateOcr extends Command
{
    protected $signature = 'ocr:evaluate
        {--ground-truth= : Path to ground truth CSV file}
        {--generate-csv : Generate a starter CSV with all original image filenames}
        {--output= : Path for results CSV output}';

    protected $description = 'Evaluate OCR performance against ground truth and calculate metrics';

    private string $diskPath;

    public function __construct()
    {
        parent::__construct();
        $this->diskPath = Storage::disk('public')->path('plate-captures');
    }

    public function handle(): int
    {
        if ($this->option('generate-csv')) {
            return $this->generateCsv();
        }

        $groundTruthPath = $this->option('ground-truth')
            ?? storage_path('app/evaluation/ground_truth.csv');

        if (! file_exists($groundTruthPath)) {
            $this->error("Ground truth file not found: {$groundTruthPath}");
            $this->line('Run <info>php artisan ocr:evaluate --generate-csv</info> to create a starter CSV.');

            return static::FAILURE;
        }

        $apiKey = config('services.roboflow_key');
        if (empty($apiKey)) {
            $this->error('ROBOFLOW_API_KEY is not set in .env');

            return static::FAILURE;
        }

        $endpoint = config('taxify.ocr.roboflow.endpoint');
        if (empty($endpoint)) {
            $this->error('ROBOFLOW_ENDPOINT is not set in .env');

            return static::FAILURE;
        }

        $groundTruth = $this->loadGroundTruth($groundTruthPath);
        if ($groundTruth === []) {
            $this->error('Ground truth CSV is empty or has no valid entries.');

            return static::FAILURE;
        }

        $this->info('Loaded '.count($groundTruth).' images from ground truth.');
        $this->line("Endpoint: {$endpoint}");
        $this->newLine();

        $results = [];
        $total = count($groundTruth);
        $current = 0;

        foreach ($groundTruth as $filename => $expectedPlate) {
            $current++;
            $this->line("[{$current}/{$total}] Processing: {$filename}");

            $result = $this->processImage($filename, $expectedPlate, $endpoint, $apiKey);
            $results[] = $result;

            $status = match (true) {
                $result['match'] => '<info>MATCH</info>',
                $result['recognized_plate'] === null && $expectedPlate === null => '<info>TRUE NEGATIVE</info>',
                $result['recognized_plate'] !== null && $expectedPlate === null => '<error>FALSE POSITIVE</error>',
                $result['recognized_plate'] === null && $expectedPlate !== null => '<error>FALSE NEGATIVE</error>',
                default => '<error>WRONG</error>',
            };

            $recognized = $result['recognized_plate'] ?? 'NONE';
            $expected = $expectedPlate ?? 'NO_PLATE';
            $this->line("  Expected: {$expected} | Got: {$recognized} | {$status}");
        }

        $this->newLine();
        $this->info('Calculating metrics...');

        $metrics = $this->calculateMetrics($results);

        $this->displayResults($metrics, $results);

        $outputPath = $this->option('output')
            ?? storage_path('app/evaluation/results.csv');

        $this->saveResults($results, $metrics, $outputPath);

        return static::SUCCESS;
    }

    private function generateCsv(): int
    {
        $files = collect(Storage::disk('public')->files('plate-captures'))
            ->filter(fn (string $path) => ! str_contains($path, '-annotated'))
            ->map(fn (string $path) => basename($path))
            ->sort()
            ->values();

        if ($files->isEmpty()) {
            $this->error('No original images found in storage/app/public/plate-captures/');

            return static::FAILURE;
        }

        $outputPath = storage_path('app/evaluation/ground_truth.csv');
        $this->makeDirectory($outputPath);

        $csv = "image_filename,ground_truth_plate\n";
        foreach ($files as $filename) {
            $csv .= "{$filename},\n";
        }

        file_put_contents($outputPath, $csv);

        $this->info("Created starter CSV with {$files->count()} images:");
        $this->line("  {$outputPath}");
        $this->newLine();
        $this->line('Fill in the ground_truth_plate column with the correct plate number for each image.');
        $this->line('Use NO_PLATE for images where no plate is visible.');
        $this->newLine();
        $this->table(['#', 'Image Filename'], $files->map(fn ($f, $i) => [$i + 1, $f])->toArray());

        return static::SUCCESS;
    }

    private function loadGroundTruth(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $data = [];
        $header = fgetcsv($handle); // skip header

        while (($row = fgetcsv($handle)) !== false) {
            $filename = trim($row[0] ?? '');
            $plate = trim($row[1] ?? '');

            if ($filename === '') {
                continue;
            }

            $plate = $plate !== '' && strtoupper($plate) !== 'NO_PLATE' ? strtoupper($plate) : null;
            $data[$filename] = $plate;
        }

        fclose($handle);

        return $data;
    }

    private function processImage(string $filename, ?string $expectedPlate, string $endpoint, string $apiKey): array
    {
        $imagePath = $this->diskPath.'/'.$filename;

        if (! file_exists($imagePath)) {
            return [
                'filename' => $filename,
                'expected_plate' => $expectedPlate,
                'recognized_plate' => null,
                'confidence' => 0,
                'car_found' => false,
                'plate_found' => false,
                'match' => false,
                'processing_time_ms' => 0,
                'message' => 'File not found',
                'correct_chars' => 0,
                'total_chars' => 0,
            ];
        }

        $imageData = file_get_contents($imagePath);
        $base64Image = base64_encode($imageData);

        $startTime = microtime(true);

        try {
            $response = Http::timeout(config('taxify.ocr.timeout', 120))
                ->connectTimeout(config('taxify.ocr.connect_timeout', 10))
                ->post($endpoint, [
                    'api_key' => $apiKey,
                    'inputs' => [
                        'image' => [
                            'type' => 'base64',
                            'value' => $base64Image,
                        ],
                    ],
                ]);

            $endTime = microtime(true);
            $processingTimeMs = round(($endTime - $startTime) * 1000);

            if (! $response->successful()) {
                return [
                    'filename' => $filename,
                    'expected_plate' => $expectedPlate,
                    'recognized_plate' => null,
                    'confidence' => 0,
                    'car_found' => false,
                    'plate_found' => false,
                    'match' => false,
                    'processing_time_ms' => $processingTimeMs,
                    'message' => 'HTTP '.$response->status(),
                    'correct_chars' => 0,
                    'total_chars' => 0,
                ];
            }

            $data = $response->json();
            $output = $data['outputs'][0] ?? $data[0] ?? null;

            if (! is_array($output)) {
                return [
                    'filename' => $filename,
                    'expected_plate' => $expectedPlate,
                    'recognized_plate' => null,
                    'confidence' => 0,
                    'car_found' => false,
                    'plate_found' => false,
                    'match' => false,
                    'processing_time_ms' => $processingTimeMs,
                    'message' => 'Invalid response format',
                    'correct_chars' => 0,
                    'total_chars' => 0,
                ];
            }

            $rawPlate = $output['license_plate_number'] ?? null;
            $normalizedPlate = $this->normalizePlate($rawPlate);
            $confidence = $output['plate_found'] && $normalizedPlate ? 99.99 : 0;
            $carFound = (bool) ($output['car_found'] ?? false);
            $plateFound = (bool) ($output['plate_found'] ?? false);
            $message = $output['message'] ?? null;

            $match = $normalizedPlate !== null
                && $expectedPlate !== null
                && $normalizedPlate === $expectedPlate;

            // True negative: no plate expected, none detected
            $trueNegative = $normalizedPlate === null && $expectedPlate === null;
            if ($trueNegative) {
                $match = true;
            }

            $correctChars = 0;
            $totalChars = 0;
            if ($expectedPlate !== null && $normalizedPlate !== null) {
                $totalChars = max(strlen($expectedPlate), strlen($normalizedPlate));
                $expectedChars = str_split($expectedPlate);
                $recognizedChars = str_split($normalizedPlate);
                for ($i = 0; $i < min(count($expectedChars), count($recognizedChars)); $i++) {
                    if ($expectedChars[$i] === $recognizedChars[$i]) {
                        $correctChars++;
                    }
                }
            }

            return [
                'filename' => $filename,
                'expected_plate' => $expectedPlate,
                'recognized_plate' => $normalizedPlate,
                'confidence' => $confidence,
                'car_found' => $carFound,
                'plate_found' => $plateFound,
                'match' => $match,
                'processing_time_ms' => $processingTimeMs,
                'message' => $message,
                'correct_chars' => $correctChars,
                'total_chars' => $totalChars,
            ];
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $processingTimeMs = round(($endTime - $startTime) * 1000);

            return [
                'filename' => $filename,
                'expected_plate' => $expectedPlate,
                'recognized_plate' => null,
                'confidence' => 0,
                'car_found' => false,
                'plate_found' => false,
                'match' => false,
                'processing_time_ms' => $processingTimeMs,
                'message' => $e->getMessage(),
                'correct_chars' => 0,
                'total_chars' => 0,
            ];
        }
    }

    private function normalizePlate(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return null;
        }

        $normalized = preg_replace('/[^A-Za-z0-9]/', '', $text);

        return $normalized !== '' ? strtoupper($normalized) : null;
    }

    private function calculateMetrics(array $results): array
    {
        $total = count($results);
        $tp = 0; // plate detected AND matches ground truth
        $fp = 0; // plate detected BUT wrong (or no plate in image)
        $fn = 0; // plate NOT detected BUT should have been
        $tn = 0; // no plate detected AND no plate in image
        $totalCorrectChars = 0;
        $totalChars = 0;
        $totalProcessingTime = 0;
        $platesDetected = 0;
        $platesExpected = 0;

        foreach ($results as $r) {
            $recognized = $r['recognized_plate'];
            $expected = $r['expected_plate'];
            $totalProcessingTime += $r['processing_time_ms'];

            if ($recognized !== null) {
                $platesDetected++;
            }

            if ($expected !== null) {
                $platesExpected++;
            }

            $totalCorrectChars += $r['correct_chars'];
            $totalChars += $r['total_chars'];

            if ($recognized !== null && $expected !== null && $recognized === $expected) {
                $tp++;
            } elseif ($recognized !== null && ($expected === null || $recognized !== $expected)) {
                $fp++;
            } elseif ($recognized === null && $expected !== null) {
                $fn++;
            } else {
                // Both null = true negative
                $tn++;
            }
        }

        $accuracy = $total > 0 ? (($tp + $tn) / $total) * 100 : 0;
        $precision = ($tp + $fp) > 0 ? ($tp / ($tp + $fp)) * 100 : 0;
        $recall = ($tp + $fn) > 0 ? ($tp / ($tp + $fn)) * 100 : 0;
        $f1Score = ($precision + $recall) > 0 ? (2 * $precision * $recall) / ($precision + $recall) : 0;
        $cra = $totalChars > 0 ? ($totalCorrectChars / $totalChars) * 100 : 0;
        $pdr = $total > 0 ? ($platesDetected / $total) * 100 : 0;
        $avgProcessingTime = $total > 0 ? $totalProcessingTime / $total : 0;
        $fpr = ($fp + $tn) > 0 ? ($fp / ($fp + $tn)) * 100 : 0;
        $fnr = ($fn + $tp) > 0 ? ($fn / ($fn + $tp)) * 100 : 0;

        return [
            'total_images' => $total,
            'true_positives' => $tp,
            'false_positives' => $fp,
            'false_negatives' => $fn,
            'true_negatives' => $tn,
            'plates_detected' => $platesDetected,
            'plates_expected' => $platesExpected,
            'accuracy' => round($accuracy, 2),
            'precision' => round($precision, 2),
            'recall' => round($recall, 2),
            'f1_score' => round($f1Score, 2),
            'cra' => round($cra, 2),
            'pdr' => round($pdr, 2),
            'avg_processing_time_ms' => round($avgProcessingTime, 2),
            'fpr' => round($fpr, 2),
            'fnr' => round($fnr, 2),
            'total_correct_chars' => $totalCorrectChars,
            'total_chars' => $totalChars,
        ];
    }

    private function displayResults(array $metrics, array $results): void
    {
        $this->newLine();
        $this->info('========================================');
        $this->info('  OCR PERFORMANCE EVALUATION RESULTS');
        $this->info('========================================');
        $this->newLine();

        $this->line('  <comment>Confusion Matrix:</comment>');
        $this->table(
            ['', 'Predicted: Plate', 'Predicted: No Plate'],
            [
                ['Actual: Plate', "<info>{$metrics['true_positives']}</info> TP", "<error>{$metrics['false_negatives']}</error> FP"],
                ['Actual: No Plate', "<error>{$metrics['false_positives']}</error> FN", "<info>{$metrics['true_negatives']}</info> TN"],
            ]
        );

        $this->line('  <comment>Key Metrics:</comment>');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Accuracy', "{$metrics['accuracy']}%"],
                ['Precision', "{$metrics['precision']}%"],
                ['Recall (Sensitivity)', "{$metrics['recall']}%"],
                ['F1-Score', "{$metrics['f1_score']}%"],
                ['Character Recognition Accuracy (CRA)', "{$metrics['cra']}%"],
                ['Plate Detection Rate (PDR)', "{$metrics['pdr']}%"],
                ['Average Processing Time', "{$metrics['avg_processing_time_ms']} ms"],
                ['False Positive Rate (FPR)', "{$metrics['fpr']}%"],
                ['False Negative Rate (FNR)', "{$metrics['fnr']}%"],
            ]
        );

        $this->line('  <comment>Summary:</comment>');
        $this->line("  Total images evaluated: {$metrics['total_images']}");
        $this->line("  Plates detected: {$metrics['plates_detected']}");
        $this->line("  Plates expected (ground truth): {$metrics['plates_expected']}");
        $this->line("  Correct characters: {$metrics['total_correct_chars']} / {$metrics['total_chars']}");
        $this->newLine();
    }

    private function saveResults(array $results, array $metrics, string $outputPath): void
    {
        $this->makeDirectory($outputPath);

        // Save per-image results
        $csv = "filename,expected_plate,recognized_plate,match,car_found,plate_found,confidence,processing_time_ms,correct_chars,total_chars,message\n";
        foreach ($results as $r) {
            $expected = $r['expected_plate'] ?? 'NO_PLATE';
            $recognized = $r['recognized_plate'] ?? 'NONE';
            $match = $r['match'] ? 'TRUE' : 'FALSE';
            $carFound = $r['car_found'] ? 'TRUE' : 'FALSE';
            $plateFound = $r['plate_found'] ? 'TRUE' : 'FALSE';
            $message = str_replace(',', ';', $r['message'] ?? '');

            $csv .= "{$r['filename']},{$expected},{$recognized},{$match},{$carFound},{$plateFound},{$r['confidence']},{$r['processing_time_ms']},{$r['correct_chars']},{$r['total_chars']},{$message}\n";
        }

        file_put_contents($outputPath, $csv);

        // Save metrics JSON
        $metricsPath = str_replace('.csv', '.json', $outputPath);
        file_put_contents($metricsPath, json_encode($metrics, JSON_PRETTY_PRINT));

        $this->info('Results saved to:');
        $this->line("  Per-image results: {$outputPath}");
        $this->line("  Metrics JSON:      {$metricsPath}");
    }

    private function makeDirectory(string $path): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
