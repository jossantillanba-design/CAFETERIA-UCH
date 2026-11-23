<x-layouts.app>
    <x-admin-nav />

    @php
        $coloresMenu = [
            'blue' => ['barra' => 'bg-uch-blue', 'etiqueta' => 'text-uch-blue', 'progreso' => 'bg-uch-cyan'],
            'coral' => ['barra' => 'bg-uch-coral', 'etiqueta' => 'text-uch-coral', 'progreso' => 'bg-uch-coral'],
            'cyan' => ['barra' => 'bg-uch-cyan', 'etiqueta' => 'text-[#0087B8]', 'progreso' => 'bg-uch-cyan'],
        ];

        $estadosPedido = [
            'pendiente' => ['clase' => 'bg-uch-coral/10 text-uch-coral-dark', 'punto' => 'bg-uch-coral', 'texto' => 'Pendiente'],
            'en_preparacion' => ['clase' => 'bg-uch-blue/10 text-uch-blue', 'punto' => 'bg-uch-blue', 'texto' => 'En preparación'],
            'listo' => ['clase' => 'bg-uch-cyan/15 text-[#0060A9]', 'punto' => 'bg-uch-cyan', 'texto' => 'Listo'],
            'entregado' => ['clase' => 'bg-uch-teal/15 text-[#008779]', 'punto' => 'bg-uch-teal', 'texto' => 'Entregado'],
            'cancelado' => ['clase' => 'bg-uch-bg text-uch-muted', 'punto' => 'bg-uch-muted', 'texto' => 'Cancelado'],
        ];
    @endphp

    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <x-flash-message />

        <!-- Banner institucional -->
        <section class="relative overflow-hidden rounded-card bg-gradient-to-r from-uch-navy via-uch-blue to-uch-blue p-6 text-white shadow-[0_8px_28px_rgba(27,54,104,0.18)]">
            <div class="pointer-events-none absolute inset-y-0 right-0 flex w-44 items-stretch opacity-90">
                <div class="h-full w-16 translate-x-4 skew-x-[-24deg] bg-uch-cyan"></div>
                <div class="h-full w-12 translate-x-2 skew-x-[-24deg] bg-uch-coral"></div>
                <div class="h-full w-16 translate-x-6 skew-x-[-24deg] bg-uch-navy-dark"></div>
            </div>

            <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-label-md font-bold uppercase tracking-wider text-white">
                        <span class="h-1.5 w-1.5 rounded-full bg-uch-cyan animate-pulse"></span>
                        Panel administrativo · Campus Los Olivos
                    </span>
                    <h1 class="mt-3 font-rubik text-headline-lg-mobile lg:text-headline-lg font-bold tracking-tight text-white">
                        Resumen del comedor
                    </h1>
                    <p class="mt-1 flex items-center gap-2 text-body-md font-medium text-white/80">
                        <span class="material-symbols-outlined text-[18px] text-uch-cyan">schedule</span>
                        Turno Almuerzo · {{ $fechaTurno }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="/admin/menus" class="inline-flex items-center gap-1.5 rounded-lg bg-uch-surface px-4 py-2.5 text-label-lg font-bold text-uch-navy shadow-sm transition-colors hover:bg-uch-bg">
                        <span class="material-symbols-outlined text-[18px]">restaurant_menu</span>
                        Configurar menús
                    </a>
                    <a href="/admin/inventario" class="relative inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-4 py-2.5 text-label-lg font-bold text-white transition-colors hover:bg-white/20">
                        <span class="material-symbols-outlined text-[18px]">notifications_active</span>
                        Alertas
                        @if ($alertasStock > 0)
                            <span class="ml-1 rounded-full bg-uch-coral px-1.5 py-0.5 text-label-md font-bold text-white">{{ $alertasStock }}</span>
                        @endif
                    </a>
                    <a href="/admin/pedidos/crear" class="inline-flex items-center gap-1.5 rounded-lg bg-uch-cyan px-4 py-2.5 text-label-lg font-bold text-uch-navy-dark shadow-md transition-colors hover:bg-uch-blue hover:text-white">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        + Nuevo pedido
                    </a>
                </div>
            </div>
        </section>

        <!-- KPIs -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $tarjetas = [
                    [
                        'etiqueta' => 'Ventas de hoy',
                        'valor' => 'S/ '.number_format((float) $ventasHoy, 2),
                        'icono' => 'payments',
                        'barra' => 'bg-uch-cyan',
                        'iconoColor' => 'bg-uch-teal/15 text-[#008779]',
                        'meta' => '+'.$pedidosHoy.' pedido'.($pedidosHoy === 1 ? '' : 's').' hoy',
                        'metaIcono' => 'trending_up',
                        'metaColor' => 'text-uch-blue',
                    ],
                    [
                        'etiqueta' => 'Pedidos de hoy',
                        'valor' => $pedidosHoy,
                        'icono' => 'receipt_long',
                        'barra' => 'bg-uch-blue',
                        'iconoColor' => 'bg-uch-blue/10 text-uch-blue',
                        'meta' => 'Registrados en el turno actual',
                        'metaIcono' => 'check_circle',
                        'metaColor' => 'text-uch-muted',
                    ],
                    [
                        'etiqueta' => 'En preparación',
                        'valor' => $enPreparacion,
                        'icono' => 'soup_kitchen',
                        'barra' => 'bg-uch-coral',
                        'iconoColor' => 'bg-uch-coral/10 text-uch-coral',
                        'meta' => 'Ver en cocina',
                        'metaIcono' => 'arrow_forward',
                        'metaColor' => 'text-uch-coral',
                        'enlace' => '/admin/pedidos',
                    ],
                    [
                        'etiqueta' => 'Productos activos',
                        'valor' => $totalProductos,
                        'icono' => 'lunch_dining',
                        'barra' => 'bg-uch-navy',
                        'iconoColor' => 'bg-uch-cyan/15 text-[#0087B8]',
                        'meta' => $menusActivos.($menusActivos === 1 ? ' menú vigente' : ' menús vigentes'),
                        'metaIcono' => 'restaurant',
                        'metaColor' => 'text-uch-muted',
                    ],
                ];
            @endphp

            @foreach ($tarjetas as $tarjeta)
                <div class="relative overflow-hidden rounded-card bg-uch-surface p-5 shadow-uch-card transition-shadow hover:shadow-uch-card-hover">
                    <div class="absolute inset-x-0 top-0 h-1 {{ $tarjeta['barra'] }}"></div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-label-md uppercase tracking-wider text-uch-muted">{{ $tarjeta['etiqueta'] }}</p>
                            <p class="mt-1 truncate font-rubik text-headline-md font-bold text-uch-navy">{{ $tarjeta['valor'] }}</p>
                        </div>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $tarjeta['iconoColor'] }}">
                            <span class="material-symbols-outlined text-[22px]">{{ $tarjeta['icono'] }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-1.5">
                        @if (! empty($tarjeta['enlace']))
                            <a href="{{ $tarjeta['enlace'] }}" class="inline-flex items-center gap-1 text-label-md font-bold {{ $tarjeta['metaColor'] }} hover:underline">
                                <span>{{ $tarjeta['meta'] }}</span>
                                <span class="material-symbols-outlined text-[15px]">{{ $tarjeta['metaIcono'] }}</span>
                            </a>
                            @if ($enPreparacion > 0)
                                <span class="h-2 w-2 rounded-full bg-uch-coral animate-ping"></span>
                            @endif
                        @else
                            <span class="inline-flex items-center gap-1 text-label-md font-bold {{ $tarjeta['metaColor'] }}">
                                <span class="material-symbols-outlined text-[15px]">{{ $tarjeta['metaIcono'] }}</span>
                                <span>{{ $tarjeta['meta'] }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>

        <!-- Contenido principal -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <div class="lg:col-span-8 space-y-5">
                <!-- Estado de menús del día -->
                <div class="rounded-card bg-uch-surface p-5 shadow-uch-card">
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded bg-uch-blue/10 text-uch-blue">
                                <span class="material-symbols-outlined text-[18px]">table_restaurant</span>
                            </div>
                            <div>
                                <h2 class="font-rubik text-headline-sm font-bold text-uch-navy">Estado de menús del día</h2>
                                <p class="text-body-sm text-uch-muted">Disponibilidad en tiempo real por modalidad.</p>
                            </div>
                            <span class="rounded-full bg-uch-bg px-2.5 py-0.5 text-label-md font-bold text-uch-muted">{{ $menusActivos }} {{ $menusActivos === 1 ? 'activo' : 'activos' }}</span>
                        </div>
                        <a href="/admin/menus" class="inline-flex items-center gap-1 text-label-md font-bold text-uch-blue hover:underline">
                            Gestionar <span class="material-symbols-outlined text-[16px]">tune</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @forelse ($menusDelDia as $menu)
                            @php $color = $coloresMenu[$menu['color']] ?? $coloresMenu['blue']; @endphp
                            <div class="relative overflow-hidden rounded-xl bg-uch-bg p-4 shadow-sm">
                                <div class="absolute inset-y-0 left-0 w-1.5 {{ $color['barra'] }}"></div>
                                <div class="pl-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <span class="text-[11px] font-bold uppercase tracking-wider {{ $color['etiqueta'] }}">{{ $menu['etiqueta'] }}</span>
                                            <h3 class="truncate font-rubik text-title-dish font-bold leading-tight text-uch-navy">{{ $menu['titulo'] }}</h3>
                                            <p class="truncate text-[11px] text-uch-muted">{{ $menu['plato'] }}</p>
                                        </div>
                                        <form method="POST" action="/admin/menus/{{ $menu['id'] }}/toggle">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    aria-label="{{ $menu['activo'] ? 'Desactivar' : 'Activar' }} {{ $menu['titulo'] }}"
                                                    class="flex h-4 w-8 items-center rounded-full px-0.5 transition-colors focus:outline-none {{ $menu['activo'] ? 'bg-uch-blue' : 'bg-uch-border' }}">
                                                <span class="h-3 w-3 rounded-full bg-white shadow-sm transition-transform {{ $menu['activo'] ? 'translate-x-4' : 'translate-x-0' }}"></span>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="mt-2 flex items-baseline justify-between gap-2">
                                        <span class="font-rubik text-price-tag font-bold text-uch-navy">{{ $menu['precioTexto'] }}</span>
                                        <span class="whitespace-nowrap text-[11px] font-bold text-uch-muted">{{ $menu['raciones'] }}/{{ $menu['total'] }} rac.</span>
                                    </div>

                                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-uch-border">
                                        <div class="h-full rounded-full {{ $color['progreso'] }}" style="width: {{ $menu['porcentaje'] }}%"></div>
                                    </div>
                                    <div class="mt-1 flex justify-between text-[10px] text-uch-muted">
                                        <span>{{ $menu['porcentaje'] }}% disponible</span>
                                        <span class="font-bold {{ $menu['raciones'] <= 5 ? 'text-uch-coral' : 'text-uch-navy' }}">{{ $menu['raciones'] }} disp.</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="col-span-full py-6 text-center text-body-sm text-uch-muted">No hay menús configurados.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Últimos pedidos -->
                <div class="overflow-hidden rounded-card bg-uch-surface shadow-uch-card">
                    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded bg-uch-blue/10 text-uch-blue">
                                <span class="material-symbols-outlined text-[18px]">receipt</span>
                            </div>
                            <div>
                                <h2 class="font-rubik text-headline-sm font-bold leading-tight text-uch-navy">Últimos pedidos</h2>
                                <p class="text-body-sm text-uch-muted">Operación en caja y mostrador en tiempo real.</p>
                            </div>
                        </div>
                        <a href="/admin/pedidos" class="inline-flex items-center gap-1 text-label-lg font-bold text-uch-blue hover:text-uch-navy">
                            Ver todos <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-uch-bg text-label-md uppercase tracking-wider text-uch-muted">
                                    <th class="rounded-l-lg px-5 py-2.5 font-semibold">Pedido</th>
                                    <th class="px-5 py-2.5 font-semibold">Detalle rápido</th>
                                    <th class="px-5 py-2.5 font-semibold">Estado</th>
                                    <th class="px-5 py-2.5 font-semibold">Total</th>
                                    <th class="rounded-r-lg px-5 py-2.5 text-right font-semibold">Acción rápida</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-uch-border">
                                @forelse ($ultimosPedidos as $pedido)
                                    @php $estado = $estadosPedido[$pedido->estado] ?? $estadosPedido['pendiente']; @endphp
                                    <tr class="transition-colors hover:bg-uch-bg">
                                        <td class="px-5 py-3.5 align-middle">
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-rubik text-[16px] font-bold text-uch-navy">#{{ str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT) }}</span>
                                                    <span class="rounded bg-uch-cyan/15 px-1.5 py-0.5 text-[11px] font-bold text-[#0060A9]">{{ $pedido->metodo_pago ?: 'Efectivo' }}</span>
                                                </div>
                                                <span class="text-body-sm text-uch-muted">{{ $pedido->created_at->format('h:i a') }} · {{ $pedido->tipo_cliente }}</span>
                                            </div>
                                        </td>
                                        <td class="max-w-xs px-5 py-3.5 align-middle">
                                            <p class="truncate text-label-lg font-semibold text-uch-text" title="{{ $pedido->detalle_rapido }}">{{ $pedido->detalle_rapido }}</p>
                                            <p class="text-body-sm text-uch-muted">Para consumir en salón</p>
                                        </td>
                                        <td class="px-5 py-3.5 align-middle">
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-label-md font-bold {{ $estado['clase'] }}">
                                                <span class="h-2 w-2 rounded-full {{ $estado['punto'] }} {{ $pedido->estado === 'pendiente' ? 'animate-ping' : '' }}"></span>
                                                {{ $estado['texto'] }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3.5 align-middle font-rubik text-price-tag font-bold text-uch-navy">
                                            S/ {{ number_format((float) $pedido->total, 2) }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right align-middle">
                                            @if ($pedido->estado === 'pendiente')
                                                <form method="POST" action="/admin/pedidos/{{ $pedido->id }}/estado">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="estado" value="en_preparacion">
                                                    <button type="submit" class="ml-auto inline-flex items-center gap-1 rounded-lg bg-uch-blue px-3 py-1.5 text-label-md font-bold text-white shadow-sm transition-colors hover:bg-uch-navy">
                                                        Preparar <span class="material-symbols-outlined text-[16px]">play_arrow</span>
                                                    </button>
                                                </form>
                                            @elseif ($pedido->estado === 'en_preparacion')
                                                <form method="POST" action="/admin/pedidos/{{ $pedido->id }}/estado">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="estado" value="listo">
                                                    <button type="submit" class="ml-auto inline-flex items-center gap-1 rounded-lg bg-uch-cyan px-3 py-1.5 text-label-md font-bold text-uch-navy-dark shadow-sm transition-colors hover:bg-uch-blue hover:text-white">
                                                        Marcar listo <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                                    </button>
                                                </form>
                                            @else
                                                <a href="/admin/pedidos" class="ml-auto inline-flex items-center gap-1 rounded-lg bg-uch-bg px-3 py-1.5 text-label-md font-bold text-uch-blue transition-colors hover:bg-uch-border">
                                                    <span class="material-symbols-outlined text-[16px]">visibility</span> Ver ticket
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-10 text-center text-body-sm text-uch-muted">Aún no hay pedidos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-5">
                <!-- Alertas de stock -->
                <div class="relative overflow-hidden rounded-card bg-uch-surface p-5 shadow-uch-card">
                    <div class="absolute inset-x-0 top-0 h-1 bg-uch-coral"></div>
                    <div class="flex items-center gap-3 pb-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-uch-coral/10 text-uch-coral">
                            <span class="material-symbols-outlined text-[20px]">warning</span>
                        </div>
                        <div class="min-w-0">
                            <h2 class="font-rubik text-[17px] font-bold leading-tight text-uch-navy">Alertas de stock</h2>
                            <p class="text-body-sm font-medium {{ $alertasStock > 0 ? 'text-uch-coral' : 'text-uch-muted' }}">
                                {{ $alertasStock }} ítem{{ $alertasStock === 1 ? '' : 's' }} bajo el mínimo
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @forelse ($alertasResumen as $alerta)
                            <div class="rounded-lg {{ $loop->first ? 'bg-uch-coral/10 p-3' : 'bg-uch-bg px-3 py-2' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-title-dish font-bold leading-tight text-uch-navy">{{ $alerta['nombre'] }}</p>
                                        <p class="truncate text-body-sm text-uch-muted">{{ $alerta['categoria'] }} · {{ $alerta['referencia'] }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-uch-coral px-2 py-0.5 text-label-md font-bold text-white">{{ $alerta['stock'] }} {{ $alerta['unidad'] }}</span>
                                </div>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="text-[11px] font-semibold {{ $alerta['critica'] ? 'text-uch-coral' : 'text-uch-muted' }}">Mínimo sugerido: {{ $alerta['minimo'] }} {{ $alerta['unidad'] }}</span>
                                    <a href="/admin/inventario" class="inline-flex items-center gap-1 rounded bg-uch-coral px-2 py-1 text-label-md font-bold text-white transition-colors hover:bg-uch-coral-dark">
                                        <span class="material-symbols-outlined text-[14px]">add</span> Reponer
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-body-sm text-uch-muted">Todo el inventario está en niveles normales.</p>
                        @endforelse
                    </div>

                    <div class="mt-4 border-t border-uch-border pt-3 text-center">
                        <a href="/admin/inventario" class="inline-flex items-center gap-1 text-label-md font-bold text-uch-blue hover:text-uch-navy">
                            Gestionar inventario <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>
                </div>

                <!-- Movimientos de inventario -->
                <div class="rounded-card bg-uch-surface p-5 shadow-uch-card">
                    <div class="flex items-center gap-3 pb-4">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-uch-blue/10 text-uch-blue">
                            <span class="material-symbols-outlined text-[18px]">history</span>
                        </div>
                        <div>
                            <h2 class="font-rubik text-[16px] font-bold leading-tight text-uch-navy">Movimientos de inventario</h2>
                            <p class="text-body-sm text-uch-muted">Kardex y registro diario</p>
                        </div>
                    </div>

                    <div class="relative space-y-4 pl-6">
                        <div class="absolute bottom-2 left-2.5 top-2 w-0.5 bg-uch-border"></div>

                        @forelse ($ultimosMovimientos as $movimiento)
                            @php
                                $esIngreso = $movimiento->operacion === 'ingreso';
                                $punto = $esIngreso ? 'bg-uch-cyan' : ($movimiento->operacion === 'venta' ? 'bg-uch-blue' : 'bg-uch-coral');
                                $signo = $esIngreso ? '+' : '-';
                            @endphp
                            <div class="relative flex flex-col gap-0.5">
                                <span class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full {{ $punto }} ring-4 ring-uch-surface"></span>
                                <div class="flex items-baseline justify-between gap-2">
                                    <span class="text-label-lg font-bold text-uch-navy">
                                        {{ $signo }}{{ $movimiento->cantidad }} {{ strtolower($movimiento->nombre_item) }}
                                    </span>
                                    <span class="whitespace-nowrap text-[11px] text-uch-muted">{{ $movimiento->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="truncate text-body-sm text-uch-muted">{{ $movimiento->motivo_categoria }} · {{ $movimiento->usuario->name ?? 'Sistema' }}</p>
                            </div>
                        @empty
                            <p class="text-body-sm text-uch-muted">Sin movimientos registrados.</p>
                        @endforelse
                    </div>

                    <div class="mt-4 border-t border-uch-border pt-3 text-center">
                        <a href="/admin/inventario#historial" class="inline-flex items-center gap-1 text-label-md font-bold text-uch-blue hover:text-uch-navy">
                            Ver historial completo <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
