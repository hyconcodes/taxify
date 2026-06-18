<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'make',
        'model',
        'year',
        'color',
        'type',
        'owner_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(VehicleOwner::class, 'owner_id');
    }

    public function plateAlerts(): HasMany
    {
        return $this->hasMany(PlateAlert::class, 'vehicle_id');
    }
}
