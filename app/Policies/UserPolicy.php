<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Permissions;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(Permissions::USERS_MANAGE);
    }

    public function view(User $actor, User $user): bool
    {
        return $actor->is($user) || $actor->can(Permissions::USERS_MANAGE);
    }

    public function create(User $actor): bool
    {
        return $actor->can(Permissions::USERS_MANAGE);
    }

    public function update(User $actor, User $user): bool
    {
        return $actor->can(Permissions::USERS_MANAGE);
    }

    /** Accounts are deactivated, never deleted. */
    public function delete(User $actor, User $user): bool
    {
        return false;
    }

    public function deleteAny(User $actor): bool
    {
        return false;
    }
}
