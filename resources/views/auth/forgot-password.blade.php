<x-layouts.guest title="Reset password">
    <h1 class="mb-1 text-xl font-bold">Forgot your password?</h1>
    <p class="mb-4 text-sm text-gray-600">Enter your email address and we will send you a link to choose a new password.</p>

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <x-field name="email" label="Email address" type="email" autocomplete="email" required autofocus />
        <button type="submit" class="btn-primary w-full">Email reset link</button>
    </form>

    <p class="mt-5 text-sm"><a href="{{ route('login') }}" class="link">Back to sign in</a></p>
</x-layouts.guest>
