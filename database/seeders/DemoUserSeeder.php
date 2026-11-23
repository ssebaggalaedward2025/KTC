<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * One clearly marked demo account per role, for training and pilot testing.
 * Never seeded in production; demo accounts are also refused at sign-in there
 * (User::canSignIn()).
 *
 * All demo accounts use the password: Demo@KTC-2026!
 */
class DemoUserSeeder extends Seeder
{
    public const PASSWORD = 'Demo@KTC-2026!';

    public function run(): void
    {
        if (app()->isProduction() || ! config('bptms.security.seed_demo_users')) {
            $this->command?->warn('Demo users skipped (production or BPTMS_SEED_DEMO_USERS=false).');

            return;
        }

        foreach (Role::cases() as $role) {
            $user = User::firstOrNew(['email' => str_replace('_', '.', $role->value).'@demo.ktc.test']);
            $user->forceFill([
                'name' => '[DEMO] '.$role->label(),
                'phone' => '+256700000'.str_pad((string) (array_search($role, Role::cases(), true) + 100), 3, '0', STR_PAD_LEFT),
                'password' => self::PASSWORD,
                'email_verified_at' => now(),
                'is_active' => true,
                'is_demo' => true,
            ])->save();

            $user->syncRoles([$role->value]);
        }

        $this->command?->info('Demo users: <role>@demo.ktc.test (dots for underscores), password '.self::PASSWORD);
    }
}
