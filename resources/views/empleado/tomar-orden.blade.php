<x-layouts.app>
    <x-empleado-nav />

    @php
        $menusPayload = $menus->map(function ($menu): array {
            return [
                'id' => $menu->id,
                'titulo' => $menu->titulo,
                'tipo' => $menu->tipo_menu,
                'precioBase' => (float) $menu->precio_base,
                'precioVisible' => (float) $menu->precioVisible(),
                'precioVariable' => (bool) $menu->es_precio_variable,
                'agotado' => (bool) $menu->agotado,
                'opciones' => $menu->opciones
                    ->filter(fn ($opcion): bool => $opcion->habilitado && $opcion->componente !== null)
                    ->groupBy(fn ($opcion): string => $opcion->componente->tiempo)
                    ->map(fn ($grupo): array => $grupo->map(fn ($opcion): array => [
                        'id' => (int) $opcion->componente_id,
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

    <style>
        [x-cloak] { display: none !important; }

        .to-wrap { width: 100%; max-width: 1500px; margin: 0 auto; padding: 1.5rem 1.25rem 3rem; }
        .to-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1.25rem; }
        .to-title { font-family: 'Rubik', sans-serif; font-size: 1.6rem; font-weight: 700; letter-spacing: -0.01em; color: var(--hasp-text, #1C1C1C); }
        .to-sub { font-size: 0.875rem; color: var(--hasp-muted, #6E6E6E); margin-top: 0.15rem; }
        .to-grid { display: grid; grid-template-columns: minmax(0, 1fr) 410px; gap: 1.5rem; align-items: start; }
        @media (max-width: 1150px) { .to-grid { grid-template-columns: minmax(0, 1fr); } }

        .to-card { background: #fff; border: 1px solid var(--hasp-border, #EBEBEB); border-radius: var(--hasp-r, 12px); }
        .to-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; border-radius: 8px; padding: 0.55rem 1rem; font-size: 0.875rem; font-weight: 600; font-family: inherit; cursor: pointer; border: 1px solid transparent; text-decoration: none; transition: background .15s, border-color .15s, color .15s; }
        .to-btn-outline { background: #fff; border-color: var(--hasp-border, #EBEBEB); color: var(--hasp-text, #1C1C1C); }
        .to-btn-outline:hover { border-color: var(--hasp-brand, #C8511B); color: var(--hasp-brand, #C8511B); }
        .to-btn-brand { background: var(--hasp-brand, #C8511B); color: #fff; width: 100%; padding: 0.8rem 1rem; font-size: 0.95rem; }
        .to-btn-brand:hover { background: var(--hasp-brand-mid, #E8650A); }
        .to-btn-brand:disabled { background: var(--hasp-border, #EBEBEB); color: var(--hasp-subtle, #ABABAB); cursor: not-allowed; }
        .to-icon { font-size: 20px; line-height: 1; }

        /* Pestañas */
        .to-tabbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem; padding: 0.9rem 1.1rem 0; border-bottom: 1px solid var(--hasp-border, #EBEBEB); }
        .to-tabs { display: flex; gap: 0.25rem; }
        .to-tab { display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.6rem 0.9rem; border: 0; background: none; font-family: inherit; font-size: 0.9rem; font-weight: 600; color: var(--hasp-muted, #6E6E6E); cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; }
        .to-tab:hover { color: var(--hasp-text, #1C1C1C); }
        .to-tab.is-on { color: var(--hasp-brand, #C8511B); border-bottom-color: var(--hasp-brand, #C8511B); }
        .to-count { background: var(--hasp-bg, #F9F9F7); color: var(--hasp-muted, #6E6E6E); border-radius: 9999px; padding: 0 0.5rem; font-size: 0.72rem; line-height: 1.25rem; }
        .to-tab.is-on .to-count { background: var(--hasp-brand-lt, #FDF0E8); color: var(--hasp-brand, #C8511B); }
        .to-date { font-size: 0.78rem; color: var(--hasp-muted, #6E6E6E); padding-bottom: 0.6rem; }
        .to-body { padding: 1.1rem; }

        /* Selector de menú */
        .to-label { font-size: 0.78rem; font-weight: 600; color: var(--hasp-muted, #6E6E6E); margin-bottom: 0.5rem; }
        .to-menus { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 0.6rem; }
        .to-menu { text-align: left; background: #fff; border: 1px solid var(--hasp-border, #EBEBEB); border-radius: 10px; padding: 0.7rem 0.85rem; cursor: pointer; font-family: inherit; display: flex; flex-direction: column; gap: 0.15rem; }
        .to-menu:hover { border-color: var(--hasp-brand, #C8511B); }
        .to-menu.is-on { border-color: var(--hasp-brand, #C8511B); background: var(--hasp-brand-lt, #FDF0E8); box-shadow: 0 0 0 1px var(--hasp-brand, #C8511B); }
        .to-menu:disabled { opacity: .5; cursor: not-allowed; }
        .to-menu-name { font-size: 0.9rem; font-weight: 700; color: var(--hasp-text, #1C1C1C); }
        .to-menu-meta { font-size: 0.75rem; color: var(--hasp-muted, #6E6E6E); text-transform: capitalize; }
        .to-menu-price { font-size: 0.85rem; font-weight: 700; color: var(--hasp-brand, #C8511B); }

        /* Armado del menú */
        .to-build { margin-top: 1.1rem; border-top: 1px solid var(--hasp-border, #EBEBEB); padding-top: 1.1rem; }
        .to-steps { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
        @media (max-width: 780px) { .to-steps { grid-template-columns: minmax(0, 1fr); } }
        .to-step-title { display: flex; align-items: center; justify-content: space-between; font-size: 0.85rem; font-weight: 700; color: var(--hasp-text, #1C1C1C); margin-bottom: 0.5rem; }
        .to-step-ok { color: var(--hasp-green, #2E7D32); font-size: 18px; }
        .to-opt { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; text-align: left; background: #fff; border: 1px solid var(--hasp-border, #EBEBEB); border-radius: 8px; padding: 0.5rem 0.7rem; margin-bottom: 0.4rem; font-family: inherit; font-size: 0.85rem; color: var(--hasp-text, #1C1C1C); cursor: pointer; }
        .to-opt:hover { border-color: var(--hasp-brand, #C8511B); }
        .to-opt.is-on { border-color: var(--hasp-brand, #C8511B); background: var(--hasp-brand-lt, #FDF0E8); font-weight: 600; }
        .to-opt:disabled { opacity: .45; cursor: not-allowed; text-decoration: line-through; }
        .to-opt small { color: var(--hasp-muted, #6E6E6E); font-size: 0.72rem; white-space: nowrap; }
        .to-addbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; margin-top: 0.6rem; background: var(--hasp-bg, #F9F9F7); border-radius: 10px; padding: 0.75rem 0.9rem; }
        .to-addbar .to-btn-brand { width: auto; }

        /* Productos */
        .to-search { position: relative; margin-bottom: 0.9rem; max-width: 360px; }
        .to-search .material-symbols-outlined { position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%); font-size: 19px; color: var(--hasp-muted, #6E6E6E); pointer-events: none; }
        .to-search input { width: 100%; border: 1px solid var(--hasp-border, #EBEBEB); border-radius: 9999px; padding: 0.5rem 1rem 0.5rem 2.4rem; font-size: 0.875rem; background: var(--hasp-bg, #F9F9F7); --tw-ring-color: var(--hasp-brand, #C8511B); }
        .to-search input:focus { border-color: var(--hasp-brand, #C8511B); background: #fff; }
        .to-prods { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 0.65rem; }
        .to-prod { position: relative; text-align: left; background: #fff; border: 1px solid var(--hasp-border, #EBEBEB); border-radius: 10px; padding: 0.75rem 0.85rem; cursor: pointer; font-family: inherit; display: flex; flex-direction: column; gap: 0.2rem; }
        .to-prod:hover { border-color: var(--hasp-brand, #C8511B); background: var(--hasp-brand-lt, #FDF0E8); }
        .to-prod-name { font-size: 0.875rem; font-weight: 600; color: var(--hasp-text, #1C1C1C); padding-right: 1.4rem; }
        .to-prod-price { font-size: 0.9rem; font-weight: 700; color: var(--hasp-brand, #C8511B); }
        .to-prod-stock { font-size: 0.72rem; color: var(--hasp-muted, #6E6E6E); }
        .to-prod-qty { position: absolute; top: 0.55rem; right: 0.55rem; min-width: 1.35rem; height: 1.35rem; padding: 0 0.3rem; border-radius: 9999px; background: var(--hasp-brand, #C8511B); color: #fff; font-size: 0.72rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .to-empty { padding: 2rem 1rem; text-align: center; font-size: 0.875rem; color: var(--hasp-muted, #6E6E6E); }
        .to-toast { font-size: 0.8rem; font-weight: 600; color: var(--hasp-green, #2E7D32); background: var(--hasp-green-lt, #E8F5E9); border-radius: 9999px; padding: 0.15rem 0.7rem; }

        /* Ticket tipo recibo */
        .to-aside { position: sticky; top: 5rem; }
        @media (max-width: 1150px) { .to-aside { position: static; } }
        .to-ticket { position: relative; background: #fff; border: 1px solid var(--hasp-border, #EBEBEB); border-radius: 6px 6px 0 0; padding: 1.4rem 1.25rem 1.6rem; box-shadow: 0 8px 24px rgba(0,0,0,.08); font-family: ui-monospace, 'SFMono-Regular', Menlo, Consolas, 'Courier New', monospace; color: var(--hasp-text, #1C1C1C); }
        .to-ticket::after { content: ''; position: absolute; left: 0; right: 0; bottom: -8px; height: 8px; background: linear-gradient(-45deg, transparent 6px, #fff 0), linear-gradient(45deg, transparent 6px, #fff 0); background-size: 12px 12px; background-position: left bottom; filter: drop-shadow(0 2px 1px rgba(0,0,0,.06)); }
        .to-t-center { text-align: center; }
        .to-t-logo { width: 38px; height: 38px; margin: 0 auto 0.4rem; border-radius: 8px; background: var(--hasp-brand, #C8511B); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
        .to-t-company { font-family: 'Rubik', sans-serif; font-size: 1rem; font-weight: 700; letter-spacing: 0.02em; }
        .to-t-line { font-size: 0.72rem; color: var(--hasp-muted, #6E6E6E); line-height: 1.45; }
        .to-dash { border: 0; border-top: 1px dashed #BDBDBD; margin: 0.9rem 0; }
        .to-t-doc { text-align: center; font-size: 0.82rem; font-weight: 700; letter-spacing: 0.12em; }
        .to-kv { display: flex; justify-content: space-between; gap: 0.75rem; font-size: 0.78rem; line-height: 1.6; }
        .to-kv span:first-child { color: var(--hasp-muted, #6E6E6E); }
        .to-kv span:last-child { text-align: right; font-weight: 600; }
        .to-field { margin-top: 0.7rem; }
        .to-field label, .to-field legend { display: block; font-family: 'Open Sans', sans-serif; font-size: 0.75rem; font-weight: 600; color: var(--hasp-muted, #6E6E6E); margin-bottom: 0.3rem; }
        .to-input { width: 100%; border: 1px solid var(--hasp-border, #EBEBEB); border-radius: 8px; padding: 0.5rem 0.7rem; font-family: 'Open Sans', sans-serif; font-size: 0.875rem; background: #fff; --tw-ring-color: var(--hasp-brand, #C8511B); }
        .to-input:focus { border-color: var(--hasp-brand, #C8511B); }
        .to-seg { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.4rem; }
        .to-seg label, .to-pay label { margin: 0; display: flex; align-items: center; justify-content: center; gap: 0.35rem; border: 1px solid var(--hasp-border, #EBEBEB); border-radius: 8px; padding: 0.5rem 0.4rem; font-family: 'Open Sans', sans-serif; font-size: 0.8rem; font-weight: 600; color: var(--hasp-muted, #6E6E6E); cursor: pointer; text-align: center; }
        .to-seg label.is-on, .to-pay label.is-on { border-color: var(--hasp-brand, #C8511B); background: var(--hasp-brand-lt, #FDF0E8); color: var(--hasp-brand, #C8511B); }
        .to-pay { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.4rem; }
        .to-pay label { flex-direction: column; gap: 0.15rem; }
        .to-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; }

        .to-cols { display: grid; grid-template-columns: 5.2rem 1fr auto; gap: 0.5rem; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.06em; color: var(--hasp-muted, #6E6E6E); padding-bottom: 0.35rem; border-bottom: 1px solid var(--hasp-border, #EBEBEB); }
        .to-items { max-height: 280px; overflow-y: auto; }
        .to-row { display: grid; grid-template-columns: 5.2rem 1fr auto; gap: 0.5rem; align-items: start; padding: 0.65rem 0; border-bottom: 1px dotted #D6D6D6; }
        .to-qty { display: inline-flex; align-items: center; border: 1px solid var(--hasp-border, #EBEBEB); border-radius: 6px; overflow: hidden; }
        .to-qty button { width: 1.45rem; height: 1.6rem; border: 0; background: var(--hasp-bg, #F9F9F7); color: var(--hasp-text, #1C1C1C); cursor: pointer; font-size: 0.95rem; line-height: 1; }
        .to-qty button:hover { background: var(--hasp-brand-lt, #FDF0E8); color: var(--hasp-brand, #C8511B); }
        .to-qty span { width: 1.6rem; text-align: center; font-size: 0.82rem; font-weight: 700; }
        .to-row-name { font-size: 0.8rem; font-weight: 700; line-height: 1.3; }
        .to-row-det { font-size: 0.7rem; color: var(--hasp-muted, #6E6E6E); line-height: 1.4; }
        .to-row-amt { text-align: right; font-size: 0.8rem; font-weight: 700; white-space: nowrap; }
        .to-row-del { display: block; margin: 0.25rem 0 0 auto; border: 0; background: none; color: var(--hasp-subtle, #ABABAB); cursor: pointer; font-size: 0.7rem; font-family: 'Open Sans', sans-serif; }
        .to-row-del:hover { color: var(--hasp-brand, #C8511B); }
        .to-total { display: flex; align-items: baseline; justify-content: space-between; margin-top: 0.4rem; padding-top: 0.6rem; border-top: 2px solid var(--hasp-text, #1C1C1C); }
        .to-total span:first-child { font-size: 0.9rem; font-weight: 700; letter-spacing: 0.08em; }
        .to-total span:last-child { font-family: 'Rubik', sans-serif; font-size: 1.6rem; font-weight: 700; }
        .to-change { margin-top: 0.6rem; display: flex; justify-content: space-between; align-items: center; border-radius: 8px; padding: 0.5rem 0.75rem; font-family: 'Open Sans', sans-serif; font-size: 0.82rem; font-weight: 600; background: var(--hasp-green-lt, #E8F5E9); color: var(--hasp-green, #2E7D32); }
        .to-change.is-warn { background: var(--hasp-brand-lt, #FDF0E8); color: var(--hasp-brand, #C8511B); }
        .to-thanks { margin-top: 0.9rem; text-align: center; font-size: 0.72rem; color: var(--hasp-muted, #6E6E6E); line-height: 1.5; }
        .to-submit { margin-top: 0.9rem; font-family: 'Open Sans', sans-serif; }
    </style>

    <div class="to-wrap" x-data="ordenCaja()">
        <x-flash-message />

        <header class="to-head">
            <div>
                <h1 class="to-title">Tomar orden</h1>
                <p class="to-sub">Elige lo que pide el cliente, revisa el ticket y cobra.</p>
            </div>
            <a href="{{ route('empleado.pedidos.index') }}" class="to-btn to-btn-outline">
                <span class="material-symbols-outlined to-icon">shopping_bag</span>
                Pedidos en vivo
            </a>
        </header>

        <div class="to-grid">
            <!-- CATÁLOGO -->
            <section class="to-card" aria-label="Catálogo del día">
                <div class="to-tabbar">
                    <div class="to-tabs" role="tablist">
                        <button type="button" role="tab" class="to-tab" :class="{ 'is-on': vista === 'menus' }" :aria-selected="vista === 'menus'" @click="vista = 'menus'">
                            Menús del día <span class="to-count" x-text="menus.length"></span>
                        </button>
                        <button type="button" role="tab" class="to-tab" :class="{ 'is-on': vista === 'productos' }" :aria-selected="vista === 'productos'" @click="vista = 'productos'">
                            Productos <span class="to-count" x-text="productos.length"></span>
                        </button>
                    </div>
                    <span class="to-date">{{ ucfirst(today()->locale('es')->isoFormat('dddd D [de] MMMM')) }}</span>
                </div>

                <!-- MENÚS -->
                <div class="to-body" x-show="vista === 'menus'">
                    <template x-if="menus.length === 0">
                        <p class="to-empty">No hay menús habilitados para hoy.</p>
                    </template>

                    <template x-if="menus.length > 0">
                        <div>
                            <p class="to-label">1. Elige el menú</p>
                            <div class="to-menus">
                                <template x-for="menu in menus" :key="menu.id">
                                    <button type="button" class="to-menu" :class="{ 'is-on': menuActivoId === menu.id }" :disabled="menu.agotado" @click="menuActivoId = menu.id">
                                        <span class="to-menu-name" x-text="menu.titulo"></span>
                                        <span class="to-menu-meta" x-text="menu.agotado ? 'Agotado' : menu.tipo"></span>
                                        <span class="to-menu-price" x-text="menu.precioVariable ? 'Desde ' + moneda(menu.precioVisible) : moneda(menu.precioBase)"></span>
                                    </button>
                                </template>
                            </div>

                            <template x-if="menuActivo">
                                <div class="to-build">
                                    <p class="to-label">2. Elige entrada, fondo y refresco</p>
                                    <div class="to-steps">
                                        <template x-for="tiempo in tiempos" :key="tiempo.clave">
                                            <div>
                                                <div class="to-step-title">
                                                    <span x-text="tiempo.etiqueta"></span>
                                                    <span class="material-symbols-outlined to-step-ok" x-show="seleccionActiva[tiempo.clave]" x-cloak>check_circle</span>
                                                </div>
                                                <template x-for="opcion in (menuActivo.opciones[tiempo.clave] || [])" :key="opcion.id">
                                                    <button type="button" class="to-opt" :class="{ 'is-on': seleccionActiva[tiempo.clave] === opcion.id }" :disabled="opcion.agotado"
                                                            @click="elegir(tiempo.clave, opcion.id)">
                                                        <span x-text="opcion.nombre"></span>
                                                        <small x-text="opcion.agotado ? 'Agotado' : (menuActivo.precioVariable && tiempo.clave === 'fondo' ? moneda(opcion.precio) : (opcion.stock !== null ? opcion.stock + ' rac.' : ''))"></small>
                                                    </button>
                                                </template>
                                                <p class="to-empty" style="padding: 0.5rem 0; text-align: left;" x-show="(menuActivo.opciones[tiempo.clave] || []).length === 0">Sin opciones habilitadas.</p>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="to-addbar">
                                        <div>
                                            <span style="font-size: 0.78rem; color: var(--hasp-muted, #6E6E6E);">Precio del menú</span>
                                            <p style="font-size: 1.1rem; font-weight: 700; color: var(--hasp-text, #1C1C1C);" x-text="precioMenuTexto"></p>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <span class="to-toast" x-show="aviso" x-text="aviso" x-cloak></span>
                                            <button type="button" class="to-btn to-btn-brand" :disabled="! puedeAgregarMenu" @click="agregarMenu()">
                                                <span class="material-symbols-outlined to-icon">add_shopping_cart</span>
                                                <span x-text="puedeAgregarMenu ? 'Agregar al ticket' : 'Completa las 3 opciones'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- PRODUCTOS -->
                <div class="to-body" x-show="vista === 'productos'" x-cloak>
                    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem;">
                        <div class="to-search" style="flex: 1 1 260px;">
                            <span class="material-symbols-outlined">search</span>
                            <input type="search" x-model="busqueda" placeholder="Buscar café, sánguche, postre..." aria-label="Buscar producto">
                        </div>
                        <span class="to-toast" x-show="aviso" x-text="aviso" x-cloak style="margin-bottom: 0.9rem;"></span>
                    </div>

                    <p class="to-empty" x-show="productosFiltrados.length === 0" x-cloak>
                        <span x-text="productos.length === 0 ? 'No hay productos con stock disponible.' : 'No hay productos que coincidan con la búsqueda.'"></span>
                    </p>

                    <div class="to-prods">
                        <template x-for="producto in productosFiltrados" :key="producto.id">
                            <button type="button" class="to-prod" @click="agregarProducto(producto.id)">
                                <span class="to-prod-qty" x-show="cantidadEnTicket(producto.id) > 0" x-text="cantidadEnTicket(producto.id)" x-cloak></span>
                                <span class="to-prod-name" x-text="producto.nombre"></span>
                                <span class="to-prod-price" x-text="moneda(producto.precio)"></span>
                                <span class="to-prod-stock" x-text="producto.stock + ' disponibles'"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </section>

            <!-- TICKET -->
            <aside class="to-aside" aria-label="Ticket de venta">
                <form method="POST" action="{{ route('empleado.tomar-orden.store') }}">
                    @csrf

                    <div class="to-ticket">
                        <div class="to-t-center">
                            <div class="to-t-logo">☕</div>
                            <p class="to-t-company">GRUPO HASP SERVICE</p>
                            <p class="to-t-line">Cafetería UCH · Comedor Universitario</p>
                            <p class="to-t-line">RUC: 20512836471</p>
                            <p class="to-t-line">Campus Los Olivos · Pabellón A</p>
                        </div>

                        <hr class="to-dash">
                        <p class="to-t-doc">TICKET DE VENTA</p>
                        <div style="margin-top: 0.5rem;">
                            <p class="to-kv"><span>N.° de ticket</span><span>Se asigna al cobrar</span></p>
                            <p class="to-kv"><span>Fecha</span><span>{{ now()->format('d/m/Y') }}</span></p>
                            <p class="to-kv"><span>Hora</span><span x-text="hora"></span></p>
                            <p class="to-kv"><span>Cajero</span><span>{{ auth()->user()->name }}</span></p>
                        </div>

                        <div class="to-field">
                            <label for="cliente_nombre_caja">Cliente *</label>
                            <input id="cliente_nombre_caja" type="text" name="cliente_nombre_caja" x-model="clienteNombre" maxlength="120" required
                                   placeholder="Ej. Juan Pérez" class="to-input">
                        </div>

                        {{-- La modalidad ya no se elige: el sistema exige el campo, por eso va fijo --}}
                        <input type="hidden" name="tipo_consumo" value="salon">

                        <hr class="to-dash">

                        <div class="to-cols"><span>CANT.</span><span>DESCRIPCIÓN</span><span>IMPORTE</span></div>

                        <div class="to-items">
                            <p class="to-empty" x-show="items.length === 0">Aún no hay consumos.<br>Agrega un menú o un producto del catálogo.</p>

                            <template x-for="(item, index) in items" :key="item.clave">
                                <div class="to-row">
                                    <div class="to-qty">
                                        <button type="button" @click="cambiarCantidad(index, -1)" aria-label="Restar una unidad">−</button>
                                        <span x-text="item.cantidad"></span>
                                        <button type="button" @click="cambiarCantidad(index, 1)" aria-label="Sumar una unidad">+</button>
                                    </div>
                                    <div style="min-width: 0;">
                                        <p class="to-row-name" x-text="item.nombre"></p>
                                        <template x-for="linea in item.detalle" :key="linea">
                                            <p class="to-row-det" x-text="linea"></p>
                                        </template>
                                        <p class="to-row-det" x-text="moneda(item.precio) + ' c/u'"></p>
                                    </div>
                                    <div>
                                        <p class="to-row-amt" x-text="moneda(item.precio * item.cantidad)"></p>
                                        <button type="button" class="to-row-del" @click="quitar(index)">Quitar</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <template x-for="(item, index) in items" :key="'hidden-' + item.clave">
                            <div>
                                <input type="hidden" :name="'productos[' + index + '][tipo]'" :value="item.tipo">
                                <input type="hidden" :name="'productos[' + index + '][cantidad]'" :value="item.cantidad">
                                <input type="hidden" :name="'productos[' + index + '][id]'" :value="item.tipo === 'producto' ? item.id : ''">
                                <input type="hidden" :name="'productos[' + index + '][menu_id]'" :value="item.tipo === 'menu' ? item.menu_id : ''">
                                <template x-if="item.tipo === 'menu'">
                                    <div>
                                        <input type="hidden" :name="'productos[' + index + '][opciones][entrada]'" :value="item.opciones.entrada">
                                        <input type="hidden" :name="'productos[' + index + '][opciones][fondo]'" :value="item.opciones.fondo">
                                        <input type="hidden" :name="'productos[' + index + '][opciones][refresco]'" :value="item.opciones.refresco">
                                    </div>
                                </template>
                            </div>
                        </template>

                        <hr class="to-dash" style="margin-bottom: 0.5rem;">

                        <p class="to-kv"><span>Cant. de ítems</span><span x-text="unidades"></span></p>
                        <p class="to-kv"><span>Subtotal (sin IGV)</span><span x-text="moneda(subtotal)"></span></p>
                        <p class="to-kv"><span>IGV (18%)</span><span x-text="moneda(igv)"></span></p>
                        <div class="to-total"><span>TOTAL</span><span x-text="moneda(total)"></span></div>

                        <hr class="to-dash">

                        <fieldset class="to-field" style="border: 0; padding: 0; margin-top: 0;">
                            <legend>Método de pago *</legend>
                            <div class="to-pay">
                                <template x-for="opcion in metodos" :key="opcion.valor">
                                    <label :class="{ 'is-on': metodoPago === opcion.valor }">
                                        <input type="radio" name="metodo_pago" :value="opcion.valor" x-model="metodoPago" class="to-sr">
                                        <span class="material-symbols-outlined" style="font-size: 20px;" x-text="opcion.icono"></span>
                                        <span x-text="opcion.etiqueta"></span>
                                    </label>
                                </template>
                            </div>

                            <div x-show="metodoPago === 'efectivo'" x-cloak>
                                <div class="to-field">
                                    <label for="monto_recibido">Monto recibido</label>
                                    <input id="monto_recibido" type="number" name="monto_recibido" x-model="montoRecibido" step="0.01"
                                           :required="metodoPago === 'efectivo'" :min="total.toFixed(2)" placeholder="0.00" class="to-input">
                                </div>
                                <div class="to-change" :class="{ 'is-warn': faltante > 0 }">
                                    <span x-text="faltante > 0 ? 'Falta por cobrar' : 'Vuelto a entregar'"></span>
                                    <span x-text="moneda(faltante > 0 ? faltante : vuelto)"></span>
                                </div>
                            </div>
                        </fieldset>

                        <button type="submit" class="to-btn to-btn-brand to-submit" :disabled="! puedeCobrar">
                            <span class="material-symbols-outlined to-icon">check_circle</span>
                            Confirmar
                        </button>
                        <p class="to-thanks" style="margin-top: 0.5rem;">Al confirmar se imprime el ticket automáticamente.</p>

                        <p class="to-thanks">¡Gracias por su preferencia!<br>Conserve su ticket para recoger su pedido en barra.</p>
                    </div>
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
                    tiempos: [
                        { clave: 'entrada', etiqueta: 'Entrada' },
                        { clave: 'fondo', etiqueta: 'Fondo' },
                        { clave: 'refresco', etiqueta: 'Refresco' },
                    ],
                    metodos: [
                        { valor: 'efectivo', etiqueta: 'Efectivo', icono: 'payments' },
                        { valor: 'yape_plin', etiqueta: 'Yape/Plin', icono: 'account_balance_wallet' },
                        { valor: 'tarjeta', etiqueta: 'Tarjeta POS', icono: 'credit_card' },
                    ],
                    vista: 'menus',
                    menuActivoId: null,
                    selecciones: {},
                    busqueda: '',
                    items: [],
                    clienteNombre: '',
                    metodoPago: 'efectivo',
                    montoRecibido: '',
                    hora: '',
                    aviso: '',
                    temporizadorAviso: null,

                    init() {
                        const vacias = {};

                        this.menus.forEach((menu) => {
                            vacias[menu.id] = { entrada: null, fondo: null, refresco: null };
                        });

                        this.selecciones = vacias;

                        const primero = this.menus.find((menu) => ! menu.agotado) || this.menus[0];
                        this.menuActivoId = primero ? primero.id : null;

                        this.actualizarHora();
                        setInterval(() => this.actualizarHora(), 30000);
                    },

                    actualizarHora() {
                        this.hora = new Date().toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: true });
                    },

                    moneda(valor) {
                        return 'S/ ' + Number(valor || 0).toFixed(2);
                    },

                    avisar(texto) {
                        this.aviso = texto;
                        clearTimeout(this.temporizadorAviso);
                        this.temporizadorAviso = setTimeout(() => { this.aviso = ''; }, 1800);
                    },

                    /* ---- Menús ---- */
                    get menuActivo() {
                        return this.menus.find((menu) => menu.id === this.menuActivoId) || null;
                    },

                    get seleccionActiva() {
                        return this.selecciones[this.menuActivoId] || { entrada: null, fondo: null, refresco: null };
                    },

                    opcionDe(menu, tiempo, id) {
                        return ((menu.opciones || {})[tiempo] || []).find((opcion) => opcion.id === id) || null;
                    },

                    elegir(tiempo, id) {
                        const actual = this.selecciones[this.menuActivoId];
                        actual[tiempo] = actual[tiempo] === id ? null : id;
                    },

                    get puedeAgregarMenu() {
                        const sel = this.seleccionActiva;

                        return !! (this.menuActivo && sel.entrada && sel.fondo && sel.refresco);
                    },

                    precioMenu(menu, sel) {
                        if (! menu.precioVariable) {
                            return Number(menu.precioBase);
                        }

                        const fondo = this.opcionDe(menu, 'fondo', sel.fondo);

                        return fondo ? Number(fondo.precio) : null;
                    },

                    get precioMenuTexto() {
                        const menu = this.menuActivo;

                        if (! menu) {
                            return '';
                        }

                        const precio = this.precioMenu(menu, this.seleccionActiva);

                        return precio === null ? 'Desde ' + this.moneda(menu.precioVisible) : this.moneda(precio);
                    },

                    agregarMenu() {
                        if (! this.puedeAgregarMenu) {
                            return;
                        }

                        const menu = this.menuActivo;
                        const sel = this.seleccionActiva;
                        const nombres = {};

                        this.tiempos.forEach((tiempo) => {
                            const opcion = this.opcionDe(menu, tiempo.clave, sel[tiempo.clave]);
                            nombres[tiempo.clave] = opcion ? opcion.nombre : 'Opción';
                        });

                        const clave = ['menu', menu.id, sel.entrada, sel.fondo, sel.refresco].join('-');
                        const existente = this.items.find((item) => item.clave === clave);

                        if (existente) {
                            existente.cantidad = Math.min(50, existente.cantidad + 1);
                        } else {
                            this.items.push({
                                clave,
                                tipo: 'menu',
                                menu_id: menu.id,
                                id: '',
                                cantidad: 1,
                                opciones: { entrada: sel.entrada, fondo: sel.fondo, refresco: sel.refresco },
                                nombre: menu.titulo,
                                detalle: [
                                    'Entrada: ' + nombres.entrada,
                                    'Fondo: ' + nombres.fondo,
                                    'Refresco: ' + nombres.refresco,
                                ],
                                precio: this.precioMenu(menu, sel),
                            });
                        }

                        this.selecciones[menu.id] = { entrada: null, fondo: null, refresco: null };
                        this.avisar('Menú agregado');
                    },

                    /* ---- Productos ---- */
                    get productosFiltrados() {
                        const texto = this.busqueda.trim().toLowerCase();

                        if (! texto) {
                            return this.productos;
                        }

                        return this.productos.filter((producto) => producto.nombre.toLowerCase().includes(texto));
                    },

                    cantidadEnTicket(productoId) {
                        const item = this.items.find((registro) => registro.clave === 'producto-' + productoId);

                        return item ? item.cantidad : 0;
                    },

                    agregarProducto(productoId) {
                        const producto = this.productos.find((item) => item.id === productoId);

                        if (! producto || producto.stock <= 0) {
                            return;
                        }

                        const existente = this.items.find((item) => item.clave === 'producto-' + productoId);

                        if (existente) {
                            if (existente.cantidad >= Math.min(50, producto.stock)) {
                                this.avisar('No hay más stock');

                                return;
                            }

                            existente.cantidad++;
                        } else {
                            this.items.push({
                                clave: 'producto-' + productoId,
                                tipo: 'producto',
                                id: producto.id,
                                menu_id: '',
                                cantidad: 1,
                                opciones: null,
                                nombre: producto.nombre,
                                detalle: [],
                                precio: Number(producto.precio),
                            });
                        }

                        this.avisar('Agregado');
                    },

                    /* ---- Ticket ---- */
                    cambiarCantidad(index, delta) {
                        const item = this.items[index];
                        const producto = item.tipo === 'producto' ? this.productos.find((registro) => registro.id === item.id) : null;
                        const maximo = producto ? Math.min(50, producto.stock) : 50;

                        item.cantidad = Math.max(1, Math.min(maximo, Number(item.cantidad) + delta));
                    },

                    quitar(index) {
                        this.items.splice(index, 1);
                    },

                    get unidades() {
                        return this.items.reduce((suma, item) => suma + Number(item.cantidad), 0);
                    },

                    get total() {
                        return this.items.reduce((suma, item) => suma + Number(item.precio) * Number(item.cantidad), 0);
                    },

                    get subtotal() {
                        return Math.round((this.total / 1.18) * 100) / 100;
                    },

                    get igv() {
                        return Math.round((this.total - this.subtotal) * 100) / 100;
                    },

                    get vuelto() {
                        if (this.metodoPago !== 'efectivo' || this.montoRecibido === '') {
                            return 0;
                        }

                        return Math.max(0, Number(this.montoRecibido) - this.total);
                    },

                    get faltante() {
                        if (this.metodoPago !== 'efectivo' || this.montoRecibido === '' || this.items.length === 0) {
                            return 0;
                        }

                        return Math.max(0, Math.round((this.total - Number(this.montoRecibido)) * 100) / 100);
                    },

                    get puedeCobrar() {
                        if (this.items.length === 0 || ! this.clienteNombre.trim()) {
                            return false;
                        }

                        if (this.metodoPago === 'efectivo') {
                            return this.montoRecibido !== '' && this.faltante === 0;
                        }

                        return true;
                    },
                };
            }
        </script>
    @endpush
</x-layouts.app>
