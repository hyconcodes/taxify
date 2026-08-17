<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlateCapture extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'image_path',
        'annotated_image_path',
        'confidence',
        'is_matched',
        'captured_by',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'is_matched' => 'boolean',
            'confidence' => 'decimal:2',
            'captured_at' => 'datetime',
        ];
    }

    public function capturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'captured_by');
    }

    public function alert(): HasMany
    {
        return $this->hasMany(PlateAlert::class, 'plate_capture_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'plate_number', 'plate_number');
    }
}
