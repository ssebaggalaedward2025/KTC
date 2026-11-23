<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
    <main class="mx-auto max-w-3xl px-4 py-12">
        <p class="text-sm font-semibold uppercase tracking-wide text-brand-700">{{ config('bptms.council_name') }} · Town Engineer's Department</p>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Building Plan Applications</h1>
        <p class="mt-4 text-lg text-gray-700">Submit your building plan, follow it through every stage, respond to corrections and download your permit, without visiting the office to ask where your file is.</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">Go to my dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn-primary">Apply: create an account</a>
                <a href="{{ route('login') }}" class="btn-secondary">Sign in</a>
            @endauth
        </div>

        <p class="mt-10 text-sm text-gray-500">
            This system complements the national Building Information Management System (BIMS).
            Council staff sign in with their official account.
        </p>
    </main>
</body>
</html>
