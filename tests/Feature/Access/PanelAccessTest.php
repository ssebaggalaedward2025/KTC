<?php

use App\Enums\Role;
use App\Models\User;
use App\Support\Permissions;
use Spatie\Permission\Models\Role as RoleModel;

it('redirects guests from the staff panel to the single login page', function () {
    $this->get('/staff')->assertRedirect(route('login'));
});

it('redirects guests from the portal to login', function () {
    $this->get(route('portal.dashboard'))->assertRedirect(route('login'));
});

it('forbids applicants from the staff panel', function () {
    $this->actingAs(userWithRole(Role::Applicant))->get('/staff')->assertForbidden();
});

it('forbids users with no role from the staff panel', function () {
    $this->actingAs(User::factory()->create())->get('/staff')->assertForbidden();
});

it('keeps staff out of the applicant portal', function () {
    $this->actingAs(userWithRole(Role::RecordsOfficer))->get(route('portal.dashboard'))->assertForbidden();
});

it('only lets user managers open user administration', function (Role $role, int $status) {
    $this->actingAs(userWithRole($role))->get('/staff/users')->assertStatus($status);
})->with([
    'system admin' => [Role::SystemAdmin, 200],
    'town engineer' => [Role::TownEngineer, 403],
    'records officer' => [Role::RecordsOfficer, 403],
    'building control officer' => [Role::BuildingControlOfficer, 403],
]);

it('only lets role managers open roles and permissions', function (Role $role, int $status) {
    $this->actingAs(userWithRole($role))->get('/staff/roles')->assertStatus($status);
})->with([
    'system admin' => [Role::SystemAdmin, 200],
    'town engineer' => [Role::TownEngineer, 403],
]);

it('renders the user and role edit pages for the system admin', function () {
    $admin = userWithRole(Role::SystemAdmin);
    $planner = RoleModel::findByName(Role::PhysicalPlanner->value);

    $this->actingAs($admin)->get("/staff/users/{$admin->id}/edit")->assertOk();
    $this->actingAs($admin)->get('/staff/users/create')->assertOk();
    $this->actingAs($admin)->get("/staff/roles/{$planner->id}/edit")->assertOk()->assertSee('Physical Planner');
});

it('forbids editing the system admin role so the system cannot be locked out', function () {
    $admin = userWithRole(Role::SystemAdmin);
    $adminRole = RoleModel::findByName(Role::SystemAdmin->value);

    $this->actingAs($admin)->get("/staff/roles/{$adminRole->id}/edit")->assertForbidden();
});

it('never allows deleting users or roles', function () {
    $admin = userWithRole(Role::SystemAdmin);
    $other = User::factory()->create();

    expect($admin->can('delete', $other))->toBeFalse()
        ->and($admin->can('delete', RoleModel::findByName('applicant')))->toBeFalse()
        ->and($admin->can('update', RoleModel::findByName(Role::SystemAdmin->value)))->toBeFalse();
});

it('follows permission changes made by the administrator', function () {
    $te = userWithRole(Role::TownEngineer);
    expect($te->can(Permissions::USERS_MANAGE))->toBeFalse();

    RoleModel::findByName(Role::TownEngineer->value)->givePermissionTo(Permissions::USERS_MANAGE);

    expect($te->fresh()->can(Permissions::USERS_MANAGE))->toBeTrue();
});
