<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} · Comedor UCH</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/uch-isotipo.svg') }}">

    <!-- Fuentes institucionales UCH -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Rubik:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-uch-navy-dark">
    <div>
        <a href="/">
            <img src="{{ asset('images/uch-logo-blanco.svg') }}" alt="Comedor UCH" class="h-20 w-auto object-contain">
        </a>
    </div>

    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-uch-surface shadow-uch-card-hover overflow-hidden sm:rounded-card">
        {{ $slot }}
    </div>
</body>
</html>
