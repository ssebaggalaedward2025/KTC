<?php

namespace App\Enums;

enum ApplicantType: string
{
    case Individual = 'individual';
    case Company = 'company';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Individual',
            self::Company => 'Company / organisation',
        };
    }
}
