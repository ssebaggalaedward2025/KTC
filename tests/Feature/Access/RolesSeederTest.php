<?php

use App\Enums\Role;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role as RoleModel;

it('creates every Phase 1 role', function () {
    expect(RoleModel::pluck('name')->sort()->values()->all())
        ->toBe(collect(Role::cases())->map->value->sort()->values()->all());
});

it('gives system_admin every permission', function () {
    expect(RoleModel::findByName(Role::SystemAdmin->value)->permissions->pluck('name')->sort()->values()->all())
        ->toBe(collect(Permissions::all())->sort()->values()->all());
});

it('gives applicants no staff permissions', function () {
    expect(RoleModel::findByName(Role::Applicant->value)->permissions)->toBeEmpty();
});

it('keeps administrator changes when re-seeded', function () {
    $planner = RoleModel::findByName(Role::PhysicalPlanner->value);
    $planner->revokePermissionTo(Permissions::REVIEWS_PERFORM);

    $this->seed(RolesAndPermissionsSeeder::class);

    expect($planner->fresh()->hasPermissionTo(Permissions::REVIEWS_PERFORM))->toBeFalse();
});

it('seeds one marked demo user per role outside production', function () {
    $this->seed(DemoUserSeeder::class);

    foreach (Role::cases() as $role) {
        $user = User::role($role->value)->where('is_demo', true)->sole();
        expect($user->name)->toStartWith('[DEMO]');
    }
});

it('never seeds demo users in production', function () {
    app()->detectEnvironment(fn () => 'production');

    // Called directly: `db:seed` would stop at Laravel's production confirmation prompt.
    app(DemoUserSeeder::class)->run();

    expect(User::where('is_demo', true)->count())->toBe(0);
});
