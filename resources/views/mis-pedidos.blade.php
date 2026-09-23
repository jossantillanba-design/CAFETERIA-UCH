<script>
    localStorage.removeItem('carrito');
</script>
<x-layouts.app>
    <h1 class="text-3xl font-bold mb-6">Mis pedidos</h1>

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
                <ul class="text-sm mb-3">
                    @foreach ($pedido->detalles as $detalle)
                        <li>{{ $detalle->cantidad }}x {{ $detalle->producto->nombre }}</li>
                    @endforeach
                </ul>
                <p class="font-bold text-blue-900">Total: S/{{ number_format($pedido->total, 2) }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ $pedido->created_at->format('d/m/Y H:i') }}</p>
            </div>
        @empty
            <p class="text-gray-500 col-span-full">Todavía no hiciste ningún pedido.</p>
        @endforelse
    </div>
</x-layouts.app>