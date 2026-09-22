<x-layouts.app>
    <h1 class="text-3xl font-bold mb-6">Nuestro menú</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($productos as $producto)
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="text-xl font-semibold">{{ $producto->nombre }}</h2>
                <p class="text-gray-600 mt-1">{{ $producto->descripcion }}</p>
                <p class="text-blue-900 font-bold mt-3">S/{{ number_format($producto->precio, 2) }}</p>
            </div>
        @endforeach
    </div>
</x-layouts.app>