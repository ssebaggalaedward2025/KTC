<?php

namespace App\Filament\Staff\Resources\Users\Pages;

use App\Filament\Staff\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;

/** No delete action: users are deactivated so history keeps its references. */
class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
}
