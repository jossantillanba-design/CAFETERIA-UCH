<x-layouts.app>
    <div x-data="carrito()" x-init="init()">
        <h1 class="text-3xl font-bold mb-6">Nuestro menú</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($productos as $producto)
                <div class="bg-white rounded-lg shadow p-4">
                    <h2 class="text-xl font-semibold">{{ $producto->nombre }}</h2>
                    <p class="text-gray-600 mt-1">{{ $producto->descripcion }}</p>
                    <p class="text-blue-900 font-bold mt-3">S/{{ number_format($producto->precio, 2) }}</p>
                    <button
                        @click="agregar({{ $producto->id }}, '{{ addslashes($producto->nombre) }}', {{ $producto->precio }})"
                        class="mt-3 w-full bg-blue-900 text-white py-2 rounded">
                        Agregar al pedido
                    </button>
                </div>
            @endforeach
        </div>

        <!-- Botón flotante del carrito -->
        <button
            @click="abierto = true"
            x-show="items.length > 0"
            class="fixed bottom-6 right-6 bg-blue-900 text-white px-5 py-3 rounded-full shadow-lg font-bold">
            🛒 <span x-text="items.length"></span> · S/<span x-text="total().toFixed(2)"></span>
        </button>

        <!-- Panel lateral del carrito -->
        <div x-show="abierto" x-cloak class="fixed inset-0 bg-black/50 flex justify-end z-50" @click.self="abierto = false">
            <div class="bg-white w-full max-w-sm h-full p-6 overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Tu pedido</h2>
                    <button @click="abierto = false" class="text-gray-500">✕</button>
                </div>

                <template x-if="items.length === 0">
                    <p class="text-gray-500">Todavía no agregaste productos.</p>
                </template>

                <template x-for="(item, index) in items" :key="item.id">
                    <div class="flex justify-between items-center mb-3 border-b pb-3">
                        <div>
                            <p class="font-semibold" x-text="item.nombre"></p>
                            <p class="text-sm text-gray-500">S/<span x-text="item.precio.toFixed(2)"></span> c/u</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="item.cantidad > 1 ? item.cantidad-- : items.splice(index, 1)" class="px-2 border rounded">-</button>
                            <span x-text="item.cantidad"></span>
                            <button @click="item.cantidad++" class="px-2 border rounded">+</button>
                        </div>
                    </div>
                </template>

                <div class="mt-4 font-bold text-lg" x-show="items.length > 0">
                    Total: S/<span x-text="total().toFixed(2)"></span>
                </div>

                <form action="/pedidos" method="POST" x-show="items.length > 0" class="mt-4">
                    @csrf
                    <template x-for="(item, index) in items" :key="item.id">
                        <div>
                            <input type="hidden" :name="'productos[' + index + '][id]'" :value="item.id">
                            <input type="hidden" :name="'productos[' + index + '][cantidad]'" :value="item.cantidad">
                        </div>
                    </template>
                    <button type="submit" class="w-full bg-green-700 text-white py-2 rounded font-bold">
                        Confirmar pedido
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function carrito() {
            return {
                items: [],
                abierto: false,
                init() {
                    const guardado = localStorage.getItem('carrito');
                    if (guardado) {
                        this.items = JSON.parse(guardado);
                    }
                    this.$watch('items', () => {
                        localStorage.setItem('carrito', JSON.stringify(this.items));
                    });
                },
                agregar(id, nombre, precio) {
                    const existente = this.items.find(i => i.id === id);
                    if (existente) {
                        existente.cantidad++;
                    } else {
                        this.items.push({ id, nombre, precio, cantidad: 1 });
                    }
                    this.abierto = true;
                },
                total() {
                    return this.items.reduce((sum, i) => sum + (i.precio * i.cantidad), 0);
                }
            }
        }
    </script>
</x-layouts.app>