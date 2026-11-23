<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\RolePolicy;
use App\Support\Audit;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Catch lazy loading and silently discarded attributes during development.
        Model::shouldBeStrict(! $this->app->isProduction());

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Password::defaults(function () {
            $rule = Password::min(config('bptms.security.password_min_length'))
                ->mixedCase()
                ->numbers()
                ->symbols();

            // Checks the Have I Been Pwned range API (k-anonymity) in production only.
            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });

        // spatie's Role model lives outside App\Models, so its policy is registered explicitly.
        Gate::policy(Role::class, RolePolicy::class);

        $this->registerAuthAuditing();
    }

    private function registerAuthAuditing(): void
    {
        Event::listen(function (Login $event) {
            if ($event->user instanceof User) {
                $event->user->forceFill([
                    'last_login_at' => now(),
                    'last_login_ip' => request()->ip(),
                ])->saveQuietly();
                Audit::record('login', $event->user, causer: $event->user);
            }
        });

        Event::listen(function (Logout $event) {
            if ($event->user instanceof User) {
                Audit::record('logout', $event->user, causer: $event->user);
            }
        });

        Event::listen(function (Failed $event) {
            // Record the attempted email (not the password) to spot brute-force attempts.
            Audit::record('login_failed', $event->user instanceof User ? $event->user : null, [
                'email' => mb_substr((string) ($event->credentials['email'] ?? ''), 0, 255),
            ]);
        });

        Event::listen(fn (TwoFactorAuthenticationConfirmed $e) => Audit::record('two_factor_enabled', $e->user, causer: $e->user));
        Event::listen(fn (TwoFactorAuthenticationDisabled $e) => Audit::record('two_factor_disabled', $e->user, causer: $e->user));
    }
}
