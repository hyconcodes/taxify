<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Taxify Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Vehicle Plate Number Recognition system.
    |
    */

    'ocr' => [
        'driver' => env('OCR_DRIVER', 'roboflow'),
        'timeout' => env('OCR_TIMEOUT', 120),
        'connect_timeout' => env('OCR_CONNECT_TIMEOUT', 10),
        'roboflow' => [
            'endpoint' => env('ROBOFLOW_ENDPOINT', 'https://serverless.roboflow.com/hycons-workspace/workflows/custom-workflow'),
        ],
    ],

];
