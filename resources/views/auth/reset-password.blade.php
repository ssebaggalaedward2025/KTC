<x-layouts.guest title="Choose a new password">
    <h1 class="mb-4 text-xl font-bold">Choose a new password</h1>

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-field name="email" label="Email address" type="email" autocomplete="username" :value="$request->email" required />
        <x-field name="password" label="New password" type="password" autocomplete="new-password" required autofocus />
        <x-field name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" required />
        <button type="submit" class="btn-primary w-full">Save new password</button>
    </form>
</x-layouts.guest>
