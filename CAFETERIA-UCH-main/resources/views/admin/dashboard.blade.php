<x-layouts.app>
    <x-admin-nav />

    @php
        $coloresMenu = [
            'blue' => [
                'barra' => 'bg-uch-blue',
                'etiqueta' => 'text-uch-blue',
                'raciones' => 'bg-uch-blue/10 text-uch-blue border-uch-blue/20',
            ],
            'coral' => [
                'barra' => 'bg-uch-coral',
                'etiqueta' => 'text-uch-coral',
                'raciones' => 'bg-uch-coral/10 text-uch-coral-dark border-uch-coral/20',
            ],
            'cyan' => [
                'barra' => 'bg-uch-cyan',
                'etiqueta' => 'text-[#0087B8]',
                'raciones' => 'bg-uch-cyan/15 text-[#0060A9] border-uch-cyan/20',
            ],
        ];

        $estadosPedido = [
            'pendiente' => ['clase' => 'bg-uch-coral/10 text-uch-coral-dark', 'punto' => 'bg-uch-coral', 'texto' => 'Pendiente'],
            'en_preparacion' => ['clase' => 'bg-uch-blue/10 text-uch-blue', 'punto' => 'bg-uch-blue', 'texto' => 'En preparación'],
            'listo' => ['clase' => 'bg-uch-cyan/15 text-[#0060A9]', 'punto' => 'bg-uch-cyan', 'texto' => 'Listo'],
            'entregado' => ['clase' => 'bg-uch-teal/15 text-[#008779]', 'punto' => 'bg-uch-teal', 'texto' => 'Entregado'],
            'cancelado' => ['clase' => 'bg-uch-bg text-uch-muted', 'punto' => 'bg-uch-muted', 'texto' => 'Cancelado'],
        ];
    @endphp

    <div class="mx-auto flex w-full max-w-[1600px] flex-col gap-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-flash-message />

        {{-- Cabecera --}}
        <header class="flex flex-col justify-between gap-4 pb-2 md:flex-row md:items-center">
            <div class="flex flex-col gap-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-uch-blue">
                    Panel administrativo · Campus Los Olivos · Turno almuerzo
                </span>
                <h1 class="font-rubik text-headline-lg-mobile font-bold tracking-tight text-uch-navy lg:text-headline-lg">
                    Resumen del comedor
                </h1>
                <p class="flex items-center gap-1.5 text-body-sm text-uch-muted">
                    <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                    {{ $fechaTurno }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="/admin/menus"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-uch-navy bg-uch-surface px-4 py-2 text-label-lg font-semibold text-uch-navy transition-colors hover:bg-uch-bg">
                    <span class="material-symbols-outlined text-[18px]">tune</span>
                    Configurar menús
                </a>
                <a href="/admin/pedidos/crear"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-uch-blue px-4 py-2 text-label-lg font-semibold text-white shadow-sm transition-colors hover:bg-uch-navy">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Nuevo pedido
                </a>
            </div>
        </header>

        {{-- Indicadores del día --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $tarjetas = [
                    [
                        'etiqueta' => 'Ventas de hoy',
                        'valor' => 'S/ '.number_format((float) $ventasHoy, 2),
                        'icono' => 'account_balance_wallet',
                        'iconoColor' => 'bg-uch-cyan/15 text-[#0087B8]',
                        'meta' => $pedidosHoy > 0
                            ? '+'.$pedidosHoy.' pedido'.($pedidosHoy === 1 ? '' : 's').' hoy'
                            : 'Sin movimientos registrados',
                    ],
                    [
                        'etiqueta' => 'Pedidos de hoy',
                        'valor' => $pedidosHoy,
                        'icono' => 'confirmation_number',
                        'iconoColor' => 'bg-uch-blue/10 text-uch-blue',
                        'meta' => 'Registrados en el turno actual',
                    ],
                    [
                        'etiqueta' => 'En preparación',
                        'valor' => $enPreparacion,
                        'icono' => 'schedule',
                        'iconoColor' => 'bg-uch-coral/10 text-uch-coral',
                        'meta' => 'Ver cocina',
                        'enlace' => '/admin/pedidos',
                    ],
                    [
                        'etiqueta' => 'Productos activos',
                        'valor' => $totalProductos,
                        'icono' => 'restaurant',
                        'iconoColor' => 'bg-uch-cyan/15 text-[#0087B8]',
                        'meta' => $menusActivos.($menusActivos === 1 ? ' menú disponible hoy' : ' menús disponibles hoy'),
                    ],
                ];
            @endphp

            @foreach ($tarjetas as $tarjeta)
                <div class="flex h-full flex-col justify-between rounded-xl border border-uch-border bg-uch-surface p-4 shadow-sm lg:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-label-md uppercase tracking-wider text-uch-muted">{{ $tarjeta['etiqueta'] }}</p>
                            <p class="mt-1 truncate font-rubik text-headline-md font-bold text-uch-navy">{{ $tarjeta['valor'] }}</p>
                        </div>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tarjeta['iconoColor'] }}">
                            <span class="material-symbols-outlined text-[20px]">{{ $tarjeta['icono'] }}</span>
                        </div>
                    </div>

                    <div class="mt-3 border-t border-uch-border pt-2 text-xs font-medium text-uch-muted">
                        @if (! empty($tarjeta['enlace']))
                            <a href="{{ $tarjeta['enlace'] }}" class="inline-flex items-center gap-1 font-bold text-uch-blue hover:underline">
                                <span>{{ $tarjeta['meta'] }}</span>
                                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                            </a>
                        @else
                            {{ $tarjeta['meta'] }}
                        @endif
                    </div>
                </div>
            @endforeach
        </section>

        {{-- Contenido principal --}}
        <section class="grid grid-cols-1 gap-5 lg:grid-cols-12">
            <div class="flex flex-col gap-5 lg:col-span-8">
                {{-- Últimos pedidos --}}
                <div class="rounded-card border border-uch-border bg-uch-surface shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-uch-border px-5 py-4">
                        <h2 class="font-rubik text-headline-sm font-bold text-uch-navy">Últimos pedidos</h2>
                        <a href="/admin/pedidos" class="inline-flex items-center gap-1 text-label-lg font-semibold text-uch-blue hover:underline">
                            Ver todos
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="border-b border-uch-border text-label-md uppercase tracking-wider text-uch-muted">
                                <tr>
                                    <th class="px-3 py-3 font-semibold">Pedido</th>
                                    <th class="px-3 py-3 font-semibold">Detalle</th>
                                    <th class="px-3 py-3 font-semibold">Estado</th>
                                    <th class="px-3 py-3 font-semibold">Total</th>
                                    <th class="px-3 py-3 text-right font-semibold">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-uch-border text-body-md">
                                @forelse ($ultimosPedidos as $pedido)
                                    @php $estado = $estadosPedido[$pedido->estado] ?? $estadosPedido['pendiente']; @endphp
                                    <tr class="transition-colors hover:bg-uch-bg">
                                        <td class="px-3 py-3">
                                            <div class="flex flex-col">
                                                <span class="font-rubik text-[15px] font-bold text-uch-navy">#{{ str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT) }}</span>
                                                <span class="truncate text-body-sm text-uch-muted">{{ $pedido->created_at->format('h:i a') }} · {{ $pedido->tipo_cliente }}</span>
                                            </div>
                                        </td>
                                        <td class="max-w-[260px] px-3 py-3">
                                            <p class="truncate text-label-lg font-semibold text-uch-text" title="{{ $pedido->detalle_rapido }}">{{ $pedido->detalle_rapido }}</p>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-label-md font-semibold {{ $estado['clase'] }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $estado['punto'] }}"></span>
                                                {{ $estado['texto'] }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-3 font-rubik text-price-tag font-bold text-uch-navy">
                                            S/ {{ number_format((float) $pedido->total, 2) }}
                                        </td>
                                        <td class="px-3 py-3 text-right">
                                            @if ($pedido->estado === 'pendiente')
                                                <form method="POST" action="/admin/pedidos/{{ $pedido->id }}/estado">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="estado" value="en_preparacion">
                                                    <button type="submit" class="ml-auto inline-flex items-center gap-1 rounded-lg bg-uch-blue px-3 py-1.5 text-label-md font-bold text-white transition-colors hover:bg-uch-navy">
                                                        Preparar <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                                    </button>
                                                </form>
                                            @elseif ($pedido->estado === 'en_preparacion')
                                                <form method="POST" action="/admin/pedidos/{{ $pedido->id }}/estado">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="estado" value="listo">
                                                    <button type="submit" class="ml-auto inline-flex items-center gap-1 rounded-lg bg-uch-cyan px-3 py-1.5 text-label-md font-bold text-uch-navy-dark transition-colors hover:bg-uch-blue hover:text-white">
                                                        Marcar listo <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                                    </button>
                                                </form>
                                            @else
                                                <a href="/admin/pedidos/{{ $pedido->id }}" class="ml-auto inline-flex items-center gap-1 rounded-lg border border-uch-border bg-uch-surface px-3 py-1.5 text-label-md font-bold text-uch-blue transition-colors hover:bg-uch-bg">
                                                    <span class="material-symbols-outlined text-[16px]">visibility</span> Ver ticket
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-10 text-center text-body-sm text-uch-muted">Aún no hay pedidos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Estado de menús del día --}}
                <div class="rounded-card border border-uch-border bg-uch-surface p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-uch-border pb-4">
                        <h2 class="font-rubik text-headline-sm font-bold text-uch-navy">Estado de menús del día</h2>
                        <a href="/admin/menus" class="inline-flex items-center gap-1 text-label-lg font-semibold text-uch-blue hover:underline">
                            Gestionar
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 gap-4 pt-4 md:grid-cols-3">
                        @forelse ($menusDelDia as $menu)
                            @php $color = $coloresMenu[$menu['color']] ?? $coloresMenu['blue']; @endphp
                            <div class="relative flex flex-col justify-between overflow-hidden rounded-xl border border-uch-border bg-uch-bg p-3.5 shadow-sm">
                                <div class="absolute inset-x-0 top-0 h-1 {{ $color['barra'] }}"></div>

                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <span class="text-[11px] font-bold uppercase tracking-wider {{ $color['etiqueta'] }}">{{ $menu['etiqueta'] }}</span>
                                        <h3 class="truncate font-rubik text-headline-sm text-[15px] font-bold leading-tight text-uch-navy">{{ $menu['titulo'] }}</h3>
                                        <p class="truncate text-body-sm text-uch-muted">{{ $menu['plato'] }}</p>
                                    </div>

                                    <form method="POST" action="/admin/menus/{{ $menu['id'] }}/toggle">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                aria-label="{{ $menu['activo'] ? 'Desactivar' : 'Activar' }} {{ $menu['titulo'] }}"
                                                class="flex h-4 w-8 shrink-0 items-center rounded-full px-0.5 transition-colors focus:outline-none {{ $menu['activo'] ? 'bg-uch-blue' : 'bg-uch-border' }}">
                                            <span class="h-3 w-3 rounded-full bg-white shadow-sm transition-transform {{ $menu['activo'] ? 'translate-x-4' : 'translate-x-0' }}"></span>
                                        </button>
                                    </form>
                                </div>

                                <div class="mt-3 font-rubik text-price-tag font-bold text-uch-navy">{{ $menu['precioTexto'] }}</div>

                                <div class="mt-3 rounded-lg border p-2 text-center text-label-md font-semibold {{ $color['raciones'] }}">
                                    {{ $menu['raciones'] }} raciones disponibles
                                </div>
                            </div>
                        @empty
                            <p class="col-span-full py-6 text-center text-body-sm text-uch-muted">No hay menús configurados.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-5 lg:col-span-4">
                {{-- Alertas de stock --}}
                <div class="relative overflow-hidden rounded-card border border-uch-border bg-uch-surface p-5 shadow-sm">
                    <div class="absolute inset-y-0 left-0 w-1.5 bg-uch-coral"></div>

                    <div class="flex items-center justify-between gap-2 pb-3">
                        <h2 class="font-rubik text-base font-bold text-uch-navy">Alertas de stock</h2>
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-uch-coral text-[11px] font-bold text-white">{{ $alertasStock }}</span>
                    </div>

                    <div class="flex flex-col gap-2">
                        @forelse ($alertasResumen as $alerta)
                            <div class="rounded-lg {{ $loop->first ? 'bg-uch-coral/10 p-3' : 'bg-uch-bg px-3 py-2' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-label-lg font-semibold leading-tight text-uch-navy">{{ $alerta['nombre'] }}</p>
                                        <p class="truncate text-body-sm text-uch-muted">{{ $alerta['categoria'] }} · {{ $alerta['referencia'] }}</p>
                                    </div>
                                    <span class="shrink-0 rounded bg-uch-coral px-2 py-0.5 text-label-md font-bold text-white">{{ $alerta['stock'] }} {{ $alerta['unidad'] }}</span>
                                </div>
                                <div class="mt-1 flex items-center justify-between gap-2">
                                    <span class="text-[11px] font-semibold {{ $alerta['critica'] ? 'text-uch-coral' : 'text-uch-muted' }}">Mínimo sugerido: {{ $alerta['minimo'] }} {{ $alerta['unidad'] }}</span>
                                    <a href="/admin/inventario" class="inline-flex items-center gap-1 rounded bg-uch-coral px-2 py-1 text-label-md font-semibold text-white transition-colors hover:bg-uch-coral-dark">
                                        Reponer
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-body-sm text-uch-muted">Todo el inventario está en niveles normales.</p>
                        @endforelse
                    </div>

                    <div class="mt-4 border-t border-uch-border pt-3 text-center">
                        <a href="/admin/inventario" class="inline-flex items-center gap-1 text-label-md font-bold text-uch-blue hover:underline">
                            Gestionar inventario
                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                        </a>
                    </div>
                </div>

                {{-- Movimientos recientes --}}
                <div class="rounded-card border border-uch-border bg-uch-surface p-5 shadow-sm">
                    <h2 class="pb-3 font-rubik text-base font-bold text-uch-navy">Movimientos recientes</h2>

                    <div class="divide-y divide-uch-border text-body-sm">
                        @forelse ($ultimosMovimientos as $movimiento)
                            @php
                                $esIngreso = $movimiento->operacion === 'ingreso';
                                $punto = $esIngreso ? 'bg-uch-teal' : ($movimiento->operacion === 'venta' ? 'bg-uch-blue' : 'bg-uch-coral');
                                $signo = $esIngreso ? '+' : '-';
                            @endphp
                            <div class="flex items-center gap-2 py-2.5 text-uch-muted">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $punto }}"></span>
                                <span class="min-w-0 truncate">
                                    {{ $signo }}{{ $movimiento->cantidad }} {{ strtolower($movimiento->nombre_item) }} ·
                                    {{ $movimiento->motivo_categoria }} ·
                                    {{ $movimiento->created_at->diffForHumans() }}
                                </span>
                            </div>
                        @empty
                            <p class="py-4 text-body-sm text-uch-muted">Sin movimientos registrados.</p>
                        @endforelse
                    </div>

                    <div class="mt-3 border-t border-uch-border pt-3 text-center">
                        <a href="/admin/movimientos" class="inline-flex items-center gap-1 text-label-md font-bold text-uch-blue hover:underline">
                            Ver historial completo
                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
