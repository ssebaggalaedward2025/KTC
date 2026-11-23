<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Idempotent. On first run it applies the default grants. On later runs it only
 * creates missing roles and permissions and gives NEW permissions their default
 * roles, so grants an administrator changed in the panel are kept.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $newPermissions = [];
        foreach (Permissions::all() as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            if ($permission->wasRecentlyCreated) {
                $newPermissions[] = $name;
            }
        }

        foreach (Permissions::defaultGrants() as $roleName => $grants) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            $toGrant = $role->wasRecentlyCreated ? $grants : array_intersect($grants, $newPermissions);
            if ($toGrant !== []) {
                $role->givePermissionTo($toGrant);
            }
        }

        // system_admin always holds every permission so the system cannot be locked out.
        Role::findByName(RoleEnum::SystemAdmin->value, 'web')->syncPermissions(Permissions::all());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
