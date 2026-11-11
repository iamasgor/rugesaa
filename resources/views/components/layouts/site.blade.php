@props(['title' => null, 'active' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? $title.' — ' : '' }}RUGESAA</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">
        <link rel="icon" href="/favicon-512x512.png" type="image/png" sizes="512x512">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-paper font-sans text-ink antialiased">
        <x-site.header :active="$active" />

        @if (session('status') || session('error'))
            <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-8">
                @if (session('status'))
                    <p role="status" class="rounded-sm border border-forest bg-paper-dim px-5 py-4 text-[15px] font-semibold text-forest">{{ session('status') }}</p>
                @endif
                @if (session('error'))
                    <p role="alert" class="rounded-sm border border-amber-deep bg-card px-5 py-4 text-[15px] font-semibold text-amber-deep">{{ session('error') }}</p>
                @endif
            </div>
        @endif

        <main>
            {{ $slot }}
        </main>

        <x-site.footer />
    </body>
</html>
