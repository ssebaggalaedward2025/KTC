<?php

namespace App\Actions\Fortify;

use App\Enums\Role;
use App\Models\User;
use App\Rules\UgandaPhone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/** Public self-registration. Always creates an applicant account. */
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /** @param  array<string, string>  $input */
    public function create(array $input): User
    {
        $input['phone'] = UgandaPhone::normalise($input['phone'] ?? null);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['required', new UgandaPhone],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'password' => $input['password'],
            ]);
            $user->assignRole(Role::Applicant->value);

            return $user;
        });
    }
}
