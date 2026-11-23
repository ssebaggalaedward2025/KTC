<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Permissions;
use Spatie\Permission\Models\Role;

/**
 * Role names are referenced by workflow transitions, so they are created by the
 * seeder and never renamed or deleted in the UI. Only their permissions are edited.
 */
class RolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(Permissions::ROLES_MANAGE);
    }

    public function view(User $actor, Role $role): bool
    {
        return $actor->can(Permissions::ROLES_MANAGE);
    }

    public function create(User $actor): bool
    {
        return false;
    }

    public function update(User $actor, Role $role): bool
    {
        // system_admin's grants are fixed (always everything) to prevent lock-out.
        return $actor->can(Permissions::ROLES_MANAGE) && $role->name !== \App\Enums\Role::SystemAdmin->value;
    }

    public function delete(User $actor, Role $role): bool
    {
        return false;
    }

    public function deleteAny(User $actor): bool
    {
        return false;
    }
}
