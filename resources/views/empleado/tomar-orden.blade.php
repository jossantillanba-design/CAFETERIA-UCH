<x-layouts.app>
    <x-empleado-nav />

    @php
        $menusPayload = $menus->map(function ($menu): array {
            return [
                'id' => $menu->id,
                'titulo' => $menu->titulo,
                'precioBase' => (float) $menu->precio_base,
                'precioVariable' => (bool) $menu->es_precio_variable,
                'opciones' => $menu->opciones
                    ->filter(fn ($opcion): bool => $opcion->habilitado && $opcion->componente !== null)
                    ->groupBy(fn ($opcion): string => $opcion->componente->tiempo)
                    ->map(fn ($grupo): array => $grupo->map(fn ($opcion): array => [
                        'id' => $opcion->componente_id,
                        'nombre' => $opcion->componente->nombre,
                        'precio' => (float) $opcion->precio_opcion,
                        'stock' => $opcion->stock_raciones,
                        'agotado' => $opcion->stock_raciones !== null && $opcion->stock_raciones <= 0,
                    ])->values()->all())
                    ->all(),
            ];
        })->values()->all();

        $productosPayload = $productos->map(fn ($producto): array => [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'precio' => (float) $producto->precio,
            'stock' => (int) $producto->stock_actual,
        ])->values()->all();
    @endphp

    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">
        <x-flash-message />

        <header class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="flex items-center gap-2 text-label-md font-bold uppercase tracking-widest text-uch-teal">
                    <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                    Módulo 1 · Mostrador
                </p>
                <h1 class="mt-1 font-rubik text-headline-lg-mobile tracking-tight text-uch-navy lg:text-headline-lg">Tomar orden</h1>
                <p class="text-body-md text-uch-muted">Arma el ticket en segundos y cobra antes de enviarlo a cocina.</p>
            </div>
            <a href="{{ route('empleado.pedidos.index') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-[10px] border border-uch-border bg-uch-surface px-4 py-2.5 text-label-lg font-semibold text-uch-navy transition-colors hover:border-uch-blue">
                <span class="material-symbols-outlined text-[20px]">shopping_bag</span>
                Pedidos en vivo
            </a>
        </header>

        <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
            <!-- CATÁLOGO OPERATIVO -->
            <section class="flex flex-col gap-6" aria-label="Catálogo del día">
                <div class="rounded-card bg-uch-surface p-5 shadow-uch-card">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-rubik text-headline-sm text-uch-navy">Menús del día</h2>
                            <p class="text-body-sm text-uch-muted">Disponibles hoy · {{ ucfirst(today()->locale('es')->isoFormat('dddd D [de] MMMM')) }}</p>
                        </div>
                        <span class="rounded-full bg-uch-teal/10 px-3 py-1 text-label-md font-semibold text-[#008779]">Solo lectura</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @forelse ($menus as $menu)
                            <article class="flex flex-col rounded-xl border border-uch-border bg-uch-bg p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <h3 class="truncate font-rubik text-body-md font-bold text-uch-navy">{{ $menu->titulo }}</h3>
                                        <p class="text-body-sm capitalize text-uch-muted">{{ $menu->tipo_menu }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-uch-surface px-2.5 py-1 text-label-md font-bold text-uch-blue">
                                        {{ $menu->es_precio_variable ? 'Desde S/ '.number_format($menu->precioVisible(), 2) : 'S/ '.number_format((float) $menu->precio_base, 2) }}
                                    </span>
                                </div>

                                <div class="mt-3 space-y-2">
                                    @foreach (['entrada' => 'Entrada', 'fondo' => 'Fondo', 'refresco' => 'Refresco'] as $tiempo => $etiqueta)
                                        @php
                                            $opcionesTiempo = $menu->opciones->filter(
                                                fn ($opcion): bool => $opcion->habilitado && $opcion->componente?->tiempo === $tiempo,
                                            );
                                        @endphp
                                        <label class="block">
                                            <span class="mb-1 block text-label-md font-semibold uppercase tracking-wide text-uch-muted">{{ $etiqueta }}</span>
                                            <select x-model="selecciones[{{ $menu->id }}].{{ $tiempo }}"
                                                    class="w-full rounded-lg border-uch-border bg-uch-surface py-2 text-body-sm focus:border-uch-blue focus:ring-uch-blue">
                                                <option value="">Seleccionar {{ strtolower($etiqueta) }}</option>
                                                @foreach ($opcionesTiempo as $opcion)
                                                    <option value="{{ $opcion->componente_id }}"
                                                            @if ($opcion->stock_raciones !== null && $opcion->stock_raciones <= 0) disabled @endif>
                                                        {{ $opcion->componente->nombre }}{{ $opcion->stock_raciones !== null ? ' ('.$opcion->stock_raciones.' rac.)' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </label>
                                    @endforeach
                                </div>

                                <button type="button" @click="agregarMenu({{ $menu->id }})"
                                        @if ($menu->agotado) disabled class="mt-4 w-full cursor-not-allowed rounded-lg bg-uch-border px-3 py-2 text-label-md font-bold text-uch-muted" @else class="mt-4 w-full rounded-lg bg-uch-blue px-3 py-2 text-label-md font-bold text-white transition-colors hover:bg-uch-navy" @endif>
                                    @if ($menu->agotado) Agotado @else Agregar menú @endif
                                </button>
                            </article>
                        @empty
                            <p class="col-span-full rounded-lg bg-uch-surface p-4 text-body-sm text-uch-muted">No hay menús habilitados para hoy.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-card bg-uch-surface p-5 shadow-uch-card">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-rubik text-headline-sm text-uch-navy">Adicionales al paso</h2>
                            <p class="text-body-sm text-uch-muted">Cafés, sánguches, postres y bebidas.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
                        @forelse ($productos as $producto)
                            <button type="button" @click="agregarProducto({{ $producto->id }})"
                                    class="flex flex-col items-start gap-1 rounded-lg border border-uch-border bg-uch-bg p-3 text-left transition-colors hover:border-uch-cyan hover:bg-uch-cyan/5">
                                <span class="line-clamp-2 text-body-sm font-semibold text-uch-navy">{{ $producto->nombre }}</span>
                                <span class="text-label-md font-bold text-uch-blue">S/ {{ number_format((float) $producto->precio, 2) }}</span>
                                <span class="text-body-sm text-uch-muted">{{ $producto->stock_actual }} disp.</span>
                            </button>
                        @empty
                            <p class="col-span-full text-body-sm text-uch-muted">No hay productos con stock disponible.</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <!-- TICKET EN MEMORIA -->
            <aside class="xl:sticky xl:top-20">
                <form method="POST" action="{{ route('empleado.tomar-orden.store') }}" x-data="ordenCaja()" class="flex flex-col gap-4 rounded-card bg-uch-surface p-5 shadow-uch-card">
                    @csrf

                    <div class="flex items-center justify-between gap-2 border-b border-uch-border pb-3">
                        <h2 class="font-rubik text-headline-sm text-uch-navy">Ticket actual</h2>
                        <span class="rounded-full bg-uch-blue/10 px-2.5 py-1 text-label-md font-bold text-uch-blue">
                            <span x-text="items.length"></span> ítems
                        </span>
                    </div>

                    <div class="max-h-56 space-y-2 overflow-y-auto pr-1">
                        <template x-if="items.length === 0">
                            <p class="rounded-lg bg-uch-bg px-3 py-6 text-center text-body-sm text-uch-muted">
                                Selecciona menús o adicionales para armar el ticket.
                            </p>
                        </template>

                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex items-start gap-2 rounded-lg bg-uch-bg p-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-body-sm font-bold text-uch-navy" x-text="item.nombre"></p>
                                    <p class="line-clamp-2 text-body-sm text-uch-muted" x-text="item.detalle || 'Adicional al paso'"></p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <label class="text-label-md text-uch-muted">Cant.</label>
                                        <input type="number" x-model.number="item.cantidad" min="1" max="50"
                                               class="w-16 rounded border-uch-border py-1 text-body-sm focus:border-uch-blue focus:ring-uch-blue">
                                        <span class="text-label-md font-semibold text-uch-navy" x-text="'S/ '+(item.precio*item.cantidad).toFixed(2)"></span>
                                    </div>
                                </div>
                                <button type="button" @click="quitar(index)" aria-label="Quitar consumo"
                                        class="shrink-0 rounded-lg p-1.5 text-uch-coral transition-colors hover:bg-uch-coral/10">
                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                </button>
                            </div>
                        </template>
                    </div>

                    <template x-for="(item, index) in items" :key="'hidden-'+index">
                        <div>
                            <input type="hidden" :name="'productos['+index+'][tipo]'" :value="item.tipo">
                            <input type="hidden" :name="'productos['+index+'][cantidad]'" :value="item.cantidad">
                            <input type="hidden" :name="'productos['+index+'][id]'" :value="item.tipo === 'producto' ? item.id : ''">
                            <input type="hidden" :name="'productos['+index+'][menu_id]'" :value="item.tipo === 'menu' ? item.menu_id : ''">
                            <template x-if="item.tipo === 'menu'">
                                <div>
                                    <input type="hidden" :name="'productos['+index+'][opciones][entrada]'" :value="item.opciones.entrada">
                                    <input type="hidden" :name="'productos['+index+'][opciones][fondo]'" :value="item.opciones.fondo">
                                    <input type="hidden" :name="'productos['+index+'][opciones][refresco]'" :value="item.opciones.refresco">
                                </div>
                            </template>
                        </div>
                    </template>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-1 block text-label-md font-semibold text-uch-muted">Nombre del cliente *</span>
                            <input type="text" name="cliente_nombre_caja" x-model="clienteNombre" maxlength="120" required
                                   placeholder="Ej. Juan Pérez"
                                   class="w-full rounded-lg border-uch-border text-body-md focus:border-uch-blue focus:ring-uch-blue">
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-label-md font-semibold text-uch-muted">Modalidad *</span>
                            <select name="tipo_consumo" x-model="tipoConsumo"
                                    class="w-full rounded-lg border-uch-border text-body-md focus:border-uch-blue focus:ring-uch-blue">
                                <option value="salon">En salón</option>
                                <option value="llevar">Para llevar</option>
                            </select>
                        </label>
                    </div>

                    <fieldset>
                        <legend class="mb-2 text-label-md font-semibold text-uch-muted">Método de pago *</legend>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="opcion in [{valor:'efectivo', etiqueta:'Efectivo', icono:'payments'}, {valor:'yape_plin', etiqueta:'Yape/Plin', icono:'account_balance_wallet'}, {valor:'tarjeta', etiqueta:'Tarjeta POS', icono:'credit_card'}]" :key="opcion.valor">
                                <label class="flex cursor-pointer flex-col items-center gap-1 rounded-lg border px-2 py-2 text-center transition-colors"
                                       :class="metodoPago === opcion.valor ? 'border-uch-blue bg-uch-blue/5 text-uch-blue' : 'border-uch-border text-uch-muted'">
                                    <input type="radio" name="metodo_pago" :value="opcion.valor" x-model="metodoPago" class="sr-only">
                                    <span class="material-symbols-outlined text-[18px]" x-text="opcion.icono"></span>
                                    <span class="text-label-md font-semibold" x-text="opcion.etiqueta"></span>
                                </label>
                            </template>
                        </div>

                        <div x-show="metodoPago === 'efectivo'" x-cloak class="mt-3 grid grid-cols-2 gap-3">
                            <label class="block">
                                <span class="mb-1 block text-label-md font-semibold text-uch-muted">Monto recibido</span>
                                <input type="number" name="monto_recibido" x-model="montoRecibido" step="0.01" min="0"
                                       :required="metodoPago === 'efectivo'" :min="total.toFixed(2)"
                                       placeholder="0.00"
                                       class="w-full rounded-lg border-uch-border text-body-md focus:border-uch-blue focus:ring-uch-blue">
                            </label>
                            <div class="rounded-lg bg-uch-teal/10 px-3 py-2">
                                <span class="block text-label-md text-[#008779]">Vuelto a entregar</span>
                                <span class="font-rubik text-body-md font-bold text-[#008779]" x-text="'S/ '+vuelto.toFixed(2)"></span>
                            </div>
                        </div>
                    </fieldset>

                    <div class="flex items-center justify-between rounded-xl bg-uch-bg p-4">
                        <span class="text-body-md font-semibold text-uch-muted">Total a cobrar</span>
                        <span class="font-rubik text-headline-md text-uch-navy" x-text="'S/ '+total.toFixed(2)"></span>
                    </div>

                    <button type="submit" :disabled="items.length === 0 || ! clienteNombre.trim()"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-uch-coral px-4 py-3 text-label-lg font-bold text-white transition-all hover:bg-uch-coral-dark disabled:cursor-not-allowed disabled:opacity-50">
                        <span class="material-symbols-outlined text-[20px]">check_circle</span>
                        Confirmar cobro y enviar a cocina
                    </button>
                </form>
            </aside>
        </div>
    </div>

    @push('scripts')
        <script>
            const menusCaja = @js($menusPayload);
            const productosCaja = @js($productosPayload);

            function ordenCaja() {
                return {
                    menus: menusCaja,
                    productos: productosCaja,
                    selecciones: {},
                    items: [],
                    clienteNombre: '',
                    tipoConsumo: 'salon',
                    metodoPago: 'efectivo',
                    montoRecibido: '',

                    init() {
                        this.menus.forEach((menu) => {
                            this.selecciones[menu.id] = { entrada: '', fondo: '', refresco: '' };
                        });
                    },

                    get total() {
                        return this.items.reduce((suma, item) => suma + Number(item.precio) * Number(item.cantidad), 0);
                    },

                    get vuelto() {
                        if (this.metodoPago !== 'efectivo' || this.montoRecibido === '') {
                            return 0;
                        }

                        return Math.max(0, Number(this.montoRecibido) - this.total);
                    },

                    agregarMenu(menuId) {
                        const menu = this.menus.find((item) => item.id === menuId);
                        const seleccion = this.selecciones[menuId] || {};
                        const entrada = Number(seleccion.entrada || 0);
                        const fondo = Number(seleccion.fondo || 0);
                        const refresco = Number(seleccion.refresco || 0);

                        if (! entrada || ! fondo || ! refresco) {
                            window.alert('Selecciona entrada, fondo y refresco antes de agregar el menú.');

                            return;
                        }

                        const nombres = {};
                        let precio = Number(menu.precioBase);

                        ['entrada', 'fondo', 'refresco'].forEach((tiempo) => {
                            const opcion = ((menu.opciones || {})[tiempo] || []).find((item) => item.id === seleccion[tiempo]);
                            nombres[tiempo] = opcion ? opcion.nombre : 'Opción';

                            if (tiempo === 'fondo' && menu.precioVariable && opcion) {
                                precio = Number(opcion.precio);
                            }
                        });

                        this.items.push({
                            tipo: 'menu',
                            menu_id: menu.id,
                            id: '',
                            cantidad: 1,
                            opciones: { entrada, fondo, refresco },
                            nombre: menu.titulo,
                            detalle: `Entrada: ${nombres.entrada} · Fondo: ${nombres.fondo} · Refresco: ${nombres.refresco}`,
                            precio,
                        });

                        this.selecciones[menuId] = { entrada: '', fondo: '', refresco: '' };
                    },

                    agregarProducto(productoId) {
                        const producto = this.productos.find((item) => item.id === productoId);

                        if (! producto || producto.stock <= 0) {
                            return;
                        }

                        this.items.push({
                            tipo: 'producto',
                            id: producto.id,
                            menu_id: '',
                            cantidad: 1,
                            opciones: null,
                            nombre: producto.nombre,
                            detalle: 'Adicional al paso',
                            precio: Number(producto.precio),
                        });
                    },

                    quitar(index) {
                        this.items.splice(index, 1);
                    },
                };
            }
        </script>
    @endpush
</x-layouts.app>
