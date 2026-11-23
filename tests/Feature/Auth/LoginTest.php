<?php

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\User;

it('shows the login page', function () {
    $this->get('/login')->assertOk()->assertSee('Sign in');
});

it('sends staff to the staff panel after login', function () {
    $officer = userWithRole(Role::RecordsOfficer, ['email' => 'records@example.test']);

    $this->post('/login', ['email' => 'records@example.test', 'password' => 'password'])
        ->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($officer);

    $this->get('/dashboard')->assertRedirect(url('/staff'));
});

it('sends applicants to the portal after login', function () {
    userWithRole(Role::Applicant, ['email' => 'applicant@example.test']);

    $this->post('/login', ['email' => 'applicant@example.test', 'password' => 'password']);

    $this->get('/dashboard')->assertRedirect(route('portal.dashboard'));
});

it('records the last login time and audits the login', function () {
    $user = userWithRole(Role::Applicant, ['email' => 'a@example.test']);

    $this->post('/login', ['email' => 'a@example.test', 'password' => 'password']);

    expect($user->fresh()->last_login_at)->not->toBeNull()
        ->and(AuditLog::where('event', 'login')->where('subject_id', $user->id)->exists())->toBeTrue();
});

it('rejects a wrong password and audits the failure without storing the password', function () {
    userWithRole(Role::Applicant, ['email' => 'a@example.test']);

    $this->post('/login', ['email' => 'a@example.test', 'password' => 'wrong-password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $log = AuditLog::where('event', 'login_failed')->sole();
    expect($log->getProperty('email'))->toBe('a@example.test')
        ->and(json_encode($log->properties))->not->toContain('wrong-password');
});

it('refuses deactivated accounts', function () {
    userWithRole(Role::RecordsOfficer, ['email' => 'off@example.test', 'is_active' => false]);

    $this->post('/login', ['email' => 'off@example.test', 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs out a user whose account is deactivated mid-session', function () {
    $user = userWithRole(Role::Applicant);
    $this->actingAs($user)->get(route('portal.dashboard'))->assertOk();

    $user->update(['is_active' => false]);

    $this->get(route('portal.dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});

it('refuses demo accounts in production', function () {
    $user = userWithRole(Role::RecordsOfficer, ['email' => 'demo@example.test']);
    $user->forceFill(['is_demo' => true])->save();

    app()->detectEnvironment(fn () => 'production');

    expect($user->fresh()->canSignIn())->toBeFalse();
});

it('locks out after five attempts for the same email and IP', function () {
    userWithRole(Role::Applicant, ['email' => 'a@example.test']);

    foreach (range(1, 5) as $i) {
        $this->post('/login', ['email' => 'a@example.test', 'password' => 'wrong'])->assertStatus(302);
    }

    // Even the correct password is refused while locked out.
    $this->post('/login', ['email' => 'a@example.test', 'password' => 'password'])->assertStatus(429);
    $this->assertGuest();
});

it('does not lock out a different account from the same IP', function () {
    userWithRole(Role::Applicant, ['email' => 'a@example.test']);
    userWithRole(Role::Applicant, ['email' => 'b@example.test']);

    foreach (range(1, 5) as $i) {
        $this->post('/login', ['email' => 'a@example.test', 'password' => 'wrong']);
    }

    $this->post('/login', ['email' => 'b@example.test', 'password' => 'password'])->assertRedirect('/dashboard');
});

it('hashes passwords with the configured driver, never plain text', function () {
    $user = User::factory()->create(['password' => 'Secret-Pass-123!']);

    expect($user->getAttributes()['password'])->not->toBe('Secret-Pass-123!')
        ->and(password_verify('Secret-Pass-123!', $user->getAttributes()['password']))->toBeTrue();
});
