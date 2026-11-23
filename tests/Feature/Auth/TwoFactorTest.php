<?php

use App\Enums\Role;

it('forces the Town Engineer and system admin to enrol before using the staff panel', function (Role $role) {
    $user = userWithRole($role, twoFactor: false);

    $this->actingAs($user)->get('/staff')
        ->assertRedirect(route('account.security'));

    $this->get(route('account.security'))
        ->assertOk()
        ->assertSee('Your role requires two-factor authentication');
})->with([Role::TownEngineer, Role::SystemAdmin]);

it('lets enrolled Town Engineer and admin into the staff panel', function (Role $role) {
    $this->actingAs(userWithRole($role))->get('/staff')->assertOk();
})->with([Role::TownEngineer, Role::SystemAdmin]);

it('does not force 2FA on other roles', function (Role $role) {
    $this->actingAs(userWithRole($role))->get('/staff')->assertOk();
})->with([Role::RecordsOfficer, Role::BuildingControlOfficer, Role::PhysicalPlanner, Role::CommitteeSecretary, Role::BuildingCommittee]);

it('lets applicants use the portal without 2FA', function () {
    $this->actingAs(userWithRole(Role::Applicant))->get(route('portal.dashboard'))->assertOk();
});

it('challenges an enrolled user for a code at login', function () {
    userWithRole(Role::TownEngineer, ['email' => 'te@example.test']);

    $this->post('/login', ['email' => 'te@example.test', 'password' => 'password'])
        ->assertRedirect('/two-factor-challenge');

    $this->assertGuest();
});

it('rejects a wrong 2FA code', function () {
    userWithRole(Role::TownEngineer, ['email' => 'te@example.test']);
    $this->post('/login', ['email' => 'te@example.test', 'password' => 'password']);

    $this->post('/two-factor-challenge', ['code' => '000000'])->assertRedirect('/two-factor-challenge');
    $this->assertGuest();
});

it('accepts a recovery code at the 2FA challenge', function () {
    $user = userWithRole(Role::TownEngineer, ['email' => 'te@example.test']);
    $this->post('/login', ['email' => 'te@example.test', 'password' => 'password']);

    $this->post('/two-factor-challenge', ['recovery_code' => 'code-one'])->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});
