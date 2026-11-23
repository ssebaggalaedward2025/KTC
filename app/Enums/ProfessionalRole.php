<?php

namespace App\Enums;

enum ProfessionalRole: string
{
    case Architect = 'architect';
    case StructuralEngineer = 'structural_engineer';
    case Surveyor = 'surveyor';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Architect => 'Architect',
            self::StructuralEngineer => 'Structural engineer',
            self::Surveyor => 'Surveyor',
            self::Other => 'Other professional',
        };
    }
}
