<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cafetería UCH</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-amber-50 text-gray-800">
    <nav class="bg-blue-200 text-white p-4 flex gap-6">
    <a href="/" class="font-bold">Cafetería UCH</a>
    <a href="/menu">Menú</a>
    @auth
        @if (in_array(auth()->user()->rol, ['administrador', 'empleado']))
            <a href="/admin/productos">Admin</a>
        @endif
        <a href="/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar sesión</a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    @else
        <a href="{{ route('login') }}">Iniciar sesión</a>
    @endauth
    </nav>
    <main class="max-w-5xl mx-auto p-6">
        {{ $slot }}
    </main>
</body>
</html>