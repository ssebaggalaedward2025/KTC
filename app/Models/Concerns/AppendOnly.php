<?php

namespace App\Models\Concerns;

use App\Exceptions\AppendOnlyViolation;

/**
 * Rows of models using this trait may be inserted but never updated or deleted.
 *
 * This is the application-level guard. The database-level guard is the
 * production grant set (no UPDATE/DELETE) in docker/mysql/grants.sql.
 * Mass query-builder updates/deletes bypass Eloquent events, which is why
 * the database grants are required as well.
 */
trait AppendOnly
{
    public static function bootAppendOnly(): void
    {
        static::updating(function ($model) {
            throw AppendOnlyViolation::forModel($model, 'update');
        });

        static::deleting(function ($model) {
            throw AppendOnlyViolation::forModel($model, 'delete');
        });
    }
}
