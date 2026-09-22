<x-layouts.app>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Administrar productos</h1>
        <a href="/admin/productos/crear" class="bg-blue-300 text-white px-4 py-2 rounded">Nuevo producto</a>
    </div>

    @if (session('mensaje'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('mensaje') }}
        </div>
    @endif

    <table class="w-full bg-white rounded shadow">
        <thead>
            <tr class="text-left border-b">
                <th class="p-3">Nombre</th>
                <th class="p-3">Precio</th>
                <th class="p-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $producto)
                <tr class="border-b">
                    <td class="p-3">{{ $producto->nombre }}</td>
                    <td class="p-3">S/{{ number_format($producto->precio, 2) }}</td>
                    <td class="p-3 flex gap-3">
                        <a href="/admin/productos/{{ $producto->id }}/editar" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Editar</a>
                        <form action="/admin/productos/{{ $producto->id }}" method="POST" onsubmit="return confirm('¿Eliminar este producto?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-layouts.app>