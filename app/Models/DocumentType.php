<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description', 'is_required', 'allowed_extensions', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'is_active' => 'boolean'];
    }

    /** @return list<string> */
    public function extensions(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', strtolower($this->allowed_extensions)))));
    }

    /** @param Builder<DocumentType> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}
