<?php

namespace App\Models;

use App\Enums\ApplicantType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Applicant extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id', 'applicant_type', 'name', 'phone', 'email', 'nin',
        'company_registration_number', 'address', 'registered_by',
    ];

    protected $hidden = ['nin', 'nin_hash'];

    protected function casts(): array
    {
        return [
            'applicant_type' => ApplicantType::class,
            'nin' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        // Keep the lookup hash in step with the encrypted NIN.
        static::saving(function (Applicant $applicant) {
            if ($applicant->isDirty('nin')) {
                $applicant->nin_hash = static::hashNin($applicant->nin);
            }
        });
    }

    /** Keyed hash so an NIN can be matched without decrypting every row. */
    public static function hashNin(?string $nin): ?string
    {
        if ($nin === null || $nin === '') {
            return null;
        }

        return hash_hmac('sha256', strtoupper(trim($nin)), (string) config('app.key'));
    }

    public function getActivitylogOptions(): LogOptions
    {
        // NIN is encrypted at rest and is never copied into the audit log in clear text.
        return LogOptions::defaults()
            ->useLogName('applicants')
            ->logOnly(['applicant_type', 'name', 'phone', 'email', 'company_registration_number', 'address'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Application, $this> */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function identifier(): string
    {
        return $this->applicant_type === ApplicantType::Company
            ? 'Reg. no. '.$this->company_registration_number
            : 'NIN ending '.substr((string) $this->nin, -4);
    }
}
