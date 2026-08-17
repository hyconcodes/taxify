<?php

namespace App\Http\Controllers;

use App\Models\PlateCapture;
use Illuminate\Support\Facades\Storage;

class PlateImageController extends Controller
{
    /**
     * Stream the capture image from the public disk.
     */
    public function __invoke(PlateCapture $capture)
    {
        $path = $capture->annotated_image_path ?? $capture->image_path;

        abort_if($path === null || $path === 'manual-entry' || ! Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }
}
