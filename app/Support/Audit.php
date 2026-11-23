<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * One entry point for explicit audit events (logins, document views and
 * downloads, decisions, access to personal data). Model create/update/delete
 * events are recorded automatically by the LogsActivity trait on each model.
 */
final class Audit
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public static function record(string $event, ?Model $subject = null, array $properties = [], ?Model $causer = null, ?string $description = null): void
    {
        $logger = activity('audit')->event($event)->withProperties($properties);

        if ($subject) {
            $logger->performedOn($subject);
        }

        if ($causer) {
            $logger->causedBy($causer);
        }

        $logger->log($description ?? $event);
    }
}
