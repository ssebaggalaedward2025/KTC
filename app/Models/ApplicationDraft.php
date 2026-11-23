<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Autosaved registration wizard state. The whole payload is encrypted because
 * it can hold an NIN and land title details.
 */
class ApplicationDraft extends Model
{
    protected $fillable = ['user_id', 'mode', 'step', 'data'];

    protected function casts(): array
    {
        return [
            'step' => 'integer',
            'data' => 'encrypted:array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
