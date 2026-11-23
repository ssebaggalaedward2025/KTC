@props(['title' => null])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-3">
            <a href="{{ route('dashboard') }}" class="font-bold">
                <span class="text-brand-700">KTC</span> Building Plans
            </a>
            <nav class="flex items-center gap-4 text-sm">
                @if (auth()->user()?->isStaff())
                    <a href="{{ url('/staff') }}" class="link">Staff panel</a>
                @endif
                <a href="{{ route('account.security') }}" class="link">My account</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="link">Sign out</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-6">
        @if ($title)
            <h1 class="mb-4 text-2xl font-bold">{{ $title }}</h1>
        @endif
        {{ $slot }}
    </main>
</body>
</html>
