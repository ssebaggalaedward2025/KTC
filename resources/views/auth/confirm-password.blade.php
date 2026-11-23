<x-layouts.guest title="Confirm password">
    <h1 class="mb-1 text-xl font-bold">Confirm your password</h1>
    <p class="mb-4 text-sm text-gray-600">This is a secure area. Please confirm your password to continue.</p>

    <form method="POST" action="{{ route('password.confirm') }}" novalidate>
        @csrf
        <x-field name="password" label="Password" type="password" autocomplete="current-password" required autofocus />
        <button type="submit" class="btn-primary w-full">Confirm</button>
    </form>
</x-layouts.guest>
