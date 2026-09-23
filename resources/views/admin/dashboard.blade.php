<x-layouts.app>
    <h1 class="text-3xl font-bold mb-6">Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-gray-500 text-sm">Ventas de hoy</p>
            <p class="text-2xl font-bold text-blue-900">S/{{ number_format($ventasHoy, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-gray-500 text-sm">Pedidos de hoy</p>
            <p class="text-2xl font-bold text-blue-900">{{ $pedidosHoy }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-gray-500 text-sm">Pedidos pendientes</p>
            <p class="text-2xl font-bold text-blue-900">{{ $pedidosPendientes }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-gray-500 text-sm">Productos activos</p>
            <p class="text-2xl font-bold text-blue-900">{{ $totalProductos }}</p>
        </div>
    </div>

    <h2 class="text-xl font-bold mb-4">Últimos pedidos</h2>
    <div class="overflow-x-auto">
    <table class="w-full bg-white rounded shadow">
        <thead>
            <tr class="text-left border-b">
                <th class="p-3">#</th>
                <th class="p-3">Cliente</th>
                <th class="p-3">Estado</th>
                <th class="p-3">Total</th>
                <th class="p-3">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ultimosPedidos as $pedido)
                <tr class="border-b">
                    <td class="p-3">#{{ $pedido->id }}</td>
                    <td class="p-3">{{ $pedido->cliente->name ?? 'Sin registrar' }}</td>
                    <td class="p-3 capitalize">{{ str_replace('_', ' ', $pedido->estado) }}</td>
                    <td class="p-3">S/{{ number_format($pedido->total, 2) }}</td>
                    <td class="p-3">{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-3 text-center text-gray-500">Aún no hay pedidos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</x-layouts.app>