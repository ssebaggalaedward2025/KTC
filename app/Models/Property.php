<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/** The council (Kyengera Town Council) is constant and is not stored per record. */
class Property extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['plot_number', 'block_number', 'village_id', 'land_title_details'];

    protected $hidden = ['land_title_details'];

    protected function casts(): array
    {
        return ['land_title_details' => 'encrypted'];
    }

    protected static function booted(): void
    {
        static::saving(function (Property $property) {
            $property->plot_key = static::normaliseKey($property->plot_number);
            $property->block_key = static::normaliseKey($property->block_number);
        });
    }

    /** "Plot 12", "plot no. 12" and "12" all become "12"; "Blk 3A" becomes "3A". */
    public static function normaliseKey(?string $value): string
    {
        $value = strtoupper((string) $value);
        $value = preg_replace('/\b(PLOT|BLOCK|BLK|NO)\b\.?/', '', $value);

        return (string) preg_replace('/[^A-Z0-9]/', '', (string) $value);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('properties')
            ->logOnly(['plot_number', 'block_number', 'village_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /** @return BelongsTo<Village, $this> */
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    /** @return HasMany<Application, $this> */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function label(): string
    {
        return "Plot {$this->plot_number}, Block {$this->block_number}";
    }
}
