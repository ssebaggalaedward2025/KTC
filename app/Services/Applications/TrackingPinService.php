<?php

namespace App\Services\Applications;

use App\Models\Application;
use Illuminate\Support\Facades\Hash;

/**
 * Random PIN given to the applicant at registration for the public status check.
 * Only its hash is stored, so the PIN is shown once (confirmation page, slip and
 * email) and can be re-issued by the Records Officer if lost.
 */
class TrackingPinService
{
    // No 0/O, 1/I/L: easy to read out over the phone and type on a feature phone.
    private const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    private const LENGTH = 8;

    public function generate(): string
    {
        $pin = '';
        $max = strlen(self::ALPHABET) - 1;
        for ($i = 0; $i < self::LENGTH; $i++) {
            $pin .= self::ALPHABET[random_int(0, $max)];
        }

        return $pin;
    }

    public function hash(string $pin): string
    {
        return Hash::make($this->normalise($pin));
    }

    public function verify(Application $application, string $pin): bool
    {
        return Hash::check($this->normalise($pin), $application->public_tracking_pin_hash);
    }

    private function normalise(string $pin): string
    {
        return strtoupper((string) preg_replace('/[\s-]/', '', $pin));
    }
}
