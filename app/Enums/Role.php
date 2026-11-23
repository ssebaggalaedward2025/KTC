<?php

namespace App\Enums;

/**
 * Phase 1 roles. Roles are stored as data (spatie/laravel-permission), so
 * Phase 2 roles (structural_engineer, surveyor, finance_officer, inspector)
 * are added by extending this enum and re-running the role seeder.
 */
enum Role: string
{
    case SystemAdmin = 'system_admin';
    case TownEngineer = 'town_engineer';
    case RecordsOfficer = 'records_officer';
    case BuildingControlOfficer = 'building_control_officer';
    case PhysicalPlanner = 'physical_planner';
    case BuildingCommittee = 'building_committee';
    case CommitteeSecretary = 'committee_secretary';
    case Applicant = 'applicant';

    public function label(): string
    {
        return match ($this) {
            self::SystemAdmin => 'System Administrator',
            self::TownEngineer => 'Town Engineer',
            self::RecordsOfficer => 'Records Officer',
            self::BuildingControlOfficer => 'Building Control Officer',
            self::PhysicalPlanner => 'Physical Planner',
            self::BuildingCommittee => 'Building Committee Member',
            self::CommitteeSecretary => 'Committee Secretary',
            self::Applicant => 'Applicant',
        };
    }

    public function isStaff(): bool
    {
        return $this !== self::Applicant;
    }

    /** @return list<string> */
    public static function staffValues(): array
    {
        return array_values(array_map(
            fn (self $r) => $r->value,
            array_filter(self::cases(), fn (self $r) => $r->isStaff()),
        ));
    }
}
