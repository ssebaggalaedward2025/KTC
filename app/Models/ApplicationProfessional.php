<?php

namespace App\Models;

use App\Enums\ProfessionalRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationProfessional extends Model
{
    use HasFactory;

    protected $fillable = ['application_id', 'role', 'role_description', 'name', 'registration_number', 'phone'];

    protected function casts(): array
    {
        return ['role' => ProfessionalRole::class];
    }

    /** @return BelongsTo<Application, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
