<x-layouts.app>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Pedidos pendientes</h1>
        <a href="/admin/pedidos/crear" class="bg-blue-900 text-white px-4 py-2 rounded">Nuevo ticket</a>
    </div>

    @if (session('mensaje'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('mensaje') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($pedidos as $pedido)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-2">
                    <h2 class="font-bold text-lg">Pedido #{{ $pedido->id }}</h2>
                    <span class="text-xs px-2 py-1 rounded bg-yellow-100 text-yellow-800 capitalize">
                        {{ str_replace('_', ' ', $pedido->estado) }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mb-2">{{ $pedido->cliente->name ?? 'Cliente en mostrador' }}</p>

                <ul class="text-sm mb-3">
                    @foreach ($pedido->detalles as $detalle)
                        <li>{{ $detalle->cantidad }}x {{ $detalle->producto->nombre }}</li>
                    @endforeach
                </ul>

                <p class="font-bold text-blue-900 mb-3">Total: S/{{ number_format($pedido->total, 2) }}</p>

                <form action="/admin/pedidos/{{ $pedido->id }}/estado" method="POST">
                    @csrf
                    @method('PUT')
                    <select name="estado" onchange="this.form.submit()" class="w-full border rounded p-2">
                        <option value="pendiente" @selected($pedido->estado === 'pendiente')>Pendiente</option>
                        <option value="en_preparacion" @selected($pedido->estado === 'en_preparacion')>En preparación</option>
                        <option value="listo" @selected($pedido->estado === 'listo')>Listo</option>
                        <option value="entregado" @selected($pedido->estado === 'entregado')>Entregado</option>
                        <option value="cancelado" @selected($pedido->estado === 'cancelado')>Cancelado</option>
                    </select>
                </form>
            </div>
        @empty
            <p class="text-gray-500 col-span-full">No hay pedidos pendientes por ahora.</p>
        @endforelse
    </div>
</x-layouts.app>