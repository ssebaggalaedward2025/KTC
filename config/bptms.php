<?php

/*
|--------------------------------------------------------------------------
| BPTMS configuration
|--------------------------------------------------------------------------
| Values here are boot-time defaults. Anything a KTC administrator must be
| able to change at runtime lives in the `settings` table (App\Support\Settings)
| and falls back to these defaults. Items marked [CONFIRM] await validation
| by the Town Engineer.
*/

return [

    'council_name' => 'Kyengera Town Council',
    'reference_prefix' => 'KTC/BP',

    'security' => [
        // Roles that must enrol TOTP two-factor authentication before using the staff panel.
        'two_factor_roles' => ['system_admin', 'town_engineer'],
        // Idle minutes before a logged-in session is ended.
        'idle_timeout_minutes' => (int) env('BPTMS_IDLE_TIMEOUT', 30),
        // Failed login attempts allowed per (email + IP) before a lockout.
        'login_max_attempts' => 5,
        'login_decay_seconds' => 900,
        'password_min_length' => 12,
        // Demo users are never seeded when APP_ENV=production.
        'seed_demo_users' => env('BPTMS_SEED_DEMO_USERS', env('APP_ENV') !== 'production'),
    ],

    'deadlines' => [
        // [CONFIRM] whether the 30-day legal limit counts working or calendar days.
        'day_type' => env('BPTMS_DEADLINE_DAY_TYPE', 'working'),
        'legal_limit_days' => 30,
        'amber_threshold_days' => 2,
        // Working days after a plan goes overdue before the Town Engineer is alerted.
        'escalation_grace_days' => 2,
    ],

    'inactivity' => [
        // [CONFIRM] days without applicant response before auto close (closed_no_response).
        'close_after_days' => 60,
        'warn_at_days' => [30, 50],
    ],

    'permits' => [
        // [CONFIRM] permit validity and signatory.
        'validity_years' => 3,
        'expiry_reminder_days' => 60,
    ],

    'committee' => [
        // [CONFIRM] sitting frequency; sittings are entered manually by admin.
        'agenda_cutoff_working_days' => 5,
        // Feature flag: Town Engineer may approve small plans under delegated authority.
        'delegated_approval_enabled' => false,
        'delegated_approval_max_floor_area_m2' => 100,
    ],

    'uploads' => [
        'disk' => 'private',
        'max_kb' => (int) env('BPTMS_UPLOAD_MAX_KB', 51200),
        'allowed_extensions' => ['pdf', 'dwg', 'dxf', 'jpg', 'jpeg', 'png'],
        'allowed_mimes' => [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            // CAD files are often reported generically; extension + magic bytes are checked too.
            'dwg' => ['image/vnd.dwg', 'application/acad', 'application/x-acad', 'application/dwg', 'application/octet-stream'],
            'dxf' => ['image/vnd.dxf', 'application/dxf', 'text/plain', 'application/octet-stream'],
        ],
    ],

    'scanner' => [
        // clamav | null  (null = mark clean immediately; local development only)
        'driver' => env('BPTMS_SCANNER', 'null'),
        'clamav_host' => env('CLAMAV_HOST', 'clamav'),
        'clamav_port' => (int) env('CLAMAV_PORT', 3310),
        'timeout' => 30,
    ],

    'sms' => [
        // log | africastalking (Phase 2)
        'driver' => env('BPTMS_SMS_DRIVER', 'log'),
    ],

    'public_status' => [
        'max_attempts_per_minute' => 5,
        'captcha' => env('BPTMS_CAPTCHA', 'turnstile'), // turnstile | none (tests/dev)
        'turnstile_site_key' => env('TURNSTILE_SITE_KEY'),
        'turnstile_secret' => env('TURNSTILE_SECRET'),
    ],

    // Tables the application DB user may only SELECT/INSERT (see `php artisan bptms:db-grants`).
    'append_only_tables' => [
        'audit_logs',
        'application_status_history',
    ],

    // Data Protection and Privacy Act 2019. [CONFIRM] retention periods.
    'retention' => [
        'application_records_years' => 10,
        'audit_logs_years' => 10,
        'abandoned_drafts_days' => 90,
    ],
];
