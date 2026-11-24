<x-layouts.app>
    @php
        $hoy = now();
        $heroTarjetas = collect($productosMenu)
            ->filter(fn (array $producto): bool => ! empty($producto['imagen']))
            ->take(3)
            ->map(fn (array $producto): array => [
                'nombre' => $producto['nombre'],
                'etiqueta' => $producto['categoria'],
                'imagen' => $producto['imagen'],
                'destacada' => (bool) $producto['popular'],
            ]);

        foreach ($menusMenu->take(3 - $heroTarjetas->count()) as $menu) {
            $heroTarjetas->push([
                'nombre' => $menu['titulo'],
                'etiqueta' => $menu['etiqueta'] ?: 'Menú del día',
                'imagen' => $menu['imagen'],
                'destacada' => (bool) $menu['destacado'],
            ]);
        }

        $fechaTexto = ucfirst($hoy->copy()->locale('es')->translatedFormat('l, j \d\e F \d\e Y'));
        $heroImg = $heroTarjetas->first()['imagen'] ?? null;

        $horaActual = (int) $hoy->format('G');
        $comedorAbierto = $hoy->dayOfWeek !== 0 && $horaActual >= 7 && $horaActual < 18;
        $turno = match (true) {
            $horaActual < 12 => 'Mañana',
            $horaActual < 19 => 'Tarde',
            default => 'Noche',
        };
    @endphp

    <script>
        window.productosMenu = @js($productosMenu);
        window.categoriasMenu = @js($categoriasMenu);
        window.menusMenu = @js($menusMenu);
        window.menuHero = @js($menuHero);
        window.heroTarjetas = @js($heroTarjetas);
    </script>

    <div class="hasp-page" x-data="catalogoUCH()" x-init="init()" x-cloak
         @abrir-carrito.window="abierto = true"
         @keydown.escape.window="abierto = false">

        <!-- HERO -->
        <section class="hasp-hero">
            @if ($heroImg)
                <div class="hasp-hero-bg-img" style="background-image: url('{{ $heroImg }}')"></div>
            @endif
            <div class="hasp-hero-inner">
                <div class="hasp-hero-pill">
                    <span class="hasp-dot"></span>
                    {{ $comedorAbierto ? 'Menú disponible hoy' : 'Comedor cerrado ahora' }} · {{ $fechaTexto }}
                </div>
                <h1>Bienvenido a<br><em>Grupo HASP Service</em></h1>
                <p>Pide tu almuerzo en línea y recógelo en caja sin hacer cola. Menú universitario, ejecutivo y light cada día.</p>
                <div class="hasp-hero-cta">
                    <a href="#menus-del-dia" class="hasp-btn-primary">Ver menú de hoy</a>
                    <a href="#productos" class="hasp-btn-ghost">Ver productos</a>
                </div>
                <div class="hasp-hero-info">
                    <div class="hasp-info-chip"><span>🕐</span> Lun – Vie, 7:00 am – 5:00 pm · Sáb, 7:00 am – 3:00 pm</div>
                    <div class="hasp-info-chip"><span>📍</span> Comedor UCH · 1er Piso</div>
                    <div class="hasp-info-chip"><span>💳</span> Efectivo, tarjeta, Yape, Plin</div>
                </div>
            </div>
        </section>

        <svg class="hasp-wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 48" preserveAspectRatio="none" aria-hidden="true"><path d="M0,48 C360,0 1080,0 1440,48 L1440,48 L0,48 Z"/></svg>

        <div class="hasp-main">

            <!-- MENÚ DEL DÍA -->
            <section id="menus-del-dia" style="padding-top: 2rem;">
                <div class="hasp-section-header">
                    <div class="hasp-section-title">
                        <h2>Menú del día</h2>
                        <span class="hasp-pill hasp-pill-green">Hoy disponible</span>
                    </div>
                </div>

                <div class="hasp-fecha-banner">
                    <div class="hasp-fecha-left">
                        <div class="hasp-fecha-icon">📅</div>
                        <div>
                            <div class="hasp-fecha-label">Fecha de servicio</div>
                            <div class="hasp-fecha-valor">{{ $fechaTexto }}</div>
                        </div>
                    </div>
                    <div class="hasp-estado-disponible {{ $comedorAbierto ? '' : 'hasp-cerrado' }}">
                        <div class="hasp-dot-green"></div>
                        {{ $comedorAbierto ? 'Atendiendo ahora' : 'Fuera de horario' }} · Turno {{ $turno }}
                    </div>
                </div>

                <div class="hasp-menus-grid">
                    <template x-for="menu in menusVisibles" :key="menu.id">
                        <article class="hasp-menu-card" :class="'hasp-card-' + tema(menu.tipo).clase">
                            <img class="hasp-menu-card-img" :src="menu.imagen" :alt="menu.titulo" loading="lazy">
                            <div class="hasp-menu-card-header">
                                <span class="hasp-menu-type-badge" :class="'hasp-badge-' + tema(menu.tipo).badge"
                                      x-text="tema(menu.tipo).icono + ' ' + tema(menu.tipo).nombre"></span>
                                <div class="hasp-menu-price">
                                    <span x-show="menu.esPrecioVariable" style="margin-right: 0.2rem;">desde</span>
                                    <b x-text="'S/ ' + (menu.esPrecioVariable ? menu.precioVisible : precioMenu(menu)).toFixed(2)"></b>
                                    <span>/ persona</span>
                                </div>
                            </div>

                            <div class="hasp-menu-card-body">
                                <div>
                                    <div class="hasp-menu-name" x-text="menu.titulo"></div>
                                    <div class="hasp-menu-sub" x-text="racionesMenu(menu)"></div>
                                </div>

                                <template x-for="paso in pasos" :key="paso.tiempo">
                                    <div class="hasp-fondo-row" x-show="menu.opciones[paso.tiempo].length > 0">
                                        <div class="hasp-fondo-label" x-text="paso.titulo"></div>
                                        <div class="hasp-fondo-opts" role="radiogroup" :aria-label="paso.titulo">
                                            <template x-for="opcion in menu.opciones[paso.tiempo]" :key="opcion.id">
                                                <div class="hasp-fondo-opt" role="radio" tabindex="0"
                                                     :aria-checked="sel[menu.id] && sel[menu.id][paso.tiempo] === opcion.id ? 'true' : 'false'"
                                                     :class="{
                                                         'hasp-selected': sel[menu.id] && sel[menu.id][paso.tiempo] === opcion.id,
                                                         'hasp-disabled': !opcionDisponible(opcion),
                                                     }"
                                                     @click="elegir(menu, paso.tiempo, opcion)"
                                                     @keydown.enter.prevent="elegir(menu, paso.tiempo, opcion)"
                                                     @keydown.space.prevent="elegir(menu, paso.tiempo, opcion)">
                                                    <span class="hasp-fondo-opt-text" x-text="opcion.nombre"></span>
                                                    <span class="hasp-fondo-opt-meta" x-text="detalleOpcion(menu, paso.tiempo, opcion)"></span>
                                                    <div class="hasp-fondo-radio"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="hasp-menu-card-footer">
                                <button type="button"
                                        class="hasp-btn-add"
                                        :class="'hasp-btn-add-' + tema(menu.tipo).clase"
                                        :disabled="menu.agotado || !menuListo(menu)"
                                        @click="agregarMenu(menu)"
                                        x-text="menu.agotado ? 'Agotado' : (feedbackId === 'm' + menu.id ? feedbackMensaje : '+ Agregar al carrito')"></button>
                            </div>
                        </article>
                    </template>
                </div>

                <div x-show="menusVisibles.length === 0" x-cloak class="hasp-empty">
                    <span x-text="hayFiltrosActivos ? 'Sin menús con estos filtros.' : 'No hay menús que coincidan con tu búsqueda.'"></span>
                </div>
            </section>

            <!-- PRODUCTOS -->
            <section id="productos" style="padding-top: 1rem;">
                <div class="hasp-section-header">
                    <div class="hasp-section-title">
                        <h2>Productos</h2>
                        <span class="hasp-pill hasp-pill-brand">Cafetería</span>
                    </div>
                </div>

                <div class="hasp-cat-tabs">
                    <template x-for="categoria in categorias" :key="categoria.slug">
                        <button type="button" class="hasp-cat-tab"
                                :class="{ 'hasp-active': categoriaActiva === categoria.nombre }"
                                @click="categoriaActiva = categoria.nombre"
                                x-text="categoria.nombre"></button>
                    </template>
                </div>

                <div class="hasp-filtros">
                    <select class="hasp-select" x-model="precioFiltro" aria-label="Filtrar por precio">
                        <option value="todos">Todos los precios</option>
                        <option value="hasta5">Hasta S/ 5.00</option>
                        <option value="5a10">S/ 5.00 - S/ 10.00</option>
                        <option value="desde10">Desde S/ 10.00</option>
                    </select>
                    <select class="hasp-select" x-model="etiquetaFiltro" aria-label="Filtrar por etiqueta">
                        <option value="todas">Todas las etiquetas</option>
                        <option value="popular">Popular</option>
                        <option value="rapido">Rápido</option>
                        <option value="disponible">Disponible</option>
                        <option value="agotado">Agotado</option>
                    </select>
                    <select class="hasp-select" x-model="ordenFiltro" aria-label="Ordenar productos">
                        <option value="popular">Más pedidos</option>
                        <option value="precio-asc">Precio: menor a mayor</option>
                        <option value="precio-desc">Precio: mayor a menor</option>
                        <option value="nombre">Nombre A-Z</option>
                    </select>
                    <span class="hasp-count">Mostrando <span x-text="menusVisibles.length + filtrados.length"></span> opciones</span>
                    <button type="button" class="hasp-link-btn" x-show="hayFiltrosActivos" x-cloak @click="limpiarFiltros()">Limpiar</button>
                </div>

                <div class="hasp-products-grid">
                    <template x-for="producto in filtrados" :key="producto.id">
                        <div class="hasp-product-card">
                            <div class="hasp-product-media">
                                <template x-if="producto.imagen">
                                    <img class="hasp-product-img" :src="producto.imagen" :alt="producto.nombre" loading="lazy">
                                </template>
                                <template x-if="!producto.imagen">
                                    <div class="hasp-product-ph">🍽️</div>
                                </template>
                                <div class="hasp-agotado" x-show="!producto.disponible" x-cloak><span>Agotado</span></div>
                            </div>
                            <div class="hasp-product-info">
                                <div class="hasp-product-name" x-text="producto.nombre"></div>
                                <div class="hasp-product-cat" x-text="producto.categoria"></div>
                                <div class="hasp-product-bottom">
                                    <span class="hasp-product-price" x-text="'S/ ' + producto.precio.toFixed(2)"></span>
                                    <button type="button" class="hasp-btn-add-sm"
                                            :disabled="!producto.disponible"
                                            :aria-label="'Agregar ' + producto.nombre"
                                            :title="feedbackId === producto.id ? feedbackMensaje : 'Agregar'"
                                            @click="agregar(producto)"
                                            x-text="feedbackId === producto.id ? (feedbackMensaje.startsWith('✓') ? '✓' : '✕') : '+'"></button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="filtrados.length === 0" x-cloak class="hasp-empty">Sin resultados. Prueba con otra categoría o limpia los filtros.</div>
            </section>
        </div>

        <!-- FOOTER -->
        <footer class="hasp-footer">
            <p><strong>Grupo HASP Service</strong> · Cafetería UCH · Comedor Universitario, 1er Piso</p>
            <p style="margin-top: 0.3rem;">Comprometidos con tu alimentación · Lun – Vie 7:00 am – 5:00 pm · Sáb 7:00 am – 3:00 pm</p>
        </footer>

        <!-- CARRITO -->
        <div class="hasp-cart-overlay" :class="{ 'hasp-open': abierto }" @click="abierto = false"></div>
        <aside class="hasp-cart-panel" :class="{ 'hasp-open': abierto }" :aria-hidden="abierto ? 'false' : 'true'">
            <div class="hasp-cart-header">
                <h3>🛒 Mi carrito</h3>
                <button type="button" class="hasp-btn-close" @click="abierto = false" aria-label="Cerrar carrito">✕</button>
            </div>

            <div class="hasp-cart-items">
                <div class="hasp-empty-cart" x-show="items.length === 0">
                    <div class="hasp-big-icon">🛒</div>
                    <p>Tu carrito está vacío.<br>Agrega algo del menú.</p>
                </div>

                <template x-for="(item, index) in items" :key="item.id">
                    <div class="hasp-cart-item">
                        <template x-if="item.imagen">
                            <img class="hasp-cart-item-img" :src="item.imagen" :alt="item.nombre" onerror="this.style.display='none'">
                        </template>
                        <template x-if="!item.imagen">
                            <div class="hasp-cart-item-ph">🍽️</div>
                        </template>
                        <div class="hasp-cart-item-info">
                            <div class="hasp-cart-item-name" x-text="item.nombre"></div>
                            <div class="hasp-cart-item-sub" x-text="item.detalle || ('S/ ' + item.precio.toFixed(2) + ' c/u')"></div>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.3rem;">
                            <div class="hasp-cart-qty">
                                <button type="button" class="hasp-qty-btn" @click="cambiarCantidad(index, -1)" aria-label="Quitar una unidad">−</button>
                                <span class="hasp-qty-num" x-text="item.cantidad"></span>
                                <button type="button" class="hasp-qty-btn" @click="cambiarCantidad(index, 1)" aria-label="Agregar una unidad">+</button>
                            </div>
                            <span class="hasp-cart-item-price" x-text="'S/ ' + (item.precio * item.cantidad).toFixed(2)"></span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="hasp-cart-footer" x-show="items.length > 0" x-cloak>
                <div class="hasp-cart-total-row">
                    <span class="hasp-cart-total-label">Total a pagar</span>
                    <span class="hasp-cart-total-val" x-text="'S/ ' + total().toFixed(2)"></span>
                </div>
                <button type="button" class="hasp-btn-checkout" @click="irAPagar()">💳 Ir al pago</button>
                <p class="hasp-cart-note">Pago en efectivo, tarjeta o Yape/Plin</p>
            </div>
        </aside>
    </div>

    <script>
        function catalogoUCH() {
            return {
                productos: [],
                categorias: [],
                menus: [],
                menuHero: null,
                heroTarjetas: [],
                filtroAbierto: null,
                categoriaActiva: 'Todos',
                precioFiltro: 'todos',
                etiquetaFiltro: 'todas',
                ordenFiltro: 'popular',
                busqueda: window.busquedaMenu || new URLSearchParams(window.location.search).get('buscar') || '',
                items: [],
                abierto: false,
                feedbackId: null,
                feedbackMensaje: '',
                temporizadorFeedback: null,
                sel: {},
                pasos: [
                    { tiempo: 'entrada', titulo: 'Elige tu entrada' },
                    { tiempo: 'fondo', titulo: 'Elige tu fondo' },
                    { tiempo: 'refresco', titulo: 'Elige tu refresco' },
                ],

                get etiquetaPrecio() {
                    return {
                        todos: 'Todos los precios',
                        hasta5: 'Hasta S/ 5.00',
                        '5a10': 'S/ 5.00 - S/ 10.00',
                        desde10: 'Desde S/ 10.00',
                    }[this.precioFiltro] || 'Precio';
                },

                get etiquetaEtiqueta() {
                    return {
                        todas: 'Todas',
                        popular: 'Popular',
                        rapido: 'Rápido',
                        disponible: 'Disponible',
                        agotado: 'Agotado',
                    }[this.etiquetaFiltro] || 'Etiqueta';
                },

                get etiquetaOrden() {
                    return {
                        popular: 'Más pedidos',
                        'precio-asc': 'Precio: menor a mayor',
                        'precio-desc': 'Precio: mayor a menor',
                        nombre: 'Nombre A-Z',
                    }[this.ordenFiltro] || 'Más pedidos';
                },

                get hayFiltrosActivos() {
                    return this.categoriaActiva !== 'Todos'
                        || this.precioFiltro !== 'todos'
                        || this.etiquetaFiltro !== 'todas';
                },

                get filtrados() {
                    const consulta = this.busqueda.trim().toLowerCase();

                    const resultados = this.productos.filter((producto) => {
                        const coincideCategoria = this.categoriaActiva === 'Todos' || producto.categoria === this.categoriaActiva;
                        const coincidePrecio = this.precioFiltro === 'todos'
                            || (this.precioFiltro === 'hasta5' && producto.precio < 5)
                            || (this.precioFiltro === '5a10' && producto.precio >= 5 && producto.precio <= 10)
                            || (this.precioFiltro === 'desde10' && producto.precio > 10);
                        const coincideEtiqueta = this.etiquetaFiltro === 'todas'
                            || (this.etiquetaFiltro === 'popular' && producto.popular)
                            || (this.etiquetaFiltro === 'rapido' && producto.rapido)
                            || (this.etiquetaFiltro === 'disponible' && producto.disponible)
                            || (this.etiquetaFiltro === 'agotado' && !producto.disponible);
                        const descripcion = producto.descripcion || '';
                        const coincideBusqueda = !consulta
                            || producto.nombre.toLowerCase().includes(consulta)
                            || descripcion.toLowerCase().includes(consulta);

                        return coincideCategoria && coincidePrecio && coincideEtiqueta && coincideBusqueda;
                    });

                    return resultados.sort((a, b) => {
                        if (this.ordenFiltro === 'precio-asc') {
                            return a.precio - b.precio;
                        }

                        if (this.ordenFiltro === 'precio-desc') {
                            return b.precio - a.precio;
                        }

                        if (this.ordenFiltro === 'nombre') {
                            return a.nombre.localeCompare(b.nombre, 'es');
                        }

                        if (a.popular !== b.popular) {
                            return Number(b.popular) - Number(a.popular);
                        }

                        return a.nombre.localeCompare(b.nombre, 'es');
                    });
                },

                get menusVisibles() {
                    const consulta = this.busqueda.trim().toLowerCase();

                    const resultados = this.menus.filter((menu) => {
                        const coincideCategoria = this.categoriaActiva === 'Todos';
                        const precio = Number(menu.precioVisible) || 0;
                        const coincidePrecio = this.precioFiltro === 'todos'
                            || (this.precioFiltro === 'hasta5' && precio < 5)
                            || (this.precioFiltro === '5a10' && precio >= 5 && precio <= 10)
                            || (this.precioFiltro === 'desde10' && precio > 10);
                        const coincideEtiqueta = this.etiquetaFiltro === 'todas'
                            || (this.etiquetaFiltro === 'popular' && menu.destacado)
                            || (this.etiquetaFiltro === 'disponible' && !menu.agotado)
                            || (this.etiquetaFiltro === 'agotado' && menu.agotado);
                        const textos = [
                            menu.titulo || '',
                            menu.etiqueta || '',
                            ...Object.values(menu.opciones || {})
                                .flat()
                                .map((opcion) => opcion.nombre || ''),
                        ];
                        const coincideBusqueda = !consulta
                            || textos.some((texto) => texto.toLowerCase().includes(consulta));

                        return coincideCategoria && coincidePrecio && coincideEtiqueta && coincideBusqueda;
                    });

                    return resultados.sort((a, b) => {
                        const precioA = Number(a.precioVisible) || 0;
                        const precioB = Number(b.precioVisible) || 0;

                        if (this.ordenFiltro === 'precio-asc') {
                            return precioA - precioB;
                        }

                        if (this.ordenFiltro === 'precio-desc') {
                            return precioB - precioA;
                        }

                        if (this.ordenFiltro === 'nombre') {
                            return (a.titulo || '').localeCompare(b.titulo || '', 'es');
                        }

                        if (Boolean(a.destacado) !== Boolean(b.destacado)) {
                            return Number(Boolean(b.destacado)) - Number(Boolean(a.destacado));
                        }

                        return (a.titulo || '').localeCompare(b.titulo || '', 'es');
                    });
                },

                tema(tipo) {
                    return {
                        universitario: { clase: 'univ', badge: 'universitario', icono: '🎓', nombre: 'Universitario' },
                        ejecutivo: { clase: 'ejec', badge: 'ejecutivo', icono: '👔', nombre: 'Ejecutivo' },
                        dieta: { clase: 'light', badge: 'light', icono: '🥦', nombre: 'Light' },
                    }[tipo] || { clase: 'univ', badge: 'universitario', icono: '🍽️', nombre: 'Menú' };
                },

                get totalItems() {
                    return this.items.reduce((suma, item) => suma + (Number(item.cantidad) || 0), 0);
                },

                opcionDisponible(opcion) {
                    return opcion.stock === null || opcion.stock > 0;
                },

                inicializarSeleccion(menu) {
                    const primero = (opciones) => {
                        const opcion = opciones.find((item) => this.opcionDisponible(item));
                        return opcion ? opcion.id : null;
                    };

                    this.sel[menu.id] = {
                        entrada: primero(menu.opciones.entrada),
                        fondo: primero(menu.opciones.fondo),
                        refresco: primero(menu.opciones.refresco),
                    };
                },

                elegir(menu, tiempo, opcion) {
                    if (!this.opcionDisponible(opcion)) {
                        return;
                    }

                    this.sel[menu.id][tiempo] = opcion.id;
                },

                opcionElegida(menu, tiempo) {
                    const id = this.sel[menu.id]?.[tiempo];

                    return menu.opciones[tiempo].find((item) => item.id === id) || null;
                },

                precioMenu(menu) {
                    if (!menu.esPrecioVariable) {
                        return menu.precioBase;
                    }

                    return this.opcionElegida(menu, 'fondo')?.precio ?? menu.precioBase;
                },

                menuListo(menu) {
                    return ['entrada', 'fondo', 'refresco'].every((tiempo) => {
                        const opcion = this.opcionElegida(menu, tiempo);

                        return opcion && this.opcionDisponible(opcion);
                    });
                },

                detalleOpcion(menu, tiempo, opcion) {
                    if (!this.opcionDisponible(opcion)) {
                        return 'Agotado';
                    }

                    if (tiempo === 'fondo' && menu.esPrecioVariable && opcion.precio !== null) {
                        return 'S/ ' + opcion.precio.toFixed(2);
                    }

                    return opcion.stock !== null && opcion.stock <= 5 ? 'Quedan ' + opcion.stock : '';
                },

                init() {
                    this.productos = window.productosMenu || [];
                    this.categorias = window.categoriasMenu || [];
                    this.menus = window.menusMenu || [];
                    this.menuHero = window.menuHero || null;
                    this.heroTarjetas = window.heroTarjetas || [];
                    this.menus.forEach((menu) => this.inicializarSeleccion(menu));
                    this.items = this.leerCarrito();
                    window.busquedaMenu = this.busqueda;

                    window.addEventListener('buscar-menu', (evento) => {
                        this.busqueda = evento.detail?.value || '';
                    });

                    this.$watch('items', () => {
                        localStorage.setItem('carrito', JSON.stringify(this.items));
                        localStorage.setItem('carrito_checkout', JSON.stringify(this.items));
                        window.dispatchEvent(new CustomEvent('carrito-actualizado', { detail: this.items }));
                    });

                    window.dispatchEvent(new CustomEvent('carrito-actualizado', { detail: this.items }));
                },

                limpiarFiltros() {
                    this.filtroAbierto = null;
                    this.categoriaActiva = 'Todos';
                    this.precioFiltro = 'todos';
                    this.etiquetaFiltro = 'todas';
                    this.ordenFiltro = 'popular';
                },

                racionesMenu(menu) {
                    const opciones = menu.opciones.fondo || [];
                    if (opciones.length === 0) {
                        return 'Sin raciones';
                    }

                    if (opciones.some((opcion) => opcion.stock === null)) {
                        return 'Sin límite';
                    }

                    const disponibles = opciones
                        .map((opcion) => opcion.stock)
                        .filter((stock) => stock > 0);

                    if (disponibles.length === 0) {
                        return 'Agotado';
                    }

                    return Math.max(...disponibles) + ' raciones disponibles';
                },

                leerCarrito() {
                    try {
                        const datos = JSON.parse(localStorage.getItem('carrito') || '[]');

                        if (!Array.isArray(datos)) {
                            return [];
                        }

                        return datos.map((item) => ({
                            ...item,
                            tipo: item.tipo || 'producto',
                            precio: Number(item.precio) || 0,
                            cantidad: Number(item.cantidad) || 1,
                        }));
                    } catch (error) {
                        return [];
                    }
                },

                mostrarFeedback(id, mensaje) {
                    this.feedbackId = id;
                    this.feedbackMensaje = mensaje;
                    clearTimeout(this.temporizadorFeedback);
                    this.temporizadorFeedback = setTimeout(() => {
                        this.feedbackId = null;
                        this.feedbackMensaje = '';
                    }, 900);
                },

                agregar(producto) {
                    if (!producto.disponible) {
                        return;
                    }

                    const existente = this.items.find((item) => item.id === producto.id);
                    const cantidadActual = existente ? existente.cantidad : 0;

                    if (cantidadActual >= producto.stock) {
                        this.mostrarFeedback(producto.id, 'Sin stock');
                        return;
                    }

                    if (existente) {
                        existente.cantidad++;
                    } else {
                        this.items.push({
                            id: producto.id,
                            tipo: 'producto',
                            nombre: producto.nombre,
                            precio: producto.precio,
                            cantidad: 1,
                            stock: producto.stock,
                            imagen: producto.imagen || '',
                            detalle: '',
                        });
                    }

                    this.mostrarFeedback(producto.id, '✓ Agregado');
                },

                agregarMenu(menu) {
                    if (!this.menuListo(menu)) {
                        return;
                    }

                    const entrada = this.opcionElegida(menu, 'entrada');
                    const fondo = this.opcionElegida(menu, 'fondo');
                    const refresco = this.opcionElegida(menu, 'refresco');
                    const id = `menu-${menu.id}-${entrada.id}-${fondo.id}-${refresco.id}`;
                    const existente = this.items.find((item) => item.id === id);
                    const stock = fondo.stock;

                    if (existente && stock !== null && existente.cantidad >= stock) {
                        this.mostrarFeedback('m' + menu.id, 'Sin stock');
                        return;
                    }

                    const detalle = `Entrada: ${entrada.nombre} · Plato: ${fondo.nombre} · Refresco: ${refresco.nombre}`;

                    if (existente) {
                        existente.cantidad++;
                    } else {
                        this.items.push({
                            id,
                            tipo: 'menu',
                            menu_id: menu.id,
                            nombre: menu.titulo,
                            detalle,
                            precio: this.precioMenu(menu),
                            cantidad: 1,
                            stock,
                            imagen: menu.imagen || '',
                            opciones: {
                                entrada: entrada.id,
                                fondo: fondo.id,
                                refresco: refresco.id,
                            },
                        });
                    }

                    this.mostrarFeedback('m' + menu.id, '✓ Menú agregado');
                },

                cambiarCantidad(index, delta) {
                    const item = this.items[index];

                    if (!item) {
                        return;
                    }

                    if (delta > 0 && item.stock !== null && item.stock !== undefined && item.cantidad + delta > item.stock) {
                        return;
                    }

                    item.cantidad += delta;

                    if (item.cantidad <= 0) {
                        this.items.splice(index, 1);
                    }
                },

                quitar(index) {
                    this.items.splice(index, 1);
                },

                total() {
                    return this.items.reduce((suma, item) => suma + (item.precio * item.cantidad), 0);
                },

                irAPagar() {
                    localStorage.setItem('carrito_checkout', JSON.stringify(this.items));
                    window.location.href = '/checkout';
                },
            };
        }
    </script>
</x-layouts.app>
