<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlateAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_capture_id',
        'vehicle_id',
        'status',
        'notes',
        'handled_by',
        'handled_at',
    ];

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
        ];
    }

    public function plateCapture(): BelongsTo
    {
        return $this->belongsTo(PlateCapture::class, 'plate_capture_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
