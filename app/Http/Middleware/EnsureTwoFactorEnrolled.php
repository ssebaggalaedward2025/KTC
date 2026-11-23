<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Users whose role requires 2FA (system_admin, town_engineer) must enrol
 * before they can use anything except the security settings page.
 */
class EnsureTwoFactorEnrolled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->requiresTwoFactor() && ! $user->hasEnabledTwoFactor()) {
            return redirect()->route('account.security')
                ->with('status', 'two-factor-required');
        }

        return $next($request);
    }
}
