<x-layouts.app>
    @php
        $estilosEstado = [
            'pendiente' => ['etiqueta' => 'Pendiente', 'grupo' => 'curso', 'barra' => 'bg-uch-amber', 'badge' => 'bg-uch-amber/15 text-[#B45309]', 'paso' => 1, 'chip' => 'En barra'],
            'en_preparacion' => ['etiqueta' => 'En preparación', 'grupo' => 'curso', 'barra' => 'bg-uch-amber', 'badge' => 'bg-uch-cyan/15 text-uch-blue', 'paso' => 2, 'chip' => 'En cocina'],
            'listo' => ['etiqueta' => 'Listo', 'grupo' => 'curso', 'barra' => 'bg-uch-teal', 'badge' => 'bg-uch-teal/15 text-[#008779]', 'paso' => 3, 'chip' => 'Listo'],
            'entregado' => ['etiqueta' => 'Entregado', 'grupo' => 'entregado', 'barra' => 'bg-uch-teal', 'badge' => 'bg-uch-teal/15 text-[#008779]', 'paso' => 4, 'chip' => 'Finalizado'],
            'cancelado' => ['etiqueta' => 'Cancelado', 'grupo' => 'cancelado', 'barra' => 'bg-uch-coral', 'badge' => 'bg-uch-coral/15 text-uch-coral-dark', 'paso' => 0, 'chip' => 'Cancelado'],
        ];

        $pasos = [
            ['nombre' => 'Recibido', 'icono' => 'check'],
            ['nombre' => 'Preparando', 'icono' => 'skillet'],
            ['nombre' => 'Listo p/ recoger', 'icono' => 'inventory_2'],
            ['nombre' => 'Entregado', 'icono' => 'done_all'],
        ];

        $iconosProducto = [
            'cafe-americano' => 'coffee',
            'quinua' => 'local_cafe',
            'pan-con-huevo-revueto' => 'egg',
            'pan-con-huevo-revuelto' => 'egg',
            'pan-con-pollo' => 'lunch_dining',
            'pan-con-salchicha-huachana' => 'lunch_dining',
            'pan-con-queso' => 'lunch_dining',
            'brownie' => 'icecream',
        ];

        $conteo = [
            'todos' => $pedidos->count(),
            'curso' => $pedidos->whereIn('estado', ['pendiente', 'en_preparacion', 'listo'])->count(),
            'entregado' => $pedidos->where('estado', 'entregado')->count(),
            'cancelado' => $pedidos->where('estado', 'cancelado')->count(),
        ];

        $ultimo = $pedidos->first();
        $metodoUltimo = strtolower($ultimo->metodo_pago ?? '');
        $iconoPagoUltimo = str_contains($metodoUltimo, 'yape') || str_contains($metodoUltimo, 'plin')
            ? 'smartphone'
            : (str_contains($metodoUltimo, 'tarjeta') ? 'credit_card' : 'payments');
    @endphp

    <div class="w-full max-w-[1140px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Confirmación del último pedido -->
        @if (session('mensaje'))
            <section class="bg-uch-surface rounded-card shadow-uch-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative overflow-hidden mb-7">
                <div class="absolute left-0 top-0 bottom-0 w-[5px] bg-uch-teal"></div>

                <div class="flex items-center gap-4 pl-2">
                    <div class="w-12 h-12 rounded-full bg-uch-teal/15 flex items-center justify-center shrink-0 text-uch-teal">
                        <span class="material-symbols-outlined text-[28px] font-bold">check_circle</span>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="font-rubik text-title-dish sm:text-headline-sm text-uch-navy leading-snug">
                            {{ session('mensaje') }}
                        </h2>
                        <p class="text-body-md text-uch-muted flex flex-wrap items-center gap-x-2 gap-y-1 mt-0.5">
                            @if ($ultimo && $ultimo->codigo_transaccion)
                                <span>Código: <span class="font-semibold text-uch-blue">{{ $ultimo->codigo_transaccion }}</span></span>
                                <span class="text-uch-border">•</span>
                            @endif
                            @if ($ultimo && $ultimo->metodo_pago)
                                <span class="flex items-center gap-1 text-uch-blue">
                                    <span class="material-symbols-outlined text-[16px]">{{ $iconoPagoUltimo }}</span>
                                    {{ $ultimo->metodo_pago }}
                                </span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="sm:self-center shrink-0 pl-14 sm:pl-0">
                    <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-uch-bg text-uch-navy text-label-md">
                        <span class="material-symbols-outlined text-uch-amber text-[18px]">timer</span>
                        <span>Estimado: <strong class="font-bold">10 - 15 min</strong></span>
                    </div>
                </div>
            </section>
        @endif

        <div x-data="{ filtro: 'todos' }" class="space-y-7">
            <!-- Encabezado y filtros -->
            <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 pt-2">
                <div>
                    <span class="text-label-md uppercase tracking-widest text-uch-blue flex items-center gap-1.5 mb-1.5">
                        <span class="w-2 h-2 rounded-full bg-uch-cyan animate-pulse"></span>
                        Seguimiento en tiempo real
                    </span>
                    <h1 class="text-headline-lg-mobile lg:text-headline-lg font-rubik text-uch-navy tracking-tight">
                        Mis pedidos
                    </h1>
                    <p class="text-body-md sm:text-body-lg text-uch-muted mt-1">
                        Revisa el estado de tu orden actual y el historial de tus consumos en el comedor.
                    </p>
                </div>

                <nav class="flex items-center gap-2 p-1.5 bg-uch-border/50 rounded-full shrink-0 self-start md:self-auto overflow-x-auto max-w-full no-scrollbar" aria-label="Filtrar pedidos">
                    <button type="button"
                            @click="filtro = 'todos'"
                            :class="filtro === 'todos' ? 'bg-gradient-to-r from-uch-blue to-uch-cyan text-white shadow-sm' : 'bg-uch-surface text-uch-navy hover:bg-uch-bg'"
                            class="px-5 py-2 rounded-full text-label-md font-semibold transition-all flex items-center gap-1.5 shrink-0">
                        <span>Todos</span>
                        <span :class="filtro === 'todos' ? 'bg-white/25 text-white' : 'bg-uch-bg text-uch-muted'" class="px-1.5 py-0.5 rounded-full text-[11px] font-bold">{{ $conteo['todos'] }}</span>
                    </button>
                    <button type="button"
                            @click="filtro = 'curso'"
                            :class="filtro === 'curso' ? 'bg-gradient-to-r from-uch-blue to-uch-cyan text-white shadow-sm' : 'bg-uch-surface text-uch-navy hover:bg-uch-bg'"
                            class="px-4 py-2 rounded-full text-label-md font-semibold transition-all flex items-center gap-1.5 shrink-0">
                        <span>En curso</span>
                        <span :class="filtro === 'curso' ? 'bg-white/25 text-white' : 'bg-uch-amber/15 text-[#B45309]'" class="px-1.5 py-0.5 rounded-full text-[11px] font-bold">{{ $conteo['curso'] }}</span>
                    </button>
                    <button type="button"
                            @click="filtro = 'entregado'"
                            :class="filtro === 'entregado' ? 'bg-gradient-to-r from-uch-blue to-uch-cyan text-white shadow-sm' : 'bg-uch-surface text-uch-navy hover:bg-uch-bg'"
                            class="px-4 py-2 rounded-full text-label-md font-semibold transition-all flex items-center gap-1.5 shrink-0">
                        <span>Entregados</span>
                        <span :class="filtro === 'entregado' ? 'bg-white/25 text-white' : 'bg-uch-bg text-uch-muted'" class="px-1.5 py-0.5 rounded-full text-[11px] font-bold">{{ $conteo['entregado'] }}</span>
                    </button>
                    @if ($conteo['cancelado'] > 0)
                        <button type="button"
                                @click="filtro = 'cancelado'"
                                :class="filtro === 'cancelado' ? 'bg-gradient-to-r from-uch-blue to-uch-cyan text-white shadow-sm' : 'bg-uch-surface text-uch-navy hover:bg-uch-bg'"
                                class="px-4 py-2 rounded-full text-label-md font-semibold transition-all flex items-center gap-1.5 shrink-0">
                            <span>Cancelados</span>
                            <span :class="filtro === 'cancelado' ? 'bg-white/25 text-white' : 'bg-uch-coral/15 text-uch-coral-dark'" class="px-1.5 py-0.5 rounded-full text-[11px] font-bold">{{ $conteo['cancelado'] }}</span>
                        </button>
                    @endif
                </nav>
            </header>

            <!-- Grilla de pedidos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-7">
                @forelse ($pedidos as $indice => $pedido)
                    @php
                        $estilo = $estilosEstado[$pedido->estado] ?? [
                            'etiqueta' => ucfirst(str_replace('_', ' ', $pedido->estado ?? 'Registrado')),
                            'grupo' => 'curso',
                            'barra' => 'bg-uch-blue',
                            'badge' => 'bg-uch-bg text-uch-navy',
                            'paso' => 1,
                            'chip' => 'Registrado',
                        ];

                        $itemsPedido = $pedido->detalles->map(function ($detalle) {
                            $esMenu = $detalle->tipo_item === 'menu';

                            return [
                                'id' => $esMenu
                                    ? 'menu-' . $detalle->menu_id . '-' . $detalle->entrada_id . '-' . $detalle->fondo_id . '-' . $detalle->refresco_id
                                    : $detalle->producto_id,
                                'tipo' => $detalle->tipo_item,
                                'menu_id' => $detalle->menu_id,
                                'opciones' => $esMenu ? [
                                    'entrada' => $detalle->entrada_id,
                                    'fondo' => $detalle->fondo_id,
                                    'refresco' => $detalle->refresco_id,
                                ] : null,
                                'nombre' => $esMenu ? ($detalle->menu->titulo ?? 'Menú del día') : ($detalle->producto->nombre ?? 'Producto'),
                                'detalle' => $detalle->detalle_menu ?? '',
                                'precio' => (float) $detalle->precio_unitario,
                                'cantidad' => $detalle->cantidad,
                            ];
                        })->values();

                        $metodo = strtolower($pedido->metodo_pago ?? '');
                        $iconoPago = str_contains($metodo, 'yape') || str_contains($metodo, 'plin')
                            ? 'smartphone'
                            : (str_contains($metodo, 'tarjeta') ? 'credit_card' : 'payments');
                    @endphp

                    <article x-data="{ detalle: false }"
                             x-show="filtro === 'todos' || $el.dataset.grupo === filtro"
                             x-cloak
                             data-grupo="{{ $estilo['grupo'] }}"
                             class="pedido-card bg-uch-surface rounded-card shadow-uch-card p-6 flex flex-col justify-between relative overflow-hidden"
                             style="animation-delay: {{ $indice * 70 }}ms">
                        <div class="absolute top-0 left-0 right-0 h-[4px] {{ $estilo['barra'] }}"></div>

                        <div class="space-y-6">
                            <!-- Cabecera -->
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-rubik text-headline-sm text-uch-navy">
                                            Pedido #{{ str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT) }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded bg-uch-bg text-uch-muted text-[11px] font-semibold">{{ $estilo['chip'] }}</span>
                                    </div>
                                    <p class="text-body-sm text-uch-muted flex items-center gap-1 mt-1">
                                        <span class="material-symbols-outlined text-[15px]">event</span>
                                        {{ $pedido->created_at->format('d/m/Y • h:i a') }}
                                    </p>
                                </div>

                                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full {{ $estilo['badge'] }} text-label-md shadow-sm shrink-0">
                                    @if ($estilo['paso'] > 0 && $estilo['paso'] < 4)
                                        <span class="w-2.5 h-2.5 rounded-full bg-current animate-ping"></span>
                                    @else
                                        <span class="material-symbols-outlined text-[16px]">{{ $estilo['paso'] === 4 ? 'check_circle' : 'info' }}</span>
                                    @endif
                                    {{ $estilo['etiqueta'] }}
                                </span>
                            </div>

                            <!-- Progreso -->
                            @if ($estilo['paso'] > 0)
                                <section aria-label="Progreso del pedido" class="bg-uch-bg/70 rounded-xl p-4">
                                    <div class="grid grid-cols-4 relative">
                                        <div class="absolute top-4 left-6 right-6 h-[3px] bg-uch-border z-0"></div>
                                        <div class="absolute top-4 left-6 h-[3px] bg-uch-teal z-0" style="width: {{ max(0, ($estilo['paso'] - 1) * 33.33) }}%"></div>

                                        @foreach ($pasos as $indicePaso => $paso)
                                            @php
                                                $completado = $estilo['paso'] === 4 || $indicePaso < $estilo['paso'] - 1;
                                                $activo = $indicePaso === $estilo['paso'] - 1 && $estilo['paso'] < 4;
                                            @endphp
                                            <div class="flex flex-col items-center text-center relative z-10">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $completado ? 'bg-uch-teal text-white shadow-md' : ($activo ? 'bg-uch-cyan text-white shadow-lg ring-4 ring-uch-cyan/25 animate-pulse' : 'bg-uch-border text-uch-muted') }}">
                                                    <span class="material-symbols-outlined text-[18px] {{ $completado ? 'font-bold' : '' }}">
                                                        {{ $completado ? 'check' : $paso['icono'] }}
                                                    </span>
                                                </div>
                                                <span class="text-label-md text-body-sm mt-2 leading-tight {{ $activo ? 'text-uch-blue font-bold' : ($completado ? 'text-[#004C6B] font-semibold' : 'text-uch-muted') }}">
                                                    {{ $paso['nombre'] }}
                                                </span>
                                                <span class="text-body-sm text-[11px] {{ $activo ? 'text-uch-cyan font-semibold' : 'text-uch-muted' }}">
                                                    {{ $activo ? 'En curso' : ($completado ? 'Completado' : '--:--') }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @else
                                <div class="p-3 rounded-lg bg-uch-coral/10 text-uch-coral-dark text-body-sm">
                                    Este pedido fue cancelado. Si necesitas ayuda, acércate a ventanilla.
                                </div>
                            @endif

                            <!-- Artículos -->
                            <div class="space-y-3">
                                <h3 class="text-label-md uppercase tracking-wider text-uch-muted">Artículos en esta orden</h3>
                                <div class="rounded-xl divide-y divide-uch-border">
                                    @foreach ($pedido->detalles as $detalle)
                                        @php
                                            $esMenu = $detalle->tipo_item === 'menu';
                                            $slugProducto = \Illuminate\Support\Str::slug($detalle->producto->nombre ?? '');
                                            $iconoProducto = $esMenu ? 'lunch_dining' : ($iconosProducto[$slugProducto] ?? 'restaurant');
                                            $nombreArticulo = $esMenu
                                                ? ($detalle->menu->titulo ?? 'Menú del día')
                                                : ($detalle->producto->nombre ?? 'Producto');
                                        @endphp
                                        <div class="py-2.5 flex items-center justify-between gap-4">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-9 h-9 rounded-lg bg-uch-bg flex items-center justify-center text-uch-navy shrink-0">
                                                    <span class="material-symbols-outlined text-[20px]">{{ $iconoProducto }}</span>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-rubik text-body-md text-uch-navy font-semibold truncate">
                                                        {{ $detalle->cantidad }}x {{ $nombreArticulo }}
                                                    </p>
                                                    @if ($esMenu && $detalle->detalle_menu)
                                                        <p class="text-body-sm text-uch-muted truncate">{{ $detalle->detalle_menu }}</p>
                                                    @endif
                                                    <p class="text-body-sm text-uch-muted">S/ {{ number_format($detalle->precio_unitario, 2) }} c/u</p>
                                                </div>
                                            </div>
                                            <span class="font-rubik text-body-md text-uch-navy font-bold shrink-0">
                                                S/ {{ number_format($detalle->precio_unitario * $detalle->cantidad, 2) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Pie de tarjeta -->
                        <footer class="pt-5 mt-6 border-t border-uch-border space-y-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md {{ $pedido->pagado ? 'bg-uch-teal/15 text-[#007A70]' : 'bg-uch-amber/15 text-[#B45309]' }} text-body-sm font-semibold">
                                    <span class="material-symbols-outlined text-[16px]">{{ $pedido->pagado ? 'verified' : 'schedule' }}</span>
                                    {{ $pedido->pagado ? 'Pagado · ' . ($pedido->metodo_pago ?? 'Método registrado') : 'Pendiente de cobro en caja' }}
                                </span>
                                <div class="text-right">
                                    <span class="text-body-sm text-uch-muted block">{{ $pedido->pagado ? 'Total pagado' : 'Total' }}</span>
                                    <span class="font-rubik text-headline-sm text-uch-navy font-bold">S/ {{ number_format($pedido->total, 2) }}</span>
                                </div>
                            </div>

                            <div x-show="detalle" x-cloak class="rounded-lg bg-uch-bg p-3 text-body-sm text-uch-muted space-y-1">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px] {{ $pedido->pagado ? 'text-uch-teal' : 'text-uch-amber' }}">{{ $iconoPago }}</span>
                                    {{ $pedido->metodo_pago ?? 'Método no registrado' }}
                                </span>
                                @if ($pedido->codigo_transaccion)
                                    <p>Código: <strong class="text-uch-navy">{{ $pedido->codigo_transaccion }}</strong></p>
                                @endif
                                <p>Estado actual: <strong class="text-uch-navy">{{ $estilo['etiqueta'] }}</strong></p>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-1">
                                <button type="button"
                                        @click="detalle = !detalle"
                                        class="py-2.5 px-4 rounded-xl bg-uch-bg hover:bg-uch-border text-uch-blue text-label-md transition-all flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                                    <span x-text="detalle ? 'Ocultar detalle' : 'Ver detalle'"></span>
                                </button>
                                <button type="button"
                                        @click="volverAPedir(@js($itemsPedido))"
                                        class="py-2.5 px-4 rounded-xl bg-uch-coral hover:bg-uch-coral-dark text-white text-label-md shadow-sm hover:shadow transition-all flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px]">replay</span>
                                    <span>Volver a pedir</span>
                                </button>
                            </div>
                        </footer>
                    </article>
                @empty
                    <div class="col-span-full bg-uch-surface rounded-card shadow-uch-card p-10 text-center">
                        <span class="material-symbols-outlined text-uch-muted text-[44px]">receipt_long</span>
                        <p class="font-rubik text-headline-sm text-uch-navy mt-space-sm">Todavía no tienes pedidos</p>
                        <p class="text-body-md text-uch-muted mt-space-xs">Cuando hagas tu primer pedido, podrás seguirlo desde aquí.</p>
                        <a href="/menu" class="inline-flex items-center gap-2 mt-space-md bg-uch-coral text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-uch-coral-dark transition-colors">
                            Ir al menú
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Estados filtrados sin resultados -->
            <p x-show="filtro === 'curso' && {{ $conteo['curso'] }} === 0" x-cloak class="text-center text-body-md text-uch-muted py-8">
                No tienes pedidos en curso en este momento.
            </p>
            <p x-show="filtro === 'entregado' && {{ $conteo['entregado'] }} === 0" x-cloak class="text-center text-body-md text-uch-muted py-8">
                Todavía no tienes pedidos entregados.
            </p>

            <!-- Soporte -->
            <aside id="soporte" class="p-5 rounded-card bg-uch-surface shadow-uch-card flex flex-col sm:flex-row items-center justify-between gap-4 mt-8">
                <div class="flex items-center gap-3.5 text-center sm:text-left">
                    <div class="w-10 h-10 rounded-full bg-uch-blue/10 text-uch-blue flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">contact_support</span>
                    </div>
                    <div>
                        <h4 class="font-rubik text-body-md text-uch-navy font-bold">¿Tienes dudas o necesitas modificar tu pedido?</h4>
                        <p class="text-body-sm text-uch-muted">Acércate a ventanilla del Comedor Universitario en el Pabellón A o vuelve al menú para hacer otro pedido.</p>
                    </div>
                </div>
                <a href="/menu" class="shrink-0 px-4 py-2 rounded-xl bg-uch-bg text-uch-blue text-body-sm hover:bg-uch-border transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px]">restaurant_menu</span>
                    Volver al menú
                </a>
            </aside>
        </div>
    </div>

    <script>
        function volverAPedir(items) {
            const actuales = (() => {
                try {
                    return JSON.parse(localStorage.getItem('carrito') || '[]');
                } catch (error) {
                    return [];
                }
            })();

            items.forEach((item) => {
                const existente = actuales.find((producto) => producto.id === item.id);

                if (existente) {
                    existente.cantidad += item.cantidad;
                } else {
                    actuales.push({ ...item });
                }
            });

            localStorage.setItem('carrito', JSON.stringify(actuales));
            localStorage.setItem('carrito_checkout', JSON.stringify(actuales));
            window.dispatchEvent(new CustomEvent('carrito-actualizado', { detail: actuales }));
            window.location.href = '/menu';
        }
    </script>

    @if (session('mensaje'))
        <script>
            localStorage.removeItem('carrito');
            localStorage.removeItem('carrito_checkout');
        </script>
    @endif
</x-layouts.app>
