<x-layouts.guest title="Two-factor authentication">
    <h1 class="mb-1 text-xl font-bold">Two-factor authentication</h1>
    <p class="mb-4 text-sm text-gray-600">Enter the 6-digit code from your authenticator app.</p>

    <form method="POST" action="{{ url('/two-factor-challenge') }}" novalidate>
        @csrf
        <x-field name="code" label="Authentication code" inputmode="numeric" autocomplete="one-time-code" autofocus />

        <details class="mb-4 text-sm" @if ($errors->has('recovery_code')) open @endif>
            <summary class="link cursor-pointer">Lost your phone? Use a recovery code instead</summary>
            <div class="mt-3">
                <x-field name="recovery_code" label="Recovery code" autocomplete="off" />
            </div>
        </details>

        <button type="submit" class="btn-primary w-full">Continue</button>
    </form>
</x-layouts.guest>
