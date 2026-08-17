<?php

namespace App\Actions\Plate;

use App\Models\PlateAlert;
use App\Models\PlateCapture;
use App\Models\Vehicle;

class MatchPlateAction
{
    public function execute(PlateCapture $capture): PlateCapture
    {
        $vehicle = Vehicle::where('plate_number', $capture->plate_number)->first();

        $capture->update(['is_matched' => $vehicle !== null]);

        if ($vehicle === null && $capture->plate_number !== null) {
            PlateAlert::create([
                'plate_capture_id' => $capture->id,
                'status' => 'alert',
                'notes' => "No registered vehicle matches plate: {$capture->plate_number}",
            ]);
        }

        return $capture->fresh();
    }
}
