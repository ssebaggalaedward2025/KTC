<?php

namespace App\Services\Applications;

use Illuminate\Support\Facades\DB;

/**
 * Generates KTC/BP/{year}/{5-digit sequence}, e.g. KTC/BP/2026/00125.
 *
 * The sequence restarts each year. The year's counter row is locked with
 * SELECT ... FOR UPDATE inside a transaction, so two registrations at the same
 * moment can never receive the same number (and applications.application_number
 * is also UNIQUE as a last line of defence).
 */
class ReferenceNumberGenerator
{
    public function next(?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        $number = DB::transaction(function () use ($year) {
            // Create the year's row if missing; ignore the race where another request just did.
            DB::table('application_sequences')->insertOrIgnore([
                'year' => $year, 'last_number' => 0, 'created_at' => now(), 'updated_at' => now(),
            ]);

            $current = (int) DB::table('application_sequences')
                ->where('year', $year)
                ->lockForUpdate()
                ->value('last_number');

            $next = $current + 1;

            DB::table('application_sequences')
                ->where('year', $year)
                ->update(['last_number' => $next, 'updated_at' => now()]);

            return $next;
        });

        return sprintf('%s/%d/%05d', config('bptms.reference_prefix'), $year, $number);
    }
}
