<?php

use App\Models\AuditLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

/*
| Verify the audit log hash chain. Exit code 1 if any row was altered or removed.
*/
Artisan::command('bptms:audit-verify', function () {
    $broken = AuditLog::verifyChain();

    if ($broken === null) {
        $this->info('Audit chain intact ('.AuditLog::count().' rows).');

        return 0;
    }

    $this->error("Audit chain broken at row id {$broken}. Investigate per docs/SECURITY.md (breach response).");

    return 1;
})->purpose('Check the audit log for tampering');

/*
| Print MySQL GRANT statements for the application user from the live schema.
| Append-only tables get SELECT + INSERT only. LOCK TABLES is granted because
| MySQL 8.0.22+ requires UPDATE, DELETE or LOCK TABLES for SELECT ... FOR UPDATE,
| which the audit hash chain uses; LOCK TABLES does not allow changing rows.
*/
Artisan::command('bptms:db-grants {--user=bptms_app} {--host=localhost}', function () {
    $db = DB::connection()->getDatabaseName();
    $who = sprintf("'%s'@'%s'", $this->option('user'), $this->option('host'));
    $appendOnly = config('bptms.append_only_tables');

    $tables = collect(Schema::getTableListing(schemaQualified: false))
        ->reject(fn (string $t) => in_array($t, $appendOnly, true));

    $lines = [
        "REVOKE ALL PRIVILEGES, GRANT OPTION FROM {$who};",
        "GRANT SELECT, INSERT, LOCK TABLES, CREATE TEMPORARY TABLES ON `{$db}`.* TO {$who};",
        ...$tables->map(fn (string $t) => "GRANT UPDATE, DELETE ON `{$db}`.`{$t}` TO {$who};"),
        'FLUSH PRIVILEGES;',
        '-- Append-only (SELECT, INSERT only): '.implode(', ', $appendOnly),
    ];

    $this->line(implode(PHP_EOL, $lines));
})->purpose('Print MySQL grants that make audit/history tables append-only');

/*
| Scheduled jobs (run by `php artisan schedule:work` / cron `schedule:run`).
| Feature-specific jobs (overdue flags, auto-close, permit reminders) are added with those features.
*/
Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('01:30');
Schedule::command('bptms:audit-verify')->dailyAt('02:30');
