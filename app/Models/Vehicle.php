<?php

namespace App\Models;

use App\Enums\VehicleInsuranceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'vin_number',
        'make',
        'model',
        'year',
        'registration_date',
        'color',
        'type',
        'insurance_status',
        'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'insurance_status' => VehicleInsuranceStatus::class,
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(VehicleOwner::class, 'owner_id');
    }

    public function plateAlerts(): HasMany
    {
        return $this->hasMany(PlateAlert::class, 'vehicle_id');
    }
}
