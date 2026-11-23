<x-layouts.guest title="Create an applicant account">
    <h1 class="mb-1 text-xl font-bold">Create an applicant account</h1>
    <p class="mb-4 text-sm text-gray-600">For people applying for building plan approval. Council staff accounts are created by the system administrator.</p>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <x-field name="name" label="Full name" autocomplete="name" required autofocus />
        <x-field name="email" label="Email address" type="email" autocomplete="email" required />
        <x-field name="phone" label="Phone number" type="tel" autocomplete="tel" inputmode="tel"
                 placeholder="+256772123456" hint="Uganda number, e.g. +256772123456 or 0772123456" required />
        <x-field name="password" label="Password" type="password" autocomplete="new-password"
                 hint="At least {{ config('bptms.security.password_min_length') }} characters with upper and lower case letters, a number and a symbol." required />
        <x-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" required />

        <button type="submit" class="btn-primary w-full">Create account</button>
    </form>

    <p class="mt-5 text-sm">Already registered? <a href="{{ route('login') }}" class="link">Sign in</a></p>
</x-layouts.guest>
