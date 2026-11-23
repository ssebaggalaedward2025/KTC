<?php

namespace App\Support;

use App\Enums\Role;

/**
 * Catalogue of permissions and the default role grants.
 *
 * Seeded by RolesAndPermissionsSeeder. After seeding, system_admin can change
 * grants in the staff panel; re-running the seeder only ADDS missing
 * permissions and never strips grants an administrator has made.
 *
 * Workflow status changes are governed by workflow_transitions (role-based),
 * not by these permissions.
 */
final class Permissions
{
    // Applications
    public const APPLICATIONS_VIEW_ALL = 'applications.view_all';         // every application

    public const APPLICATIONS_VIEW_ASSIGNED = 'applications.view_assigned'; // only those assigned to me

    public const APPLICATIONS_REGISTER = 'applications.register';         // walk-in registration

    public const APPLICATIONS_EDIT_DETAILS = 'applications.edit_details';  // correct registered details (logged)

    public const APPLICATIONS_ASSIGN = 'applications.assign';             // assign / reassign officer

    public const APPLICATIONS_LEGACY = 'applications.legacy_capture';

    // Documents
    public const DOCUMENTS_VERIFY = 'documents.verify';

    public const DOCUMENTS_UPLOAD = 'documents.upload';

    // Review, inspection, decision
    public const REVIEWS_PERFORM = 'reviews.perform';

    public const INSPECTIONS_PERFORM = 'inspections.perform';

    public const DECISIONS_RECORD = 'decisions.record';

    public const COMMITTEE_VIEW = 'committee.view';

    public const COMMITTEE_MANAGE = 'committee.manage';

    // Money and permits
    public const PAYMENTS_RECORD = 'payments.record';

    public const PERMITS_ISSUE = 'permits.issue';

    // Oversight
    public const DASHBOARD_TOWN_ENGINEER = 'dashboard.town_engineer';

    public const REPORTS_VIEW = 'reports.view';

    public const AUDIT_VIEW = 'audit.view';

    // Administration
    public const USERS_MANAGE = 'users.manage';

    public const ROLES_MANAGE = 'roles.manage';

    public const LOOKUPS_MANAGE = 'lookups.manage';

    public const WORKFLOW_MANAGE = 'workflow.manage';

    public const SETTINGS_MANAGE = 'settings.manage';

    /** @return list<string> */
    public static function all(): array
    {
        return array_values((new \ReflectionClass(self::class))->getConstants());
    }

    /**
     * Default grants per role. [CONFIRM] with the Town Engineer.
     *
     * @return array<string, list<string>>
     */
    public static function defaultGrants(): array
    {
        $oversight = [
            self::APPLICATIONS_VIEW_ALL, self::APPLICATIONS_ASSIGN, self::DOCUMENTS_VERIFY,
            self::COMMITTEE_VIEW, self::COMMITTEE_MANAGE, self::PERMITS_ISSUE,
            self::DASHBOARD_TOWN_ENGINEER, self::REPORTS_VIEW, self::AUDIT_VIEW,
            self::APPLICATIONS_LEGACY,
        ];

        return [
            Role::SystemAdmin->value => self::all(),
            Role::TownEngineer->value => $oversight,
            Role::RecordsOfficer->value => [
                self::APPLICATIONS_VIEW_ALL, self::APPLICATIONS_REGISTER, self::APPLICATIONS_EDIT_DETAILS,
                self::APPLICATIONS_LEGACY, self::DOCUMENTS_VERIFY, self::DOCUMENTS_UPLOAD,
                self::PAYMENTS_RECORD, self::PERMITS_ISSUE,
            ],
            Role::BuildingControlOfficer->value => [
                self::APPLICATIONS_VIEW_ASSIGNED, self::REVIEWS_PERFORM, self::INSPECTIONS_PERFORM,
            ],
            Role::PhysicalPlanner->value => [
                self::APPLICATIONS_VIEW_ASSIGNED, self::REVIEWS_PERFORM,
            ],
            Role::BuildingCommittee->value => [
                self::COMMITTEE_VIEW,
            ],
            Role::CommitteeSecretary->value => [
                self::COMMITTEE_VIEW, self::COMMITTEE_MANAGE, self::DECISIONS_RECORD,
            ],
            // Applicants act only on their own records, enforced by policies, not permissions.
            Role::Applicant->value => [],
        ];
    }
}
