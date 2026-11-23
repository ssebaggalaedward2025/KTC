<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class BuildingPlan extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'application_id', 'building_type_id', 'land_use_id', 'number_of_floors',
        'plot_size_m2', 'floor_area_m2', 'estimated_cost_ugx', 'description',
    ];

    protected function casts(): array
    {
        return [
            'number_of_floors' => 'integer',
            'plot_size_m2' => 'decimal:2',
            'floor_area_m2' => 'decimal:2',
            'estimated_cost_ugx' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('building_plans')->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }

    /** @return BelongsTo<Application, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /** @return BelongsTo<BuildingType, $this> */
    public function buildingType(): BelongsTo
    {
        return $this->belongsTo(BuildingType::class);
    }

    /** @return BelongsTo<LandUse, $this> */
    public function landUse(): BelongsTo
    {
        return $this->belongsTo(LandUse::class);
    }
}
