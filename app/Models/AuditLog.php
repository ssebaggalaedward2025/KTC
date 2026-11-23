<?php

namespace App\Models;

use App\Models\Concerns\AppendOnly;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * Append-only, hash-chained audit trail. Every row stores the hash of the
 * previous row, so editing or removing a row breaks the chain and is detected
 * by AuditLog::verifyChain() (also exposed as `php artisan bptms:audit-verify`).
 */
class AuditLog extends Activity
{
    use AppendOnly;

    protected $table = 'audit_logs';

    protected static function booted(): void
    {
        static::creating(function (AuditLog $log) {
            // Console commands and queue workers have no real client request.
            $isHttp = ! app()->runningInConsole() || app()->runningUnitTests();
            $request = $isHttp ? request() : null;
            $log->ip_address ??= $request?->ip();
            $log->user_agent ??= $request ? mb_substr((string) $request->userAgent(), 0, 512) : 'console';
            $log->created_at ??= now();
            $log->updated_at ??= $log->created_at;

            // Serialise hash computation so concurrent writers keep one chain.
            DB::transaction(function () use ($log) {
                $previous = static::query()->latest('id')->lockForUpdate()->value('row_hash');
                $log->prev_hash = $previous;
                $log->row_hash = $log->computeHash();
            });
        });
    }

    public function computeHash(): string
    {
        $changes = $this->attribute_changes;
        $properties = $this->properties;

        return hash('sha256', json_encode([
            $this->prev_hash,
            $this->log_name,
            $this->description,
            $this->event,
            $this->subject_type,
            $this->subject_id,
            $this->causer_type,
            $this->causer_id,
            $changes?->toArray(),
            $properties?->toArray(),
            $this->ip_address,
            $this->created_at?->format('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
    }

    /**
     * Walk the chain in id order. Returns the id of the first broken row, or null if intact.
     */
    public static function verifyChain(): ?int
    {
        $previous = null;
        $broken = null;

        static::query()->orderBy('id')->chunk(500, function ($rows) use (&$previous, &$broken) {
            foreach ($rows as $row) {
                if ($row->prev_hash !== $previous || $row->row_hash !== $row->computeHash()) {
                    $broken = $row->id;

                    return false;
                }
                $previous = $row->row_hash;
            }

            return true;
        });

        return $broken;
    }
}
