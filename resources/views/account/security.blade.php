<x-layouts.app title="My account">
    @php($user = auth()->user())
    @php($status = session('status'))

    @if ($status === 'two-factor-required')
        <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 p-4 text-amber-900" role="alert">
            Your role requires two-factor authentication. Set it up below before continuing.
        </div>
    @elseif ($status)
        <div class="mb-4 rounded-lg bg-brand-50 p-3 text-sm text-brand-800" role="status">
            {{ match ($status) {
                'profile-information-updated' => 'Your details were saved.',
                'password-updated' => 'Your password was changed.',
                'two-factor-authentication-enabled' => 'Scan the QR code, then enter a code to finish setup.',
                'two-factor-authentication-confirmed' => 'Two-factor authentication is now on.',
                'two-factor-authentication-disabled' => 'Two-factor authentication was turned off.',
                'recovery-codes-generated' => 'New recovery codes were generated.',
                default => $status,
            } }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="card">
            <h2 class="mb-4 text-lg font-semibold">Your details</h2>
            <form method="POST" action="{{ route('user-profile-information.update') }}" novalidate>
                @csrf @method('PUT')
                <x-field name="name" label="Full name" bag="updateProfileInformation" :value="$user->name" required />
                <x-field name="email" label="Email address" type="email" bag="updateProfileInformation" :value="$user->email" required />
                <x-field name="phone" label="Phone number" type="tel" bag="updateProfileInformation" :value="$user->phone" placeholder="+256772123456" />
                <button type="submit" class="btn-primary">Save details</button>
            </form>
        </section>

        <section class="card">
            <h2 class="mb-4 text-lg font-semibold">Change password</h2>
            <form method="POST" action="{{ route('user-password.update') }}" novalidate>
                @csrf @method('PUT')
                <x-field name="current_password" label="Current password" type="password" bag="updatePassword" autocomplete="current-password" required />
                <x-field name="password" label="New password" type="password" bag="updatePassword" autocomplete="new-password" required />
                <x-field name="password_confirmation" label="Confirm new password" type="password" bag="updatePassword" autocomplete="new-password" required />
                <button type="submit" class="btn-primary">Change password</button>
            </form>
        </section>

        <section class="card lg:col-span-2">
            <h2 class="mb-1 text-lg font-semibold">Two-factor authentication</h2>
            <p class="mb-4 text-sm text-gray-600">
                Adds a 6-digit code from an authenticator app (Google Authenticator, Microsoft Authenticator, Authy) to sign-in.
                @if ($user->requiresTwoFactor()) <strong>Required for your role.</strong> @endif
            </p>

            @if (! $user->two_factor_secret)
                <form method="POST" action="{{ route('two-factor.enable') }}">
                    @csrf
                    <button type="submit" class="btn-primary">Set up two-factor authentication</button>
                </form>
            @elseif (! $user->two_factor_confirmed_at)
                <ol class="mb-4 list-decimal space-y-1 pl-5 text-sm">
                    <li>Open your authenticator app and scan this QR code.</li>
                    <li>Enter the 6-digit code it shows.</li>
                </ol>
                <div class="mb-3 inline-block rounded-lg bg-white p-3 ring-1 ring-gray-200">{!! $user->twoFactorQrCodeSvg() !!}</div>
                <p class="mb-4 text-sm text-gray-600">Cannot scan? Setup key: <code class="break-all font-mono">{{ decrypt($user->two_factor_secret) }}</code></p>
                <form method="POST" action="{{ route('two-factor.confirm') }}" class="max-w-xs" novalidate>
                    @csrf
                    <x-field name="code" label="Authentication code" bag="confirmTwoFactorAuthentication" inputmode="numeric" autocomplete="one-time-code" required />
                    <button type="submit" class="btn-primary">Confirm and turn on</button>
                </form>
            @else
                <p class="mb-3 font-medium text-brand-700">Two-factor authentication is on.</p>
                <h3 class="mb-2 font-semibold">Recovery codes</h3>
                <p class="mb-2 text-sm text-gray-600">Store these somewhere safe. Each can be used once if you lose your phone.</p>
                <ul class="mb-4 grid max-w-md grid-cols-2 gap-1 rounded-lg bg-gray-100 p-3 font-mono text-sm">
                    @foreach ($user->recoveryCodes() as $code)
                        <li>{{ $code }}</li>
                    @endforeach
                </ul>
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}">
                        @csrf
                        <button type="submit" class="btn-secondary">New recovery codes</button>
                    </form>
                    @unless ($user->requiresTwoFactor())
                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger">Turn off</button>
                        </form>
                    @endunless
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
