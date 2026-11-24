<x-layouts.app>
    <x-empleado-nav />

    @php
        $estilosEstado = [
            'pendiente' => ['fondo' => '#FFF4E0', 'texto' => '#B45309', 'etiqueta' => 'Pendiente'],
            'en_preparacion' => ['fondo' => 'var(--hasp-blue-lt, #E3F2FD)', 'texto' => 'var(--hasp-blue, #1565C0)', 'etiqueta' => 'En preparación'],
            'listo' => ['fondo' => 'var(--hasp-blue-lt, #E3F2FD)', 'texto' => 'var(--hasp-blue, #1565C0)', 'etiqueta' => 'Listo en barra'],
            'entregado' => ['fondo' => 'var(--hasp-green-lt, #E8F5E9)', 'texto' => 'var(--hasp-green, #2E7D32)', 'etiqueta' => 'Entregado'],
            'cancelado' => ['fondo' => '#FDECEA', 'texto' => '#C62828', 'etiqueta' => 'Anulado'],
        ];

        $metodosPago = [
            'efectivo' => 'Efectivo',
            'yape_plin' => 'Yape/Plin',
            'tarjeta' => 'Tarjeta POS',
        ];

        $origenes = ['todos' => 'Todos los orígenes', 'web' => 'Solo web', 'caja' => 'Solo mostrador'];
        $metodosFiltro = ['todos' => 'Todos los métodos'] + $metodosPago;
        $estadosFiltro = ['todos' => 'Todos los estados', 'entregado' => 'Entregados', 'proceso' => 'En proceso', 'cancelado' => 'Anulados'];

        $fechaDesde = \Illuminate\Support\Carbon::parse($desdeHistorial)->locale('es');
        $fechaHasta = \Illuminate\Support\Carbon::parse($hastaHistorial)->locale('es');
        $unSoloDia = $desdeHistorial === $hastaHistorial;
        $esHoy = $unSoloDia && $fechaDesde->isToday();
        $textoRango = $unSoloDia
            ? ($esHoy ? 'Hoy, ' : '').$fechaDesde->isoFormat('dddd D [de] MMMM [de] YYYY')
            : 'Del '.$fechaDesde->format('d/m/Y').' al '.$fechaHasta->format('d/m/Y');

        $tarjetasMetodo = [
            ['etiqueta' => 'Efectivo', 'valor' => $arqueo['efectivo'], 'tickets' => $arqueo['efectivoTickets']],
            ['etiqueta' => 'Yape/Plin', 'valor' => $arqueo['billeteras'], 'tickets' => $arqueo['billeterasTickets']],
            ['etiqueta' => 'Tarjeta POS', 'valor' => $arqueo['tarjeta'], 'tickets' => $arqueo['tarjetaTickets']],
        ];
    @endphp

    <style>
        .hv-wrap { width: 100%; max-width: 1500px; margin: 0 auto; padding: 1.5rem 1.25rem 3rem; }
        .hv-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; }
        .hv-title { font-family: 'Rubik', sans-serif; font-size: 1.6rem; font-weight: 700; letter-spacing: -0.01em; color: var(--hasp-text, #1C1C1C); }
        .hv-sub { margin-top: 0.15rem; font-size: 0.875rem; color: var(--hasp-muted, #6E6E6E); }
        .hv-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .hv-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; border-radius: 8px; padding: 0.55rem 1rem; font-size: 0.875rem; font-weight: 600; font-family: inherit; cursor: pointer; border: 1px solid var(--hasp-border, #EBEBEB); background: #fff; color: var(--hasp-text, #1C1C1C); text-decoration: none; transition: border-color .15s, color .15s, background .15s; }
        .hv-btn:hover { border-color: var(--hasp-brand, #C8511B); color: var(--hasp-brand, #C8511B); }
        .hv-btn-brand { background: var(--hasp-brand, #C8511B); border-color: var(--hasp-brand, #C8511B); color: #fff; }
        .hv-btn-brand:hover { background: var(--hasp-brand-mid, #E8650A); border-color: var(--hasp-brand-mid, #E8650A); color: #fff; }
        .hv-icon { font-size: 19px; line-height: 1; }
        .hv-card { background: #fff; border: 1px solid var(--hasp-border, #EBEBEB); border-radius: var(--hasp-r, 12px); }

        .hv-filters { margin-top: 1.25rem; padding: 1.1rem; }
        .hv-chips { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 0.9rem; }
        .hv-chip { border: 1px solid var(--hasp-border, #EBEBEB); border-radius: 9999px; padding: 0.3rem 0.85rem; font-size: 0.8rem; font-weight: 600; color: var(--hasp-muted, #6E6E6E); text-decoration: none; }
        .hv-chip:hover { border-color: var(--hasp-brand, #C8511B); color: var(--hasp-brand, #C8511B); }
        .hv-chip.is-on { background: var(--hasp-brand-lt, #FDF0E8); border-color: var(--hasp-brand, #C8511B); color: var(--hasp-brand, #C8511B); }
        .hv-fields { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 0.75rem; align-items: end; }
        .hv-field label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--hasp-muted, #6E6E6E); margin-bottom: 0.3rem; }
        .hv-input { width: 100%; border: 1px solid var(--hasp-border, #EBEBEB); border-radius: 8px; padding: 0.5rem 0.7rem; font-size: 0.875rem; background: var(--hasp-bg, #F9F9F7); --tw-ring-color: var(--hasp-brand, #C8511B); }
        .hv-input:focus { background: #fff; border-color: var(--hasp-brand, #C8511B); }
        .hv-form-actions { display: flex; align-items: center; gap: 0.9rem; margin-top: 0.9rem; }
        .hv-form-actions a { font-size: 0.8rem; font-weight: 600; color: var(--hasp-muted, #6E6E6E); }

        .hv-cuadre { margin-top: 1.25rem; padding: 1.25rem 1.25rem 1.1rem; }
        .hv-cuadre-head { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 0.5rem; align-items: baseline; }
        .hv-h2 { font-family: 'Rubik', sans-serif; font-size: 1.1rem; font-weight: 700; color: var(--hasp-text, #1C1C1C); }
        .hv-muted { font-size: 0.8rem; color: var(--hasp-muted, #6E6E6E); }
        .hv-kpis { display: grid; grid-template-columns: minmax(220px, 1.2fr) repeat(3, minmax(150px, 1fr)); gap: 0.75rem; margin-top: 0.9rem; }
        @media (max-width: 900px) { .hv-kpis { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 520px) { .hv-kpis { grid-template-columns: 1fr; } }
        .hv-kpi { border: 1px solid var(--hasp-border, #EBEBEB); border-radius: 10px; padding: 0.85rem 1rem; }
        .hv-kpi.is-main { background: var(--hasp-brand-lt, #FDF0E8); border-color: transparent; }
        .hv-kpi-label { font-size: 0.78rem; font-weight: 600; color: var(--hasp-muted, #6E6E6E); }
        .hv-kpi-value { margin-top: 0.15rem; font-family: 'Rubik', sans-serif; font-size: 1.45rem; font-weight: 700; color: var(--hasp-text, #1C1C1C); }
        .hv-kpi.is-main .hv-kpi-value { color: var(--hasp-brand, #C8511B); font-size: 1.8rem; }
        .hv-notes { display: flex; flex-wrap: wrap; gap: 0.4rem 1.5rem; margin-top: 0.9rem; padding-top: 0.8rem; border-top: 1px dashed var(--hasp-border, #EBEBEB); font-size: 0.82rem; color: var(--hasp-muted, #6E6E6E); }
        .hv-notes b { color: var(--hasp-text, #1C1C1C); }
        .hv-days { width: 100%; margin-top: 0.9rem; border-collapse: collapse; font-size: 0.85rem; }
        .hv-days th { text-align: left; padding: 0.4rem 0.6rem; font-size: 0.75rem; color: var(--hasp-muted, #6E6E6E); border-bottom: 1px solid var(--hasp-border, #EBEBEB); }
        .hv-days td { padding: 0.45rem 0.6rem; border-bottom: 1px solid var(--hasp-border, #EBEBEB); }
        .hv-days .r { text-align: right; }

        .hv-table-card { margin-top: 1.25rem; overflow: hidden; }
        .hv-scroll { overflow-x: auto; }
        .hv-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .hv-table th { text-align: left; padding: 0.75rem 1rem; font-size: 0.78rem; font-weight: 700; color: var(--hasp-muted, #6E6E6E); background: var(--hasp-bg, #F9F9F7); white-space: nowrap; }
        .hv-table td { padding: 0.8rem 1rem; border-top: 1px solid var(--hasp-border, #EBEBEB); vertical-align: top; }
        .hv-table tr:hover td { background: var(--hasp-bg, #F9F9F7); }
        .hv-num { font-family: 'Rubik', sans-serif; font-weight: 700; white-space: nowrap; }
        .hv-sm { display: block; font-size: 0.75rem; font-weight: 400; color: var(--hasp-muted, #6E6E6E); }
        .hv-resumen { max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .hv-pill { display: inline-block; border-radius: 9999px; padding: 0.15rem 0.6rem; font-size: 0.72rem; font-weight: 700; white-space: nowrap; }
        .hv-link { font-weight: 600; color: var(--hasp-brand, #C8511B); text-decoration: none; white-space: nowrap; }
        .hv-link:hover { text-decoration: underline; }
        .hv-empty { padding: 2.5rem 1rem; text-align: center; color: var(--hasp-muted, #6E6E6E); }
        .hv-pager { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; background: var(--hasp-bg, #F9F9F7); border-top: 1px solid var(--hasp-border, #EBEBEB); font-size: 0.8rem; color: var(--hasp-muted, #6E6E6E); }
        .hv-printonly { display: none; }
        @media print {
            .hv-noprint { display: none !important; }
            .hv-printonly { display: block; margin-bottom: 0.75rem; font-size: 0.85rem; }
            .hv-cuadre { margin-top: 0; border: 0; }
        }
    </style>

    <div class="hv-wrap">
        <x-flash-message />

        <header class="hv-head hv-noprint">
            <div>
                <h1 class="hv-title">Historial y cuadre de caja</h1>
                <p class="hv-sub">{{ ucfirst($textoRango) }}</p>
            </div>
            <div class="hv-actions">
                <button type="button" onclick="window.print()" class="hv-btn">
                    <span class="material-symbols-outlined hv-icon">print</span>
                    Imprimir cuadre
                </button>
                <a href="{{ route('empleado.pedidos.index') }}" class="hv-btn">
                    <span class="material-symbols-outlined hv-icon">shopping_bag</span>
                    Pedidos en vivo
                </a>
            </div>
        </header>

        <section class="hv-card hv-filters hv-noprint" aria-label="Filtros del historial">
            <div class="hv-chips">
                @foreach ($atajos as $atajo)
                    <a href="{{ route('empleado.historial.index', ['desde' => $atajo['desde'], 'hasta' => $atajo['hasta']]) }}"
                       class="hv-chip {{ $atajo['activo'] ? 'is-on' : '' }}">{{ $atajo['etiqueta'] }}</a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('empleado.historial.index') }}">
                <div class="hv-fields">
                    <div class="hv-field">
                        <label for="desde-historial">Desde</label>
                        <input id="desde-historial" name="desde" type="date" value="{{ $desdeHistorial }}" class="hv-input">
                    </div>
                    <div class="hv-field">
                        <label for="hasta-historial">Hasta</label>
                        <input id="hasta-historial" name="hasta" type="date" value="{{ $hastaHistorial }}" class="hv-input">
                    </div>
                    <div class="hv-field">
                        <label for="buscar-historial">Buscar</label>
                        <input id="buscar-historial" name="buscar" type="search" value="{{ $buscar }}" placeholder="N.° ticket, cliente o código" class="hv-input">
                    </div>
                    <div class="hv-field">
                        <label for="origen-historial">Origen</label>
                        <select id="origen-historial" name="origen" class="hv-input">
                            @foreach ($origenes as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected($origenFiltro === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="hv-field">
                        <label for="metodo-historial">Método de pago</label>
                        <select id="metodo-historial" name="metodo_pago" class="hv-input">
                            @foreach ($metodosFiltro as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected($metodoFiltro === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="hv-field">
                        <label for="estado-historial">Estado</label>
                        <select id="estado-historial" name="estado" class="hv-input">
                            @foreach ($estadosFiltro as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected($estadoFiltro === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="hv-form-actions">
                    <button type="submit" class="hv-btn hv-btn-brand">
                        <span class="material-symbols-outlined hv-icon">filter_alt</span>
                        Aplicar filtros
                    </button>
                    <a href="{{ route('empleado.historial.index') }}">Volver a hoy</a>
                </div>
            </form>
        </section>

        <section class="hv-card hv-cuadre" aria-label="Cuadre de caja">
            <p class="hv-printonly">
                <strong>GRUPO HASP SERVICE · Cafetería UCH</strong><br>
                Cuadre de caja · {{ ucfirst($textoRango) }}<br>
                Cajero: {{ auth()->user()->name }} · Impreso: {{ now()->format('d/m/Y h:i a') }}
            </p>

            <div class="hv-cuadre-head">
                <h2 class="hv-h2">Cuadre de caja</h2>
                <span class="hv-muted">Incluye todo lo cobrado en las fechas elegidas, sin contar pedidos anulados.</span>
            </div>

            <div class="hv-kpis">
                <div class="hv-kpi is-main">
                    <p class="hv-kpi-label">{{ $esHoy ? 'Venta de hoy' : 'Total vendido' }}</p>
                    <p class="hv-kpi-value">S/ {{ number_format($arqueo['total'], 2) }}</p>
                    <p class="hv-muted">{{ $arqueo['tickets'] }} {{ $arqueo['tickets'] === 1 ? 'ticket' : 'tickets' }}</p>
                </div>
                @foreach ($tarjetasMetodo as $tarjeta)
                    <div class="hv-kpi">
                        <p class="hv-kpi-label">{{ $tarjeta['etiqueta'] }}</p>
                        <p class="hv-kpi-value">S/ {{ number_format($tarjeta['valor'], 2) }}</p>
                        <p class="hv-muted">{{ $tarjeta['tickets'] }} {{ $tarjeta['tickets'] === 1 ? 'ticket' : 'tickets' }}</p>
                    </div>
                @endforeach
            </div>

            <div class="hv-notes">
                <span>Efectivo que debe haber en caja: <b>S/ {{ number_format($arqueo['efectivo'], 2) }}</b></span>
                <span>Anulados (no incluidos): <b>{{ $arqueo['anulados'] }}</b> · S/ {{ number_format($arqueo['anuladosMonto'], 2) }}</span>
                <span>Cobrados y aún sin entregar: <b>{{ $arqueo['sinEntregar'] }}</b></span>
                @if ($arqueo['otros'] > 0)
                    <span>Otros métodos de pago: <b>S/ {{ number_format($arqueo['otros'], 2) }}</b></span>
                @endif
            </div>

            @if (! $unSoloDia && $porDia->isNotEmpty())
                <table class="hv-days">
                    <thead><tr><th>Día</th><th class="r">Tickets</th><th class="r">Total vendido</th></tr></thead>
                    <tbody>
                        @foreach ($porDia as $dia)
                            <tr>
                                <td>{{ ucfirst(\Illuminate\Support\Carbon::parse($dia['fecha'])->locale('es')->isoFormat('ddd D [de] MMM')) }}</td>
                                <td class="r">{{ $dia['tickets'] }}</td>
                                <td class="r">S/ {{ number_format($dia['total'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="hv-card hv-table-card hv-noprint" aria-label="Pedidos del periodo">
            <div class="hv-scroll">
                <table class="hv-table">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Cliente</th>
                            <th>Resumen</th>
                            <th>Pago</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Fecha y hora</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($historial as $pedido)
                            @php
                                $estilo = $estilosEstado[$pedido->estado] ?? $estilosEstado['pendiente'];
                                $nombreCliente = $pedido->cliente_nombre_caja ?: ($pedido->cliente->name ?? 'Mostrador');
                                $esCaja = $pedido->origen === \App\Models\Pedido::ORIGEN_CAJA;
                            @endphp
                            <tr>
                                <td class="hv-num">
                                    #{{ str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT) }}
                                    @if ($pedido->codigo_transaccion)
                                        <span class="hv-sm">{{ $pedido->codigo_transaccion }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong style="font-weight: 600;">{{ $nombreCliente }}</strong>
                                    <span class="hv-sm">{{ $esCaja ? 'Mostrador' : 'Web' }}</span>
                                </td>
                                <td><div class="hv-resumen" title="{{ $pedido->resumen }}">{{ $pedido->resumen }}</div></td>
                                <td>{{ $metodosPago[$pedido->metodo_pago] ?? ($pedido->metodo_pago ?: 'Sin registro') }}</td>
                                <td class="hv-num">S/ {{ number_format((float) $pedido->total, 2) }}</td>
                                <td><span class="hv-pill" style="background: {{ $estilo['fondo'] }}; color: {{ $estilo['texto'] }};">{{ $estilo['etiqueta'] }}</span></td>
                                <td style="white-space: nowrap; color: var(--hasp-muted, #6E6E6E);">{{ $pedido->created_at->format('d/m/Y h:i a') }}</td>
                                <td style="text-align: right;"><a href="{{ route('empleado.pedidos.ticket', $pedido) }}" class="hv-link">Ver ticket</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="hv-empty">No hay pedidos en estas fechas con los filtros elegidos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="hv-pager">
                <p>Mostrando <strong>{{ $historial->count() }}</strong> de <strong>{{ $historial->total() }}</strong> pedidos</p>
                {{ $historial->links() }}
            </div>
        </section>
    </div>
</x-layouts.app>
