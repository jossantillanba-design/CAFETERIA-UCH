<x-layouts.app>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-500 mt-1">Resumen de la actividad de hoy</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="bg-green-100 text-green-700 rounded-full w-12 h-12 flex items-center justify-center text-xl">
                💰
            </div>
            <div>
                <p class="text-gray-500 text-sm">Ventas de hoy</p>
                <p class="text-2xl font-bold text-gray-800">S/{{ number_format($ventasHoy, 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="bg-blue-100 text-blue-700 rounded-full w-12 h-12 flex items-center justify-center text-xl">
                🧾
            </div>
            <div>
                <p class="text-gray-500 text-sm">Pedidos de hoy</p>
                <p class="text-2xl font-bold text-gray-800">{{ $pedidosHoy }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="bg-yellow-100 text-yellow-700 rounded-full w-12 h-12 flex items-center justify-center text-xl">
                ⏳
            </div>
            <div>
                <p class="text-gray-500 text-sm">Pedidos pendientes</p>
                <p class="text-2xl font-bold text-gray-800">{{ $pedidosPendientes }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="bg-purple-100 text-purple-700 rounded-full w-12 h-12 flex items-center justify-center text-xl">
                ☕
            </div>
            <div>
                <p class="text-gray-500 text-sm">Productos activos</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalProductos }}</p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Últimos pedidos</h2>
        <a href="/admin/pedidos" class="text-blue-700 text-sm font-semibold hover:underline">Ver todos →</a>
    </div>

    <div class="overflow-x-auto bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full">
            <thead>
                <tr class="text-left border-b border-gray-100 bg-gray-50">
                    <th class="p-4 text-gray-500 text-sm font-semibold">#</th>
                    <th class="p-4 text-gray-500 text-sm font-semibold">Cliente</th>
                    <th class="p-4 text-gray-500 text-sm font-semibold">Estado</th>
                    <th class="p-4 text-gray-500 text-sm font-semibold">Total</th>
                    <th class="p-4 text-gray-500 text-sm font-semibold">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ultimosPedidos as $pedido)
                    @php
                        $colores = [
                            'pendiente' => 'bg-yellow-100 text-yellow-800',
                            'en_preparacion' => 'bg-blue-100 text-blue-800',
                            'listo' => 'bg-purple-100 text-purple-800',
                            'entregado' => 'bg-green-100 text-green-800',
                            'cancelado' => 'bg-red-100 text-red-800',
                        ];
                    @endphp
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                        <td class="p-4 font-semibold text-gray-700">#{{ $pedido->id }}</td>
                        <td class="p-4 text-gray-700">{{ $pedido->cliente->name ?? 'Sin registrar' }}</td>
                        <td class="p-4">
                            <span class="text-xs font-semibold px-3 py-1 rounded-full capitalize {{ $colores[$pedido->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ str_replace('_', ' ', $pedido->estado) }}
                            </span>
                        </td>
                        <td class="p-4 font-semibold text-gray-800">S/{{ number_format($pedido->total, 2) }}</td>
                        <td class="p-4 text-gray-500 text-sm">{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-400">Aún no hay pedidos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>