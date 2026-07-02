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
        'roboflow' => [
            'endpoint' => env('ROBOFLOW_ENDPOINT', 'https://serverless.roboflow.com/hycons-workspace/workflows/custom-workflow'),
        ],
    ],

];
