<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\Priority;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * A building plan application. current_status must only be changed through
 * WorkflowService (feature 4).
 */
class Application extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'application_number', 'bims_reference', 'applicant_id', 'property_id', 'parent_application_id',
        'application_date', 'current_stage_id', 'current_status', 'assigned_officer_id', 'priority',
        'stage_deadline', 'fee_estimate', 'public_tracking_pin_hash', 'is_legacy', 'registered_by',
        'duplicate_warning_acknowledged', 'submitted_at',
    ];

    protected $hidden = ['public_tracking_pin_hash'];

    protected $attributes = [
        'priority' => 'normal',
        'is_legacy' => false,
        'duplicate_warning_acknowledged' => false,
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'current_status' => ApplicationStatus::class,
            'priority' => Priority::class,
            'application_date' => 'date',
            'stage_deadline' => 'datetime',
            'submitted_at' => 'datetime',
            'fee_estimate' => 'decimal:2',
            'is_legacy' => 'boolean',
            'duplicate_warning_acknowledged' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('applications')
            ->logOnly(['bims_reference', 'property_id', 'applicant_id', 'assigned_officer_id', 'priority', 'stage_deadline', 'current_status', 'current_stage_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /** @return BelongsTo<Applicant, $this> */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    /** @return BelongsTo<Property, $this> */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /** @return HasOne<BuildingPlan, $this> */
    public function buildingPlan(): HasOne
    {
        return $this->hasOne(BuildingPlan::class);
    }

    /** @return HasMany<ApplicationProfessional, $this> */
    public function professionals(): HasMany
    {
        return $this->hasMany(ApplicationProfessional::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    /** @return BelongsTo<User, $this> */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /** @return BelongsTo<Application, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'parent_application_id');
    }

    /** @param Builder<Application> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('current_status', ApplicationStatus::openValues());
    }

    /** Applications the given applicant user owns. @param Builder<Application> $query */
    public function scopeOwnedBy(Builder $query, User $user): void
    {
        $query->whereHas('applicant', fn (Builder $q) => $q->where('user_id', $user->id));
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->applicant()->where('user_id', $user->id)->exists();
    }
}
