<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Uganda National Identification Number (NIN): 14 letters and digits,
 * e.g. CM90012345ABCD. Upper-cased before checking.
 */
class NationalId implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^[A-Z0-9]{14}$/', strtoupper(trim($value)))) {
            $fail('The National ID (NIN) must be exactly 14 letters and numbers, e.g. CM90012345ABCD.');
        }
    }
}
