<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Rules\UgandaPhone;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

/** Changes are audited by the LogsActivity trait on User. */
class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /** @param  array<string, string>  $input */
    public function update(User $user, array $input): void
    {
        $input['phone'] = UgandaPhone::normalise($input['phone'] ?? null);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', new UgandaPhone],
        ])->validateWithBag('updateProfileInformation');

        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
        ])->save();
    }
}
