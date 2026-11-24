<x-layouts.app>
    @php
        $estilosEstado = [
            'pendiente'      => ['etiqueta' => 'Pendiente',       'grupo' => 'curso',     'color' => '#B45309', 'bg' => '#FEF3C7', 'barra' => '#F59E0B', 'paso' => 1, 'chip' => 'En barra'],
            'en_preparacion' => ['etiqueta' => 'En preparación',  'grupo' => 'curso',     'color' => '#1565C0', 'bg' => '#E3F2FD', 'barra' => '#F59E0B', 'paso' => 2, 'chip' => 'En cocina'],
            'listo'          => ['etiqueta' => 'Listo',           'grupo' => 'curso',     'color' => '#2E7D32', 'bg' => '#E8F5E9', 'barra' => '#2E7D32', 'paso' => 3, 'chip' => 'Listo'],
            'entregado'      => ['etiqueta' => 'Entregado',       'grupo' => 'entregado', 'color' => '#2E7D32', 'bg' => '#E8F5E9', 'barra' => '#2E7D32', 'paso' => 4, 'chip' => 'Finalizado'],
            'cancelado'      => ['etiqueta' => 'Cancelado',       'grupo' => 'cancelado', 'color' => '#B91C1C', 'bg' => '#FEE2E2', 'barra' => '#EF4444', 'paso' => 0, 'chip' => 'Cancelado'],
        ];

        $pasos = [
            ['nombre' => 'Recibido',       'icono' => '✓'],
            ['nombre' => 'Preparando',     'icono' => '🍳'],
            ['nombre' => 'Listo p/ recoger','icono' => '📦'],
            ['nombre' => 'Entregado',      'icono' => '✔✔'],
        ];

        $conteo = [
            'todos'     => $pedidos->count(),
            'curso'     => $pedidos->whereIn('estado', ['pendiente', 'en_preparacion', 'listo'])->count(),
            'entregado' => $pedidos->where('estado', 'entregado')->count(),
            'cancelado' => $pedidos->where('estado', 'cancelado')->count(),
        ];

        $ultimo = $pedidos->first();
    @endphp

    <style>
        .mp-wrap { max-width: 1100px; margin: 0 auto; padding: 2rem 1.25rem 4rem; font-family: 'DM Sans', system-ui, sans-serif; }
        /* Confirmación */
        .mp-confirm { background:#fff; border:1px solid var(--hasp-border,#EBEBEB); border-radius:14px; padding:1.25rem 1.5rem; display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:2rem; border-left:4px solid var(--hasp-green,#2E7D32); box-shadow:0 1px 4px rgba(0,0,0,.06); }
        .mp-confirm-icon { width:44px;height:44px;border-radius:50%;background:#E8F5E9;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0; }
        .mp-confirm-title { font-size:1rem;font-weight:700;color:var(--hasp-text,#1C1C1C); }
        .mp-confirm-sub { font-size:0.78rem;color:var(--hasp-muted,#6E6E6E);margin-top:0.15rem; }
        .mp-confirm-code { color:var(--hasp-blue,#1565C0);font-weight:600; }
        .mp-confirm-eta { display:inline-flex;align-items:center;gap:0.4rem;background:var(--hasp-bg,#F9F9F7);border:1px solid var(--hasp-border,#EBEBEB);border-radius:999px;padding:0.35rem 0.9rem;font-size:0.78rem;color:var(--hasp-text,#1C1C1C); }

        /* Header */
        .mp-header { display:flex;flex-direction:column;gap:1.25rem;margin-bottom:1.75rem; }
        @media(min-width:700px){ .mp-header{flex-direction:row;align-items:flex-end;justify-content:space-between;} }
        .mp-eyebrow { font-size:0.68rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--hasp-brand,#C8511B);display:flex;align-items:center;gap:0.4rem;margin-bottom:0.3rem; }
        .mp-eyebrow-dot { width:7px;height:7px;border-radius:50%;background:var(--hasp-brand,#C8511B);animation:hasp-blink 2s infinite; }
        .mp-title { font-size:1.6rem;font-weight:700;letter-spacing:-0.02em;color:var(--hasp-text,#1C1C1C); }
        .mp-sub { font-size:0.875rem;color:var(--hasp-muted,#6E6E6E);margin-top:0.3rem; }

        /* Filter tabs */
        .mp-tabs { display:flex;align-items:center;gap:0.35rem;flex-wrap:wrap; }
        .mp-tab { border:1px solid var(--hasp-border,#EBEBEB);background:#fff;border-radius:999px;padding:0.4rem 1rem;font-size:0.8rem;font-weight:500;cursor:pointer;color:var(--hasp-muted,#6E6E6E);font-family:inherit;transition:all .15s;display:flex;align-items:center;gap:0.4rem; }
        .mp-tab:hover { border-color:var(--hasp-brand,#C8511B);color:var(--hasp-brand,#C8511B); }
        .mp-tab.mp-active { border-color:var(--hasp-brand,#C8511B);background:var(--hasp-brand-lt,#FDF0E8);color:var(--hasp-brand,#C8511B); }
        .mp-tab-count { font-size:0.65rem;font-weight:700;padding:0.1rem 0.4rem;border-radius:999px;background:rgba(0,0,0,.07); }

        /* Grid */
        .mp-grid { display:grid;grid-template-columns:1fr;gap:1.25rem; }
        @media(min-width:900px){ .mp-grid{grid-template-columns:1fr 1fr;} }

        /* Card */
        .mp-card { background:#fff;border:1px solid var(--hasp-border,#EBEBEB);border-radius:16px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);display:flex;flex-direction:column; }
        .mp-card-top-bar { height:4px;width:100%; }
        .mp-card-body { padding:1.25rem;flex:1;display:flex;flex-direction:column;gap:1.1rem; }

        /* Card header */
        .mp-card-head { display:flex;align-items:flex-start;justify-content:space-between;gap:0.75rem; }
        .mp-order-num { font-size:1rem;font-weight:700;color:var(--hasp-text,#1C1C1C);letter-spacing:-0.01em; }
        .mp-order-chip { font-size:0.65rem;font-weight:600;padding:0.15rem 0.55rem;border-radius:999px;background:var(--hasp-bg,#F9F9F7);color:var(--hasp-muted,#6E6E6E);margin-left:0.4rem; }
        .mp-order-date { font-size:0.72rem;color:var(--hasp-muted,#6E6E6E);margin-top:0.2rem; }
        .mp-status-badge { display:inline-flex;align-items:center;gap:0.4rem;border-radius:999px;padding:0.3rem 0.8rem;font-size:0.72rem;font-weight:700;flex-shrink:0; }
        .mp-status-dot { width:7px;height:7px;border-radius:50%;background:currentColor;animation:hasp-blink 2s infinite; }

        /* Progress */
        .mp-progress { background:var(--hasp-bg,#F9F9F7);border-radius:12px;padding:1rem; }
        .mp-steps { display:grid;grid-template-columns:repeat(4,1fr);position:relative;gap:0; }
        .mp-steps-line { position:absolute;top:14px;left:12.5%;right:12.5%;height:3px;background:var(--hasp-border,#EBEBEB);z-index:0; }
        .mp-steps-fill { position:absolute;top:14px;left:12.5%;height:3px;background:var(--hasp-green,#2E7D32);z-index:1; }
        .mp-step { display:flex;flex-direction:column;align-items:center;text-align:center;position:relative;z-index:2; }
        .mp-step-circle { width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;border:2px solid var(--hasp-border,#EBEBEB);background:#fff;color:var(--hasp-muted,#6E6E6E); }
        .mp-step-circle.done { background:var(--hasp-green,#2E7D32);border-color:var(--hasp-green,#2E7D32);color:#fff; }
        .mp-step-circle.active { background:var(--hasp-brand,#C8511B);border-color:var(--hasp-brand,#C8511B);color:#fff; }
        .mp-step-label { font-size:0.6rem;font-weight:600;margin-top:0.4rem;color:var(--hasp-muted,#6E6E6E);line-height:1.2; }
        .mp-step-label.active { color:var(--hasp-brand,#C8511B); }
        .mp-step-label.done { color:var(--hasp-green,#2E7D32); }

        /* Items */
        .mp-items-label { font-size:0.62rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--hasp-muted,#6E6E6E);margin-bottom:0.5rem; }
        .mp-item { display:flex;align-items:flex-start;justify-content:space-between;gap:0.75rem;padding:0.6rem 0;border-bottom:1px solid var(--hasp-border,#EBEBEB); }
        .mp-item:last-child { border-bottom:none; }
        .mp-item-icon { width:36px;height:36px;border-radius:8px;background:var(--hasp-bg,#F9F9F7);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0; }
        .mp-item-name { font-size:0.82rem;font-weight:600;color:var(--hasp-text,#1C1C1C); }
        .mp-item-det { font-size:0.68rem;color:var(--hasp-muted,#6E6E6E);margin-top:0.1rem; }
        .mp-item-price { font-size:0.85rem;font-weight:700;flex-shrink:0;color:var(--hasp-text,#1C1C1C); }

        /* Card footer */
        .mp-card-footer { padding:1rem 1.25rem;border-top:1px solid var(--hasp-border,#EBEBEB);display:flex;flex-direction:column;gap:0.75rem; }
        .mp-total-row { display:flex;align-items:center;justify-content:space-between; }
        .mp-pago-badge { display:inline-flex;align-items:center;gap:0.4rem;border-radius:6px;padding:0.3rem 0.7rem;font-size:0.72rem;font-weight:600; }
        .mp-total-label { font-size:0.72rem;color:var(--hasp-muted,#6E6E6E); }
        .mp-total-val { font-size:1.1rem;font-weight:700;color:var(--hasp-text,#1C1C1C); }
        .mp-actions { display:grid;grid-template-columns:1fr 1fr;gap:0.6rem; }
        .mp-btn-detail { border:1px solid var(--hasp-border,#EBEBEB);background:#fff;border-radius:8px;padding:0.6rem;font-size:0.8rem;font-weight:600;cursor:pointer;color:var(--hasp-muted,#6E6E6E);font-family:inherit;transition:all .14s; }
        .mp-btn-detail:hover { border-color:var(--hasp-brand,#C8511B);color:var(--hasp-brand,#C8511B); }
        .mp-btn-reorder { background:var(--hasp-brand,#C8511B);color:#fff;border:none;border-radius:8px;padding:0.6rem;font-size:0.8rem;font-weight:600;cursor:pointer;font-family:inherit;transition:opacity .14s; }
        .mp-btn-reorder:hover { opacity:0.88; }
        .mp-detail-panel { background:var(--hasp-bg,#F9F9F7);border-radius:8px;padding:0.75rem;font-size:0.78rem;color:var(--hasp-muted,#6E6E6E); }

        /* Empty */
        .mp-empty { background:#fff;border:1px solid var(--hasp-border,#EBEBEB);border-radius:16px;padding:3rem 1.5rem;text-align:center; }
        .mp-empty-icon { font-size:2.5rem;margin-bottom:0.75rem; }
        .mp-empty-title { font-size:1.1rem;font-weight:700;color:var(--hasp-text,#1C1C1C);margin-bottom:0.3rem; }
        .mp-empty-desc { font-size:0.85rem;color:var(--hasp-muted,#6E6E6E);margin-bottom:1.25rem; }
        .mp-empty-btn { display:inline-flex;align-items:center;gap:0.4rem;background:var(--hasp-brand,#C8511B);color:#fff;border-radius:8px;padding:0.65rem 1.4rem;font-size:0.875rem;font-weight:600;text-decoration:none; }

        /* Support */
        .mp-support { background:#fff;border:1px solid var(--hasp-border,#EBEBEB);border-radius:14px;padding:1.1rem 1.4rem;display:flex;flex-direction:column;align-items:center;justify-content:space-between;gap:1rem;margin-top:2rem;text-align:center; }
        @media(min-width:600px){ .mp-support{flex-direction:row;text-align:left;} }
        .mp-support-icon { width:40px;height:40px;border-radius:50%;background:var(--hasp-brand-lt,#FDF0E8);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0; }
        .mp-support-title { font-size:0.875rem;font-weight:700;color:var(--hasp-text,#1C1C1C); }
        .mp-support-desc { font-size:0.78rem;color:var(--hasp-muted,#6E6E6E);margin-top:0.15rem; }
        .mp-support-btn { display:inline-flex;align-items:center;gap:0.4rem;border:1px solid var(--hasp-border,#EBEBEB);background:#fff;border-radius:8px;padding:0.5rem 1rem;font-size:0.8rem;font-weight:600;color:var(--hasp-brand,#C8511B);text-decoration:none;white-space:nowrap;flex-shrink:0; }
        .mp-support-btn:hover { background:var(--hasp-brand-lt,#FDF0E8); }

        @media(max-width:640px){ .mp-wrap{padding:1.25rem 1rem 3rem;} }
    </style>

    <div class="mp-wrap">

        {{-- Confirmación del último pedido --}}
        @if (session('mensaje'))
            @php
                $metodoUltimo = strtolower($ultimo->metodo_pago ?? '');
                $iconoPagoUltimo = str_contains($metodoUltimo, 'yape') || str_contains($metodoUltimo, 'plin') ? '📱'
                    : (str_contains($metodoUltimo, 'tarjeta') ? '💳' : '💵');
            @endphp
            <div class="mp-confirm">
                <div style="display:flex;align-items:center;gap:0.85rem;">
                    <div class="mp-confirm-icon">✅</div>
                    <div>
                        <div class="mp-confirm-title">{{ session('mensaje') }}</div>
                        <div class="mp-confirm-sub">
                            @if ($ultimo && $ultimo->codigo_transaccion)
                                Código: <span class="mp-confirm-code">{{ $ultimo->codigo_transaccion }}</span> ·
                            @endif
                            @if ($ultimo && $ultimo->metodo_pago)
                                {{ $iconoPagoUltimo }} {{ $ultimo->metodo_pago }}
                            @endif
                        </div>
                    </div>
                </div>
                <span class="mp-confirm-eta">⏱ Estimado: <strong>10 – 15 min</strong></span>
            </div>
        @endif

        <div x-data="{ filtro: 'todos' }">

            {{-- Encabezado --}}
            <div class="mp-header">
                <div>
                    <div class="mp-eyebrow"><span class="mp-eyebrow-dot"></span> Seguimiento en tiempo real</div>
                    <h1 class="mp-title">Mis pedidos</h1>
                    <p class="mp-sub">Revisa el estado de tu orden actual y el historial de tus consumos.</p>
                </div>
                <div class="mp-tabs">
                    <button class="mp-tab" :class="{ 'mp-active': filtro === 'todos' }" @click="filtro='todos'">
                        Todos <span class="mp-tab-count">{{ $conteo['todos'] }}</span>
                    </button>
                    <button class="mp-tab" :class="{ 'mp-active': filtro === 'curso' }" @click="filtro='curso'">
                        En curso <span class="mp-tab-count">{{ $conteo['curso'] }}</span>
                    </button>
                    <button class="mp-tab" :class="{ 'mp-active': filtro === 'entregado' }" @click="filtro='entregado'">
                        Entregados <span class="mp-tab-count">{{ $conteo['entregado'] }}</span>
                    </button>
                    @if ($conteo['cancelado'] > 0)
                        <button class="mp-tab" :class="{ 'mp-active': filtro === 'cancelado' }" @click="filtro='cancelado'">
                            Cancelados <span class="mp-tab-count">{{ $conteo['cancelado'] }}</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Grid de tarjetas --}}
            <div class="mp-grid">
                @forelse ($pedidos as $indice => $pedido)
                    @php
                        $estilo = $estilosEstado[$pedido->estado] ?? [
                            'etiqueta' => ucfirst(str_replace('_', ' ', $pedido->estado ?? 'Registrado')),
                            'grupo' => 'curso', 'color' => '#1565C0', 'bg' => '#E3F2FD',
                            'barra' => '#1565C0', 'paso' => 1, 'chip' => 'Registrado',
                        ];

                        $itemsPedido = $pedido->detalles->map(function ($d) {
                            $esMenu = $d->tipo_item === 'menu';
                            return [
                                'id'       => $esMenu ? 'menu-'.$d->menu_id.'-'.$d->entrada_id.'-'.$d->fondo_id.'-'.$d->refresco_id : $d->producto_id,
                                'tipo'     => $d->tipo_item,
                                'menu_id'  => $d->menu_id,
                                'opciones' => $esMenu ? ['entrada'=>$d->entrada_id,'fondo'=>$d->fondo_id,'refresco'=>$d->refresco_id] : null,
                                'nombre'   => $esMenu ? ($d->menu->titulo ?? 'Menú del día') : ($d->producto->nombre ?? 'Producto'),
                                'detalle'  => $d->detalle_menu ?? '',
                                'precio'   => (float) $d->precio_unitario,
                                'cantidad' => $d->cantidad,
                            ];
                        })->values();

                        $metodo = strtolower($pedido->metodo_pago ?? '');
                        $iconoPago = str_contains($metodo,'yape')||str_contains($metodo,'plin') ? '📱'
                            : (str_contains($metodo,'tarjeta') ? '💳' : '💵');
                        $fillPct = max(0, ($estilo['paso'] - 1) * 33.33);
                    @endphp

                    <article x-data="{ detalle: false }"
                             x-show="filtro === 'todos' || $el.dataset.grupo === filtro"
                             x-cloak
                             data-grupo="{{ $estilo['grupo'] }}"
                             class="mp-card">

                        <div class="mp-card-top-bar" style="background:{{ $estilo['barra'] }}"></div>

                        <div class="mp-card-body">
                            {{-- Cabecera --}}
                            <div class="mp-card-head">
                                <div>
                                    <div>
                                        <span class="mp-order-num">Pedido #{{ str_pad($pedido->id, 3, '0', STR_PAD_LEFT) }}</span>
                                        <span class="mp-order-chip">{{ $estilo['chip'] }}</span>
                                    </div>
                                    <div class="mp-order-date">📅 {{ $pedido->created_at->format('d/m/Y · h:i a') }}</div>
                                </div>
                                <span class="mp-status-badge" style="background:{{ $estilo['bg'] }};color:{{ $estilo['color'] }}">
                                    @if ($estilo['paso'] > 0 && $estilo['paso'] < 4)
                                        <span class="mp-status-dot"></span>
                                    @endif
                                    {{ $estilo['etiqueta'] }}
                                </span>
                            </div>

                            {{-- Progreso --}}
                            @if ($estilo['paso'] > 0)
                                <div class="mp-progress">
                                    <div class="mp-steps">
                                        <div class="mp-steps-line"></div>
                                        <div class="mp-steps-fill" style="width:{{ $fillPct }}%"></div>
                                        @foreach ($pasos as $i => $paso)
                                            @php
                                                $done   = $estilo['paso'] === 4 || $i < $estilo['paso'] - 1;
                                                $active = $i === $estilo['paso'] - 1 && $estilo['paso'] < 4;
                                            @endphp
                                            <div class="mp-step">
                                                <div class="mp-step-circle {{ $done ? 'done' : ($active ? 'active' : '') }}">
                                                    {{ $done ? '✓' : $paso['icono'] }}
                                                </div>
                                                <div class="mp-step-label {{ $done ? 'done' : ($active ? 'active' : '') }}">
                                                    {{ $paso['nombre'] }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div style="padding:0.75rem;border-radius:8px;background:#FEE2E2;color:#B91C1C;font-size:0.82rem;">
                                    Este pedido fue cancelado. Si necesitas ayuda, acércate a ventanilla.
                                </div>
                            @endif

                            {{-- Artículos --}}
                            <div>
                                <div class="mp-items-label">Artículos en esta orden</div>
                                @foreach ($pedido->detalles as $det)
                                    @php
                                        $esMenu = $det->tipo_item === 'menu';
                                        $nombreArt = $esMenu ? ($det->menu->titulo ?? 'Menú del día') : ($det->producto->nombre ?? 'Producto');
                                        $emoji = $esMenu ? '🍽️' : '☕';
                                    @endphp
                                    <div class="mp-item">
                                        <div style="display:flex;align-items:flex-start;gap:0.65rem;min-width:0;">
                                            <div class="mp-item-icon">{{ $emoji }}</div>
                                            <div style="min-width:0;">
                                                <div class="mp-item-name">{{ $det->cantidad }}× {{ $nombreArt }}</div>
                                                @if ($esMenu && $det->detalle_menu)
                                                    <div class="mp-item-det">{{ $det->detalle_menu }}</div>
                                                @endif
                                                <div class="mp-item-det">S/ {{ number_format($det->precio_unitario, 2) }} c/u</div>
                                            </div>
                                        </div>
                                        <span class="mp-item-price">S/ {{ number_format($det->precio_unitario * $det->cantidad, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Pie --}}
                        <div class="mp-card-footer">
                            <div class="mp-total-row">
                                <span class="mp-pago-badge" style="background:{{ $pedido->pagado ? '#E8F5E9' : '#FEF3C7' }};color:{{ $pedido->pagado ? '#2E7D32' : '#B45309' }}">
                                    {{ $pedido->pagado ? '✓ Pagado · '.($pedido->metodo_pago ?? '') : '⏳ Pendiente de cobro' }}
                                </span>
                                <div style="text-align:right;">
                                    <div class="mp-total-label">Total</div>
                                    <div class="mp-total-val">S/ {{ number_format($pedido->total, 2) }}</div>
                                </div>
                            </div>

                            <div x-show="detalle" x-cloak class="mp-detail-panel">
                                <div>{{ $iconoPago }} {{ $pedido->metodo_pago ?? 'Método no registrado' }}</div>
                                @if ($pedido->codigo_transaccion)
                                    <div>Código: <strong style="color:var(--hasp-text)">{{ $pedido->codigo_transaccion }}</strong></div>
                                @endif
                                <div>Estado: <strong style="color:var(--hasp-text)">{{ $estilo['etiqueta'] }}</strong></div>
                            </div>

                            <div class="mp-actions">
                                <button type="button" class="mp-btn-detail" @click="detalle = !detalle"
                                        x-text="detalle ? 'Ocultar detalle' : 'Ver detalle'"></button>
                                <button type="button" class="mp-btn-reorder"
                                        @click="volverAPedir(@js($itemsPedido))">🔁 Volver a pedir</button>
                            </div>
                        </div>
                    </article>

                @empty
                    <div class="mp-empty" style="grid-column:1/-1;">
                        <div class="mp-empty-icon">🧾</div>
                        <div class="mp-empty-title">Todavía no tienes pedidos</div>
                        <div class="mp-empty-desc">Cuando hagas tu primer pedido, podrás seguirlo desde aquí.</div>
                        <a href="/menu" class="mp-empty-btn">Ir al menú →</a>
                    </div>
                @endforelse
            </div>

            {{-- Mensajes sin resultados por filtro --}}
            <p x-show="filtro === 'curso' && {{ $conteo['curso'] }} === 0" x-cloak
               style="text-align:center;color:var(--hasp-muted);padding:2rem 1rem;font-size:0.875rem;">
                No tienes pedidos en curso en este momento.
            </p>
            <p x-show="filtro === 'entregado' && {{ $conteo['entregado'] }} === 0" x-cloak
               style="text-align:center;color:var(--hasp-muted);padding:2rem 1rem;font-size:0.875rem;">
                Todavía no tienes pedidos entregados.
            </p>

            {{-- Soporte --}}
            <div class="mp-support">
                <div style="display:flex;align-items:center;gap:0.85rem;">
                    <div class="mp-support-icon">💬</div>
                    <div>
                        <div class="mp-support-title">¿Tienes dudas o necesitas modificar tu pedido?</div>
                        <div class="mp-support-desc">Acércate a ventanilla del Comedor Universitario · Pabellón A.</div>
                    </div>
                </div>
                <a href="/menu" class="mp-support-btn">🍽️ Volver al menú</a>
            </div>
        </div>
    </div>

    <script>
        function volverAPedir(items) {
            const actuales = (() => {
                try { return JSON.parse(localStorage.getItem('carrito') || '[]'); }
                catch { return []; }
            })();
            items.forEach(item => {
                const ex = actuales.find(p => p.id === item.id);
                if (ex) ex.cantidad += item.cantidad;
                else actuales.push({ ...item });
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
