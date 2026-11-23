<?php

namespace App\Filament\Staff\Resources\Roles\Pages;

use App\Filament\Staff\Resources\Roles\RoleResource;
use App\Support\Audit;
use Filament\Resources\Pages\EditRecord;
use LogicException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    /** @var list<string> */
    private array $before = [];

    protected function beforeSave(): void
    {
        $this->before = $this->permissionNames();
    }

    protected function afterSave(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $after = $this->permissionNames();
        Audit::record('role_permissions_changed', $this->role(), [
            'granted' => array_values(array_diff($after, $this->before)),
            'revoked' => array_values(array_diff($this->before, $after)),
        ]);
    }

    private function role(): Role
    {
        $record = $this->getRecord();

        if (! $record instanceof Role) {
            throw new LogicException('EditRole expects a Role record.');
        }

        return $record;
    }

    /** @return list<string> */
    private function permissionNames(): array
    {
        return $this->role()->permissions()->pluck('name')->sort()->values()->all();
    }
}
