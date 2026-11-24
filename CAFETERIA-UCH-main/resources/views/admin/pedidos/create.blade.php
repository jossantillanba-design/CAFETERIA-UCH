<x-layouts.app>
    <x-admin-nav />

    <div class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <x-flash-message />

        <header>
            <a href="/admin/pedidos" class="inline-flex items-center gap-1.5 text-body-sm text-uch-muted hover:text-uch-blue mb-3">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Volver a pedidos
            </a>
            <h1 class="text-headline-lg-mobile lg:text-headline-lg font-rubik text-uch-navy tracking-tight">Nuevo ticket</h1>
            <p class="text-body-md text-uch-muted mt-1">Registro manual de consumos en mostrador.</p>
        </header>

        <form action="/admin/pedidos" method="POST" x-data="ticket()" class="bg-uch-surface rounded-card shadow-uch-card p-6 space-y-5">
            @csrf

            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
                        <select :name="'productos[' + index + '][id]'" x-model="item.id" required class="flex-1 rounded-lg border-uch-border text-body-md focus:border-uch-blue focus:ring-uch-blue">
                            <option value="">Selecciona un producto</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->nombre }} — S/ {{ number_format($producto->precio, 2) }}</option>
                            @endforeach
                        </select>
                        <input type="number" :name="'productos[' + index + '][cantidad]'" x-model="item.cantidad" min="1" class="w-full sm:w-24 rounded-lg border-uch-border text-body-md focus:border-uch-blue focus:ring-uch-blue">
                        <button type="button" @click="items.splice(index, 1)" class="shrink-0 px-3 py-2 rounded-lg bg-uch-coral/10 text-uch-coral text-body-sm hover:bg-uch-coral/20 transition-colors">Quitar</button>
                    </div>
                </template>
            </div>

            <button type="button" @click="items.push({id: '', cantidad: 1})" class="inline-flex items-center gap-1.5 text-body-sm font-semibold text-uch-blue hover:underline">
                <span class="material-symbols-outlined text-[18px]">add</span> Agregar producto
            </button>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-uch-border">
                <a href="/admin/pedidos" class="px-4 py-2.5 rounded-xl bg-uch-bg text-uch-navy text-body-sm hover:bg-uch-border">Cancelar</a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-uch-coral text-white text-body-sm font-semibold hover:bg-uch-coral-dark transition-colors">Crear ticket</button>
            </div>
        </form>
    </div>

    <script>
        function ticket() {
            return {
                items: [{ id: '', cantidad: 1 }],
            };
        }
    </script>
</x-layouts.app>
