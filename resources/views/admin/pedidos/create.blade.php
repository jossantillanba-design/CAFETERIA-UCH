<x-layouts.app>
    <h1 class="text-3xl font-bold mb-6">Nuevo ticket</h1>

    <form action="/admin/pedidos" method="POST" x-data="ticket()" class="bg-white p-6 rounded shadow max-w-2xl">
        @csrf

        <div class="space-y-3 mb-4">
            <template x-for="(item, index) in items" :key="index">
                <div class="flex gap-3 items-center">
                    <select :name="'productos[' + index + '][id]'" x-model="item.id" class="flex-1 border rounded p-2">
                        <option value="">Selecciona un producto</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->nombre }} - S/{{ number_format($producto->precio, 2) }}</option>
                        @endforeach
                    </select>
                    <input type="number" :name="'productos[' + index + '][cantidad]'" x-model="item.cantidad" min="1" value="1" class="w-20 border rounded p-2">
                    <button type="button" @click="items.splice(index, 1)" class="text-red-600">Quitar</button>
                </div>
            </template>
        </div>

        <button type="button" @click="items.push({id: '', cantidad: 1})" class="text-blue-700 mb-4">+ Agregar producto</button>

        <div>
            <button type="submit" class="bg-blue-900 text-white px-4 py-2 rounded">Crear ticket</button>
            <a href="/admin/pedidos" class="ml-3 text-gray-600">Cancelar</a>
        </div>
    </form>

    <script>
        function ticket() {
            return {
                items: [{ id: '', cantidad: 1 }]
            }
        }
    </script>
</x-layouts.app>