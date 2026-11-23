<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Uganda phone numbers in E.164 form: +256 followed by 9 digits (e.g. +256772123456).
 * Use UgandaPhone::normalise() first to accept 0772123456 / 256772123456 / spaces.
 */
class UgandaPhone implements ValidationRule
{
    public const PATTERN = '/^\+256[1-9]\d{8}$/';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match(self::PATTERN, $value)) {
            $fail('The :attribute must be a Uganda number in the format +256XXXXXXXXX.');
        }
    }

    public static function normalise(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return $value;
        }

        $digits = preg_replace('/[^\d+]/', '', $value);

        return match (true) {
            str_starts_with($digits, '+256') => $digits,
            str_starts_with($digits, '256') => '+'.$digits,
            str_starts_with($digits, '0') && strlen($digits) === 10 => '+256'.substr($digits, 1),
            default => $digits,
        };
    }
}
