<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return HasMany<Village, $this> */
    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }

    /** @param Builder<Ward> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
