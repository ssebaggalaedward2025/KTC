<?php

use App\Enums\Role;
use App\Models\User;

$valid = fn (array $overrides = []) => array_merge([
    'name' => 'Nakato Sarah',
    'email' => 'nakato@example.test',
    'phone' => '0772123456',
    'password' => 'Str0ng-Passw0rd!',
    'password_confirmation' => 'Str0ng-Passw0rd!',
], $overrides);

it('registers a new applicant with the applicant role only', function () use ($valid) {
    $this->post('/register', $valid())->assertRedirect('/dashboard');

    $user = User::where('email', 'nakato@example.test')->sole();
    expect($user->getRoleNames()->all())->toBe([Role::Applicant->value])
        ->and($user->phone)->toBe('+256772123456')
        ->and($user->isStaff())->toBeFalse();
});

it('cannot be used to obtain a staff role', function () use ($valid) {
    $this->post('/register', $valid(['role' => 'system_admin', 'roles' => ['town_engineer']]));

    expect(User::where('email', 'nakato@example.test')->sole()->isStaff())->toBeFalse();
});

it('rejects phone numbers that are not Ugandan', function (string $phone) use ($valid) {
    $this->post('/register', $valid(['phone' => $phone]))->assertSessionHasErrors('phone');
})->with(['+254712345678', '12345', '+25677212345', 'abc']);

it('enforces the password strength rules', function (string $password) use ($valid) {
    $this->post('/register', $valid(['password' => $password, 'password_confirmation' => $password]))
        ->assertSessionHasErrors('password');
})->with([
    'too short' => 'Ab1!short',
    'no symbol' => 'NoSymbolsHere123',
    'no number' => 'No-Numbers-Here!',
    'no uppercase' => 'no-uppercase-123!',
]);

it('rejects a duplicate email address', function () use ($valid) {
    User::factory()->create(['email' => 'nakato@example.test']);

    $this->post('/register', $valid())->assertSessionHasErrors('email');
});
