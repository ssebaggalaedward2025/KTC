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
    <main class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-4 py-10">
        <a href="{{ url('/') }}" class="mb-6 block text-center">
            <span class="block text-sm font-semibold uppercase tracking-wide text-brand-700">{{ config('bptms.council_name') }}</span>
            <span class="block text-lg font-bold">Building Plan Tracking</span>
        </a>

        <div class="card">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-brand-50 p-3 text-sm text-brand-800" role="status">{{ session('status') }}</div>
            @endif

            {{ $slot }}
        </div>
    </main>
</body>
</html>
