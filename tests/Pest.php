<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class))
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/**
 * Create a user holding the given role. Users whose role requires 2FA are
 * enrolled by default so tests reach the page under test.
 */
function userWithRole(Role $role, array $attributes = [], ?bool $twoFactor = null): User
{
    $factory = User::factory()->withRole($role);

    $twoFactor ??= in_array($role->value, config('bptms.security.two_factor_roles'), true);
    if ($twoFactor) {
        $factory = $factory->withTwoFactor();
    }

    return $factory->create($attributes);
}
