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
        'driver' => env('OCR_DRIVER', 'tesseract'),
        'tesseract' => [
            'binary' => env('TESSERACT_BINARY', 'tesseract'),
            'lang' => env('TESSERACT_LANG', 'eng'),
            'psm' => env('TESSERACT_PSM', 7),
        ],
        'fallback' => env('OCR_FALLBACK', true),
    ],

];
