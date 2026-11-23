<?php

use App\Models\AuditLog;
use Spatie\Activitylog\Actions\CleanActivityLogAction;
use Spatie\Activitylog\Actions\LogActivityAction;

return [
    'enabled' => env('ACTIVITYLOG_ENABLED', true),

    // Audit rows are never cleaned by the application: the production DB user has
    // no DELETE grant on audit_logs. Archival after the retention period
    // (config/bptms.php retention.audit_logs_years) is a DBA task. See docs/SECURITY.md.
    'clean_after_days' => 365 * 100,

    'default_log_name' => 'audit',
    'default_auth_driver' => null,
    'include_soft_deleted_subjects' => true,

    'activity_model' => AuditLog::class,

    // Never write secrets into the audit trail.
    'default_except_attributes' => [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ],

    // Must stay off: the hash chain needs each row inserted in order.
    'buffer' => ['enabled' => false],

    'actions' => [
        'log_activity' => LogActivityAction::class,
        'clean_log' => CleanActivityLogAction::class,
    ],
];
