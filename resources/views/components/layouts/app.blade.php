<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Cafetería UCH') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-amber-50 text-gray-800">
    <nav class="bg-blue-200 p-4" x-data="{ abierto: false }">
        <div class="flex justify-between items-center">
            <a href="/" class="font-bold text-white">Cafetería UCH</a>

            <button @click="abierto = !abierto" class="sm:hidden text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div class="hidden sm:flex gap-6 text-white items-center">
                <a href="/menu">Menú</a>
                @auth
                    @if (auth()->user()->rol === 'administrador')
                        <a href="/admin/dashboard">Dashboard</a>
                    @endif
                    @if (in_array(auth()->user()->rol, ['administrador', 'empleado']))
                        <a href="/admin/productos">Admin</a>
                    @endif
                    @if (in_array(auth()->user()->rol, ['administrador', 'empleado']))
                        <a href="/admin/pedidos">Pedidos</a>
                    @endif
                    <a href="/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar sesión</a>
                    @else
                    <a href="{{ route('login') }}">Iniciar sesión</a>
                @endauth
            </div>
        </div>

        <div x-show="abierto" x-cloak class="flex flex-col gap-3 mt-3 sm:hidden text-white">
            <a href="/menu">Menú</a>
            @auth
                @if (auth()->user()->rol === 'administrador')
                    <a href="/admin/dashboard">Dashboard</a>
                @endif
                @if (in_array(auth()->user()->rol, ['administrador', 'empleado']))
                    <a href="/admin/productos">Admin</a>
                @endif
                @if (auth()->user()->rol === 'cliente')
                    <a href="/mis-pedidos">Mis pedidos</a>
                @endif
                <a href="/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar sesión</a>
            @else
                <a href="{{ route('login') }}">Iniciar sesión</a>
            @endauth
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </nav>

    <main class="max-w-5xl mx-auto p-4 sm:p-6">
        {{ $slot }}
    </main>
</body>
</html>