<x-layouts.app>
    <x-admin-nav />

    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <x-flash-message />

        @php
            $estilosEstado = [
                'pendiente' => ['barra' => 'bg-uch-amber', 'etiqueta' => 'bg-uch-amber/15 text-[#B45309]', 'punto' => 'bg-uch-amber', 'texto' => 'Pendiente'],
                'en_preparacion' => ['barra' => 'bg-uch-cyan', 'etiqueta' => 'bg-uch-cyan/15 text-[#0060A9]', 'punto' => 'bg-uch-cyan', 'texto' => 'En preparación'],
                'listo' => ['barra' => 'bg-uch-teal', 'etiqueta' => 'bg-uch-teal/15 text-[#008779]', 'punto' => 'bg-uch-teal', 'texto' => 'Listo en barra'],
            ];

            $filtros = [
                ['clave' => 'todos', 'texto' => 'Todos activos', 'punto' => null],
                ['clave' => 'pendiente', 'texto' => 'Pendientes', 'punto' => 'bg-uch-amber'],
                ['clave' => 'en_preparacion', 'texto' => 'En preparación', 'punto' => 'bg-uch-cyan'],
                ['clave' => 'listo', 'texto' => 'Listos para recoger', 'punto' => 'bg-uch-teal'],
            ];

            $acciones = [
                'pendiente' => ['estado' => 'en_preparacion', 'texto' => 'Preparar orden', 'icono' => 'arrow_forward', 'clase' => 'bg-uch-blue text-white hover:bg-uch-navy'],
                'en_preparacion' => ['estado' => 'listo', 'texto' => 'Marcar como listo', 'icono' => 'done_all', 'clase' => 'bg-uch-cyan text-uch-navy-dark hover:bg-uch-blue hover:text-white'],
                'listo' => ['estado' => 'entregado', 'texto' => 'Confirmar entrega', 'icono' => 'inventory', 'clase' => 'bg-uch-teal text-white hover:bg-[#008779]'],
            ];
        @endphp

        <!-- CABECERA Y FILTROS RÁPIDOS -->
        <section class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-uch-cyan animate-pulse"></span>
                    <span class="text-label-md font-bold uppercase tracking-widest text-uch-blue">
                        Cocina y barra · Turno almuerzo
                    </span>
                </div>
                <button type="button" onclick="location.reload()" title="{{ $fechaTurno }}"
                        class="inline-flex items-center gap-1.5 rounded-full bg-uch-surface px-3 py-1 text-label-md text-uch-muted shadow-sm transition-colors hover:text-uch-blue">
                    <span class="material-symbols-outlined text-[16px] text-uch-blue">refresh</span>
                    Actualizar
                </button>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="font-rubik text-headline-lg-mobile lg:text-headline-lg tracking-tight text-uch-navy">Pedidos en vivo</h1>
                <a href="/admin/pedidos/crear"
                   class="inline-flex shrink-0 items-center gap-2 rounded-[10px] bg-uch-coral px-4 py-2.5 text-label-lg font-bold text-white shadow-sm transition-all hover:opacity-95 active:scale-95">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Nuevo ticket
                </a>
            </div>

            <div class="flex flex-wrap items-center gap-2 pt-1">
                @foreach ($filtros as $filtro)
                    @php
                        $filtroActivo = $estadoFiltro === $filtro['clave'];
                        $urlFiltro = '/admin/pedidos?estado='.$filtro['clave'].($buscar !== '' ? '&buscar='.rawurlencode($buscar) : '');
                    @endphp
                    <a href="{{ $urlFiltro }}"
                       class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-label-md font-bold shadow-sm transition-all {{ $filtroActivo ? 'bg-uch-navy text-white' : 'bg-uch-surface text-uch-muted hover:bg-uch-border' }}">
                        @if ($filtro['punto'])
                            <span class="h-2.5 w-2.5 rounded-full {{ $filtro['punto'] }}"></span>
                        @endif
                        <span>{{ $filtro['texto'] }}</span>
                        @if ($filtro['clave'] !== 'todos')
                            <span class="flex h-5 min-w-5 items-center justify-center rounded-full px-1 text-[11px] font-bold {{ $filtroActivo ? 'bg-white/20 text-white' : 'bg-uch-bg text-uch-navy' }}">
                                {{ (int) ($conteos[$filtro['clave']] ?? 0) }}
                            </span>
                        @endif
                    </a>
                @endforeach

                @if ($buscar !== '')
                    <span class="inline-flex items-center gap-1 rounded-full bg-uch-cyan/15 px-3 py-1.5 text-label-md font-semibold text-[#0060A9]">
                        <span class="material-symbols-outlined text-[14px]">search</span>
                        Resultados para "{{ $buscar }}"
                        <a href="/admin/pedidos" class="font-bold underline">Limpiar</a>
                    </span>
                @endif
            </div>
        </section>

        <!-- TARJETAS DE PEDIDOS EN CURSO -->
        <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3" aria-label="Pedidos activos">
            @forelse ($pedidos as $pedido)
                @php
                    $estilo = $estilosEstado[$pedido->estado] ?? $estilosEstado['pendiente'];
                    $accion = $acciones[$pedido->estado] ?? null;
                @endphp
                <article class="pedido-card relative flex flex-col overflow-hidden rounded-card bg-uch-surface shadow-uch-card">
                    <div class="h-1 w-full {{ $estilo['barra'] }}"></div>

                    <div class="flex grow flex-col gap-4 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="font-rubik text-headline-sm text-uch-navy">
                                    Pedido #{{ str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT) }}
                                </h2>
                                <p class="mt-0.5 flex items-center gap-1 text-body-sm text-uch-muted">
                                    <span class="material-symbols-outlined text-[15px]">person</span>
                                    <span class="truncate">{{ $pedido->cliente->name ?? 'Cliente en mostrador' }}</span>
                                    <span aria-hidden="true">·</span>
                                    <span class="whitespace-nowrap">{{ $pedido->created_at->diffForHumans() }}</span>
                                </p>
                            </div>
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $estilo['etiqueta'] }}">
                                <span class="h-2 w-2 rounded-full {{ $estilo['punto'] }} {{ $pedido->estado === 'pendiente' ? 'animate-pulse' : '' }}"></span>
                                {{ $estilo['texto'] }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-3">
                            @foreach ($pedido->detalles as $detalle)
                                @if ($detalle->tipo_item === 'menu')
                                    <div class="relative overflow-hidden rounded-lg bg-uch-bg p-3">
                                        <div class="absolute inset-y-0 left-0 w-1 {{ $estilo['barra'] }}"></div>
                                        <div class="pl-2">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="font-rubik text-body-md font-bold text-uch-navy">
                                                    {{ $detalle->cantidad }}x {{ $detalle->menu->titulo ?? 'Menú del día' }}
                                                </span>
                                                <span class="whitespace-nowrap font-rubik text-body-md text-uch-navy">
                                                    S/ {{ number_format((float) ($detalle->precio_unitario * $detalle->cantidad), 2) }}
                                                </span>
                                            </div>
                                            <ul class="mt-1.5 space-y-1 text-body-sm text-uch-muted">
                                                @foreach (['Entrada' => $detalle->entrada?->nombre, 'Fondo' => $detalle->fondo?->nombre, 'Refresco' => $detalle->refresco?->nombre] as $etiqueta => $nombre)
                                                    @if ($nombre)
                                                        <li class="flex items-start gap-1.5">
                                                            <span class="text-uch-blue text-[14px] leading-none">•</span>
                                                            <span><strong class="font-semibold text-uch-text">{{ $etiqueta }}:</strong> {{ $nombre }}</span>
                                                        </li>
                                                    @endif
                                                @endforeach
                                                @if (! $detalle->entrada && ! $detalle->fondo && ! $detalle->refresco && $detalle->detalle_menu)
                                                    <li>{{ $detalle->detalle_menu }}</li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between gap-2 px-1 text-body-sm text-uch-text">
                                        <span class="min-w-0 truncate">{{ $detalle->cantidad }}x {{ $detalle->producto->nombre ?? 'Producto' }}</span>
                                        <span class="shrink-0 text-label-lg font-semibold">
                                            S/ {{ number_format((float) ($detalle->precio_unitario * $detalle->cantidad), 2) }}
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-auto flex flex-col gap-3 bg-uch-bg p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-rubik text-headline-sm font-bold text-uch-navy">
                                Total: S/ {{ number_format((float) $pedido->total, 2) }}
                            </span>
                            @if ($pedido->pagado)
                                <span class="inline-flex max-w-[190px] items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-label-md font-semibold text-emerald-700" title="{{ $pedido->metodo_pago }}">
                                    <span class="material-symbols-outlined text-[14px]">check</span>
                                    <span class="truncate">Pagado · {{ $pedido->metodo_pago }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-uch-amber/15 px-2.5 py-0.5 text-label-md font-semibold text-[#B45309]">
                                    <span class="material-symbols-outlined text-[14px]">schedule</span>
                                    Pendiente de cobro
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row">
                            @if (! $pedido->pagado)
                                <form method="POST" action="/admin/pedidos/{{ $pedido->id }}/pagar" class="sm:flex-1">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit"
                                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-uch-surface px-3 py-2.5 text-label-md font-bold text-uch-navy shadow-sm transition-colors hover:bg-uch-border">
                                        <span class="material-symbols-outlined text-[16px]">payments</span>
                                        Cobrar en caja
                                    </button>
                                </form>
                            @endif
                            @if ($accion)
                                <form method="POST" action="/admin/pedidos/{{ $pedido->id }}/estado" class="{{ $pedido->pagado ? 'w-full' : 'sm:flex-1' }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="estado" value="{{ $accion['estado'] }}">
                                    <button type="submit"
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-label-lg font-bold transition-colors active:scale-[0.99] {{ $accion['clase'] }}">
                                        <span class="material-symbols-outlined text-[18px]">{{ $accion['icono'] }}</span>
                                        <span>{{ $accion['texto'] }}</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-card bg-uch-surface p-10 text-center shadow-uch-card">
                    <span class="material-symbols-outlined text-[34px] text-uch-muted">room_service</span>
                    <p class="mt-2 text-body-md font-semibold text-uch-navy">No hay pedidos activos con este filtro.</p>
                    <p class="mt-1 text-body-sm text-uch-muted">Los nuevos tickets aparecerán aquí automáticamente.</p>
                </div>
            @endforelse
        </section>

    </div>
</x-layouts.app>
