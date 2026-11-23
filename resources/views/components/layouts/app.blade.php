<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Cafetería UCH') }} · Comedor UCH</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/uch-isotipo.svg') }}">

    <!-- Fuentes institucionales UCH: Rubik (títulos) y Open Sans (cuerpo) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Rubik:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $esAdmin = request()->is('admin*') && auth()->check() && auth()->user()->rol === 'administrador';
    $esEmpleado = request()->is('empleado*') && auth()->check() && auth()->user()->rol === 'empleado';
    $esOperacion = $esAdmin || $esEmpleado;
@endphp

<body class="min-h-screen flex flex-col bg-uch-bg font-sans text-uch-text antialiased">
    @if ($esAdmin)
        <x-admin-topbar />
    @elseif ($esEmpleado)
        <x-empleado-topbar />
    @else
        @include('layouts.navigation')
    @endif

    <main class="flex-1 w-full {{ $esOperacion ? 'pt-16 pl-[76px] lg:pl-[248px]' : 'pt-16' }}">
        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
