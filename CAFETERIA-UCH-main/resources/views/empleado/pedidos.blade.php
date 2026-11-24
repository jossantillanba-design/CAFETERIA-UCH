<x-layouts.app>
    <x-empleado-nav />

    @php
        $estilosEstado = [
            'pendiente' => ['fondo' => '#FFF4E0', 'texto' => '#B45309', 'etiqueta' => 'Pendiente'],
            'en_preparacion' => ['fondo' => 'var(--hasp-blue-lt, #E3F2FD)', 'texto' => 'var(--hasp-blue, #1565C0)', 'etiqueta' => 'En preparación'],
            'listo' => ['fondo' => 'var(--hasp-green-lt, #E8F5E9)', 'texto' => 'var(--hasp-green, #2E7D32)', 'etiqueta' => 'Listo en barra'],
        ];

        $filtros = [
            ['clave' => 'todos', 'texto' => 'Todos'],
            ['clave' => 'pendiente', 'texto' => 'Pendientes'],
            ['clave' => 'en_preparacion', 'texto' => 'En preparación'],
            ['clave' => 'listo', 'texto' => 'Listos'],
        ];

        $acciones = [
            'pendiente' => ['estado' => 'en_preparacion', 'texto' => 'Preparar orden', 'icono' => 'arrow_forward', 'fondo' => 'var(--hasp-brand, #C8511B)'],
            'en_preparacion' => ['estado' => 'listo', 'texto' => 'Marcar como listo', 'icono' => 'done_all', 'fondo' => 'var(--hasp-blue, #1565C0)'],
            'listo' => ['estado' => 'entregado', 'texto' => 'Confirmar entrega', 'icono' => 'inventory', 'fondo' => 'var(--hasp-green, #2E7D32)'],
        ];

        $metodosPago = [
            'efectivo' => 'Efectivo',
            'yape_plin' => 'Yape/Plin',
            'tarjeta' => 'Tarjeta POS',
        ];

        $totalActivos = (int) $conteos->sum();
    @endphp

    <style>
        .pv-wrap { width: 100%; max-width: 1500px; margin: 0 auto; padding: 1.5rem 1.25rem 3rem; }
        .pv-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; }
        .pv-title { font-family: 'Rubik', sans-serif; font-size: 1.6rem; font-weight: 700; letter-spacing: -0.01em; color: var(--hasp-text, #1C1C1C); }
        .pv-sub { margin-top: 0.15rem; font-size: 0.875rem; color: var(--hasp-muted, #6E6E6E); }
        .pv-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .pv-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; border-radius: 8px; padding: 0.55rem 1rem; font-size: 0.875rem; font-weight: 600; font-family: inherit; cursor: pointer; border: 1px solid transparent; text-decoration: none; transition: background .15s, border-color .15s, color .15s; }
        .pv-btn-outline { background: #fff; border-color: var(--hasp-border, #EBEBEB); color: var(--hasp-text, #1C1C1C); }
        .pv-btn-outline:hover { border-color: var(--hasp-brand, #C8511B); color: var(--hasp-brand, #C8511B); }
        .pv-btn-brand { background: var(--hasp-brand, #C8511B); color: #fff; }
        .pv-btn-brand:hover { background: var(--hasp-brand-mid, #E8650A); }
        .pv-icon { font-size: 19px; line-height: 1; }

        .pv-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 0.35rem; margin-top: 1.25rem; border-bottom: 1px solid var(--hasp-border, #EBEBEB); }
        .pv-filter { display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.6rem 0.9rem; font-size: 0.875rem; font-weight: 600; color: var(--hasp-muted, #6E6E6E); text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; }
        .pv-filter:hover { color: var(--hasp-text, #1C1C1C); }
        .pv-filter.is-on { color: var(--hasp-brand, #C8511B); border-bottom-color: var(--hasp-brand, #C8511B); }
        .pv-count { background: var(--hasp-bg, #F9F9F7); color: var(--hasp-muted, #6E6E6E); border-radius: 9999px; padding: 0 0.5rem; font-size: 0.72rem; line-height: 1.25rem; }
        .pv-filter.is-on .pv-count { background: var(--hasp-brand-lt, #FDF0E8); color: var(--hasp-brand, #C8511B); }
        .pv-search-chip { margin-left: auto; display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--hasp-muted, #6E6E6E); padding-bottom: 0.4rem; }
        .pv-search-chip a { color: var(--hasp-brand, #C8511B); font-weight: 600; }

        .pv-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.1rem; margin-top: 1.25rem; align-items: start; }
        .pv-card { background: #fff; border: 1px solid var(--hasp-border, #EBEBEB); border-radius: var(--hasp-r, 12px); display: flex; flex-direction: column; overflow: hidden; }
        .pv-card-top { padding: 1rem 1.1rem 0.85rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
        .pv-num { font-family: 'Rubik', sans-serif; font-size: 1.1rem; font-weight: 700; color: var(--hasp-text, #1C1C1C); }
        .pv-client { margin-top: 0.1rem; font-size: 0.85rem; color: var(--hasp-text, #1C1C1C); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .pv-meta { margin-top: 0.3rem; display: flex; flex-wrap: wrap; align-items: center; gap: 0.35rem 0.75rem; font-size: 0.75rem; color: var(--hasp-muted, #6E6E6E); }
        .pv-meta span { display: inline-flex; align-items: center; gap: 0.25rem; }
        .pv-meta .material-symbols-outlined { font-size: 15px; }
        .pv-pill { flex-shrink: 0; border-radius: 9999px; padding: 0.2rem 0.65rem; font-size: 0.72rem; font-weight: 700; white-space: nowrap; }

        .pv-items { padding: 0 1.1rem 0.9rem; }
        .pv-item { padding: 0.6rem 0; border-top: 1px solid var(--hasp-border, #EBEBEB); }
        .pv-item-row { display: flex; align-items: baseline; justify-content: space-between; gap: 0.75rem; font-size: 0.875rem; color: var(--hasp-text, #1C1C1C); }
        .pv-item-row strong { font-weight: 600; }
        .pv-item-price { font-size: 0.82rem; color: var(--hasp-muted, #6E6E6E); white-space: nowrap; }
        .pv-comp { margin-top: 0.2rem; padding-left: 1.1rem; font-size: 0.78rem; line-height: 1.5; color: var(--hasp-muted, #6E6E6E); list-style: none; }
        .pv-comp b { font-weight: 600; color: var(--hasp-text, #1C1C1C); }

        .pv-foot { margin-top: auto; background: var(--hasp-bg, #F9F9F7); border-top: 1px solid var(--hasp-border, #EBEBEB); padding: 0.9rem 1.1rem 1rem; }
        .pv-total { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; }
        .pv-total-label { font-size: 0.8rem; color: var(--hasp-muted, #6E6E6E); }
        .pv-total-amount { font-family: 'Rubik', sans-serif; font-size: 1.3rem; font-weight: 700; color: var(--hasp-text, #1C1C1C); }
        .pv-pay { margin-top: 0.15rem; display: flex; flex-wrap: wrap; gap: 0.2rem 0.9rem; font-size: 0.78rem; color: var(--hasp-muted, #6E6E6E); }
        .pv-pay span { display: inline-flex; align-items: center; gap: 0.25rem; }
        .pv-pay .material-symbols-outlined { font-size: 15px; }
        .pv-buttons { margin-top: 0.8rem; display: flex; gap: 0.5rem; }
        .pv-buttons form { display: flex; }
        .pv-buttons form.pv-main { flex: 1 1 auto; }
        .pv-main button { width: 100%; color: #fff; border: 0; padding: 0.65rem 0.8rem; }
        .pv-main button:hover { filter: brightness(1.08); }
        .pv-cancel { background: #fff; border-color: var(--hasp-border, #EBEBEB); color: var(--hasp-muted, #6E6E6E); padding: 0.65rem 0.9rem; }
        .pv-cancel:hover { border-color: var(--hasp-brand, #C8511B); color: var(--hasp-brand, #C8511B); }
        .pv-ticket { margin-top: 0.7rem; display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.8rem; font-weight: 600; color: var(--hasp-muted, #6E6E6E); text-decoration: none; }
        .pv-ticket:hover { color: var(--hasp-brand, #C8511B); }
        .pv-empty { grid-column: 1 / -1; background: #fff; border: 1px dashed var(--hasp-border, #EBEBEB); border-radius: var(--hasp-r, 12px); padding: 3rem 1rem; text-align: center; }
        .pv-empty p:first-of-type { margin-top: 0.4rem; font-weight: 600; color: var(--hasp-text, #1C1C1C); }
        .pv-empty p:last-of-type { margin-top: 0.2rem; font-size: 0.85rem; color: var(--hasp-muted, #6E6E6E); }
    </style>

    <div class="pv-wrap">
        <x-flash-message />

        <header class="pv-head">
            <div>
                <h1 class="pv-title">Pedidos en vivo</h1>
                <p class="pv-sub">Cocina y barra · {{ $fechaTurno }}</p>
            </div>
            <div class="pv-actions">
                <button type="button" onclick="location.reload()" class="pv-btn pv-btn-outline">
                    <span class="material-symbols-outlined pv-icon">refresh</span>
                    Actualizar
                </button>
                <a href="{{ route('empleado.tomar-orden.create') }}" class="pv-btn pv-btn-brand">
                    <span class="material-symbols-outlined pv-icon">add</span>
                    Tomar orden
                </a>
            </div>
        </header>

        <nav class="pv-filters" aria-label="Filtrar pedidos por estado">
            @foreach ($filtros as $filtro)
                @php
                    $filtroActivo = $estadoFiltro === $filtro['clave'];
                    $urlFiltro = route('empleado.pedidos.index', ['estado' => $filtro['clave']] + ($buscar !== '' ? ['buscar' => $buscar] : []));
                    $cantidadFiltro = $filtro['clave'] === 'todos' ? $totalActivos : (int) ($conteos[$filtro['clave']] ?? 0);
                @endphp
                <a href="{{ $urlFiltro }}" class="pv-filter {{ $filtroActivo ? 'is-on' : '' }}" @if ($filtroActivo) aria-current="page" @endif>
                    {{ $filtro['texto'] }}
                    <span class="pv-count">{{ $cantidadFiltro }}</span>
                </a>
            @endforeach

            @if ($buscar !== '')
                <span class="pv-search-chip">
                    Resultados para "{{ $buscar }}"
                    <a href="{{ route('empleado.pedidos.index') }}">Limpiar</a>
                </span>
            @endif
        </nav>

        <section class="pv-grid" aria-label="Pedidos activos">
            @forelse ($pedidos as $pedido)
                @php
                    $estilo = $estilosEstado[$pedido->estado] ?? $estilosEstado['pendiente'];
                    $accion = $acciones[$pedido->estado] ?? null;
                    $nombreCliente = $pedido->cliente_nombre_caja ?: ($pedido->cliente->name ?? 'Cliente en mostrador');
                    $esCaja = $pedido->origen === \App\Models\Pedido::ORIGEN_CAJA;
                @endphp
                <article class="pv-card">
                    <div class="pv-card-top">
                        <div style="min-width: 0;">
                            <h2 class="pv-num">Pedido #{{ str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT) }}</h2>
                            <p class="pv-client">{{ $nombreCliente }}</p>
                            <div class="pv-meta">
                                @if ($esCaja)
                                    <span>
                                        <span class="material-symbols-outlined">storefront</span>
                                        Mostrador · Caja
                                    </span>
                                @else
                                    <span>
                                        <span class="material-symbols-outlined">language</span>
                                        Web · Pagado
                                    </span>
                                @endif
                                <span>
                                    <span class="material-symbols-outlined">schedule</span>
                                    {{ $pedido->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                        <span class="pv-pill" style="background: {{ $estilo['fondo'] }}; color: {{ $estilo['texto'] }};">{{ $estilo['etiqueta'] }}</span>
                    </div>

                    <div class="pv-items">
                        @foreach ($pedido->detalles as $detalle)
                            <div class="pv-item">
                                @if ($detalle->tipo_item === 'menu')
                                    <div class="pv-item-row">
                                        <strong>{{ $detalle->cantidad }}x {{ $detalle->menu->titulo ?? 'Menú del día' }}</strong>
                                        <span class="pv-item-price">S/ {{ number_format((float) ($detalle->precio_unitario * $detalle->cantidad), 2) }}</span>
                                    </div>
                                    <ul class="pv-comp">
                                        @foreach (['Entrada' => $detalle->entrada?->nombre, 'Fondo' => $detalle->fondo?->nombre, 'Refresco' => $detalle->refresco?->nombre] as $etiqueta => $nombre)
                                            @if ($nombre)
                                                <li><b>{{ $etiqueta }}:</b> {{ $nombre }}</li>
                                            @endif
                                        @endforeach
                                        @if (! $detalle->entrada && ! $detalle->fondo && ! $detalle->refresco && $detalle->detalle_menu)
                                            <li>{{ $detalle->detalle_menu }}</li>
                                        @endif
                                    </ul>
                                @else
                                    <div class="pv-item-row">
                                        <strong>{{ $detalle->cantidad }}x {{ $detalle->producto->nombre ?? 'Producto' }}</strong>
                                        <span class="pv-item-price">S/ {{ number_format((float) ($detalle->precio_unitario * $detalle->cantidad), 2) }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="pv-foot">
                        <div class="pv-total">
                            <span class="pv-total-label">Total</span>
                            <span class="pv-total-amount">S/ {{ number_format((float) $pedido->total, 2) }}</span>
                        </div>
                        <div class="pv-pay">
                            <span title="{{ $pedido->metodo_pago }}">
                                <span class="material-symbols-outlined">payments</span>
                                {{ $metodosPago[$pedido->metodo_pago] ?? ($pedido->metodo_pago ?: 'Pagado') }}
                            </span>
                            @if ($esCaja && $pedido->vuelto !== null)
                                <span>
                                    <span class="material-symbols-outlined">replay</span>
                                    Vuelto S/ {{ number_format((float) $pedido->vuelto, 2) }}
                                </span>
                            @endif
                        </div>

                        <div class="pv-buttons">
                            @if ($accion)
                                <form method="POST" action="{{ route('empleado.pedidos.estado', $pedido) }}" class="pv-main">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="estado" value="{{ $accion['estado'] }}">
                                    <button type="submit" class="pv-btn" style="background: {{ $accion['fondo'] }};">
                                        <span class="material-symbols-outlined pv-icon">{{ $accion['icono'] }}</span>
                                        {{ $accion['texto'] }}
                                    </button>
                                </form>
                            @endif

                            @if ($pedido->estado === 'pendiente')
                                <form method="POST" action="{{ route('empleado.pedidos.estado', $pedido) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="estado" value="cancelado">
                                    <button type="submit" class="pv-btn pv-cancel">Anular</button>
                                </form>
                            @endif
                        </div>

                        <a href="{{ route('empleado.pedidos.ticket', $pedido) }}" class="pv-ticket">
                            <span class="material-symbols-outlined" style="font-size: 16px;">receipt_long</span>
                            Ver / imprimir ticket
                        </a>
                    </div>
                </article>
            @empty
                <div class="pv-empty">
                    <span class="material-symbols-outlined" style="font-size: 34px; color: var(--hasp-subtle, #ABABAB);">room_service</span>
                    <p>No hay pedidos activos con este filtro.</p>
                    <p>Los nuevos tickets aparecerán aquí al actualizar la página.</p>
                </div>
            @endforelse
        </section>
    </div>
</x-layouts.app>
