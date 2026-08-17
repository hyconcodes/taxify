<?php

namespace App\Enums;

enum VehicleInsuranceStatus: string
{
    case Valid = 'valid';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Valid => 'Valid',
            self::Expired => 'Expired',
        };
    }
}
