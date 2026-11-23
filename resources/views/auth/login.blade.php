<x-layouts.guest title="Sign in">
    <h1 class="mb-4 text-xl font-bold">Sign in</h1>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <x-field name="email" label="Email address" type="email" autocomplete="username" required autofocus />
        <x-field name="password" label="Password" type="password" autocomplete="current-password" required />

        <label class="mb-4 flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" class="h-5 w-5 rounded border-gray-300"> Keep me signed in on this device
        </label>

        @error('throttle')
            <p class="mb-4 text-sm text-red-700">{{ $message }}</p>
        @enderror

        <button type="submit" class="btn-primary w-full">Sign in</button>
    </form>

    <div class="mt-5 flex flex-col gap-2 text-sm sm:flex-row sm:justify-between">
        <a href="{{ route('password.request') }}" class="link">Forgot your password?</a>
        <a href="{{ route('register') }}" class="link">New applicant? Create an account</a>
    </div>
</x-layouts.guest>
