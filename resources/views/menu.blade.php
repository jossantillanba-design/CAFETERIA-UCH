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

        $horaActual = (int) $hoy->format('G');
        $comedorAbierto = $hoy->dayOfWeek !== 0 && $horaActual >= 7 && $horaActual < 18;
        $turno = $horaActual < 11 ? 'Desayuno' : ($horaActual < 15 ? 'Almuerzo' : 'Tarde');
    @endphp

    <script>
        window.productosMenu = @js($productosMenu);
        window.categoriasMenu = @js($categoriasMenu);
        window.menusMenu = @js($menusMenu);
        window.menuHero = @js($menuHero);
        window.heroTarjetas = @js($heroTarjetas);
    </script>

    <div x-data="catalogoUCH()" x-init="init()" x-cloak class="w-full bg-uch-bg">
        <!-- Hero institucional -->
        <section class="relative w-full h-[360px] sm:h-[400px] lg:h-[420px] overflow-hidden bg-uch-navy hero-principal select-none">
            <template x-for="(tarjeta, indice) in heroTarjetas" :key="tarjeta.nombre + '-' + indice">
                <img x-show="indiceHero === indice"
                     x-transition.opacity
                     :src="tarjeta.imagen"
                     :alt="'Plato UCH: ' + tarjeta.nombre"
                     class="absolute inset-0 w-full h-full object-cover">
            </template>
            <div class="absolute inset-0 bg-gradient-to-r from-[#1B3668]/95 via-[#1B3668]/80 to-transparent"></div>

            <div class="relative max-w-7xl mx-auto h-full px-6 lg:px-12 flex items-center z-10">
                <div class="max-w-xl flex flex-col items-start gap-4">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-uch-amber text-uch-navy font-bold text-xs tracking-wider shadow-sm">
                        <x-icon name="school" class="w-4 h-4" />
                        CAMPUS LOS OLIVOS • #PENSAMOSDIFERENTE
                    </span>
                    <h1 class="font-rubik text-headline-lg-mobile sm:text-[38px] lg:text-headline-lg font-extrabold leading-tight tracking-tight text-white">
                        Tu almuerzo UCH, listo sin hacer cola
                    </h1>
                    <p class="font-open text-body-md text-white/90 leading-relaxed">
                        Arma tu menú de 3 tiempos o pide un snack entre clases y recógelo en barra.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <a href="#menus-del-dia"
                           class="inline-flex items-center gap-2 bg-uch-coral hover:bg-uch-coral-dark text-white active:scale-95 transition-all font-rubik font-bold px-6 py-3 rounded-xl shadow-lg text-sm">
                            <span>Armar menú de hoy</span>
                            <span class="material-symbols-outlined text-[18px]">arrow_downward</span>
                        </a>
                        <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/10 border border-white/20 text-white/90 text-xs font-medium backdrop-blur-sm">
                            <span class="text-uch-amber text-sm leading-none">★</span>
                            Incluye crema de ají y refresco
                        </span>
                    </div>
                </div>
            </div>

            <template x-if="heroTarjetas.length > 1">
                <div>
                    <button type="button" @click="cambiarHero(-1)" aria-label="Diapositiva anterior"
                            class="absolute left-4 sm:left-6 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/85 hover:bg-white text-uch-navy shadow-lg flex items-center justify-center transition-all hover:scale-105 active:scale-95 z-20">
                        <span class="material-symbols-outlined text-[24px]">chevron_left</span>
                    </button>
                    <button type="button" @click="cambiarHero(1)" aria-label="Siguiente diapositiva"
                            class="absolute right-4 sm:right-6 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/85 hover:bg-white text-uch-navy shadow-lg flex items-center justify-center transition-all hover:scale-105 active:scale-95 z-20">
                        <span class="material-symbols-outlined text-[24px]">chevron_right</span>
                    </button>
                    <div class="absolute bottom-5 right-6 lg:right-12 flex items-center gap-2 z-20">
                        <template x-for="(tarjeta, indice) in heroTarjetas" :key="'punto-' + indice">
                            <button type="button" @click="indiceHero = indice" :aria-label="'Ver ' + tarjeta.nombre"
                                    :class="indiceHero === indice ? 'w-6 bg-uch-coral' : 'w-2 bg-white/60 hover:bg-white'"
                                    class="h-2 rounded-full transition-all shadow-sm"></button>
                        </template>
                    </div>
                </div>
            </template>
        </section>

        <!-- Estado operativo -->
        <section class="w-full bg-white border-b border-uch-border shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 py-3 flex flex-wrap items-center justify-between gap-3 text-uch-text">
                <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-body-sm">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-uch-teal opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-uch-teal"></span>
                        </span>
                        <span class="font-bold">{{ $comedorAbierto ? 'Comedor abierto' : 'Comedor cerrado' }}</span>
                    </div>
                    <span class="text-uch-border select-none hidden sm:inline">|</span>
                    <div class="flex items-center gap-1.5 text-uch-muted font-medium">
                        <x-icon name="schedule" class="w-4 h-4 text-uch-blue" />
                        <span>Tiempo estimado: <strong class="text-uch-text font-semibold">8 min</strong></span>
                    </div>
                    <span class="text-uch-border select-none hidden md:inline">|</span>
                    <div class="flex items-center gap-1.5 text-uch-muted font-medium">
                        <x-icon name="location" class="w-4 h-4 text-uch-coral" />
                        <span>Recojo: <strong class="text-uch-text font-semibold">Barra Rápida - Pabellón A</strong></span>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold tracking-wide {{ $comedorAbierto ? 'bg-uch-teal/10 text-uch-teal' : 'bg-uch-bg text-uch-muted' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $comedorAbierto ? 'bg-uch-teal' : 'bg-uch-muted' }}"></span>
                    Turno {{ $turno }}
                </span>
            </div>
        </section>

        <!-- Menús del día -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 py-6 w-full" id="menus-del-dia">
            <div class="mb-6">
                <span class="text-uch-blue font-bold text-xs uppercase tracking-wider">OFERTA DE HOY</span>
                <h2 class="font-rubik text-headline-md text-uch-navy font-bold tracking-tight">Menús armables de 3 tiempos</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="menu in menusVisibles" :key="menu.id">
                    <article class="relative bg-uch-surface rounded-[18px] p-6 shadow-uch-card hover:shadow-uch-card-hover transition-all duration-300 flex flex-col justify-between overflow-hidden">
                        <div class="absolute top-0 inset-x-0 h-1" :style="{ backgroundColor: colorMenu(menu.tipo) }"></div>
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                      :style="{ backgroundColor: colorMenu(menu.tipo) + '1A', color: colorMenu(menu.tipo) }"
                                      x-text="menu.etiqueta || 'Menú del día'"></span>
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-uch-teal/10 text-uch-teal flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-uch-teal"></span>
                                    <span x-text="racionesMenu(menu)"></span>
                                </span>
                            </div>

                            <h3 class="font-rubik text-headline-sm text-uch-navy font-bold mb-4" x-text="menu.titulo"></h3>

                            <div class="space-y-3 font-open text-body-md text-uch-muted">
                                <template x-for="tiempo in [
                                    { clave: 'entrada', etiqueta: 'Entrada' },
                                    { clave: 'fondo', etiqueta: 'Fondo' },
                                    { clave: 'refresco', etiqueta: 'Refresco' },
                                ]" :key="tiempo.clave">
                                    <div class="flex items-start gap-2.5" x-show="menu.opciones[tiempo.clave].length > 0">
                                        <span class="w-1.5 h-1.5 rounded-full mt-2 shrink-0" :style="{ backgroundColor: colorMenu(menu.tipo) }"></span>
                                        <div class="min-w-0">
                                            <strong class="text-uch-text font-semibold" x-text="tiempo.etiqueta + ': '"></strong>
                                            <span x-text="menu.opciones[tiempo.clave].slice(0, 2).map((opcion) => opcion.nombre).join(' / ')"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-uch-border flex items-center justify-between mt-auto">
                            <div class="flex flex-col">
                                <span class="text-xs text-uch-muted font-medium" x-show="menu.esPrecioVariable">Desde</span>
                                <span class="font-rubik text-price-tag text-uch-navy font-bold leading-tight" x-text="'S/ ' + menu.precioVisible.toFixed(2)"></span>
                            </div>
                            <button type="button"
                                    @click="abrirMenu(menu)"
                                    :disabled="menu.agotado"
                                    class="inline-flex items-center gap-1.5 bg-uch-navy hover:bg-uch-navy-dark active:scale-95 text-white font-open font-semibold px-4 py-2.5 rounded-[10px] transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-text="menu.agotado ? 'Agotado' : 'Armar menú'"></span>
                                <x-icon name="arrow_forward" class="w-4 h-4" x-show="!menu.agotado" />
                            </button>
                        </div>
                    </article>
                </template>
            </div>

            <div x-show="menusVisibles.length === 0" x-cloak class="py-8 text-center">
                <p class="font-rubik text-headline-sm text-uch-navy" x-text="hayFiltrosActivos ? 'Sin menús con estos filtros' : 'Sin menús para tu búsqueda'"></p>
            </div>
        </section>

        <!-- Al paso y Combos -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 py-8 w-full">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-rubik text-headline-md text-uch-navy font-bold tracking-tight">Al paso y Combos</h2>
                    <p class="font-open text-body-sm text-uch-muted mt-0.5">Para acompañar tus clases o recargar energía al instante</p>
                </div>
            </div>

            <div class="flex flex-wrap items-end justify-between gap-4 mt-2 mb-6" @click.outside="filtroAbierto = null">
                <div class="flex flex-col gap-1.5">
                    <span class="text-[11px] font-bold tracking-wider text-uch-muted uppercase">FILTRAR POR</span>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <div class="relative">
                            <button type="button" @click="filtroAbierto = filtroAbierto === 'categoria' ? null : 'categoria'"
                                    :class="categoriaActiva !== 'Todos' ? 'border-uch-blue text-uch-blue' : 'border-uch-border text-uch-navy'"
                                    class="bg-uch-surface border shadow-sm rounded-full px-4 py-2 text-sm font-open font-semibold flex items-center gap-2 hover:border-uch-blue transition-colors">
                                <span class="material-symbols-outlined text-[18px] text-uch-blue">grid_view</span>
                                <span x-text="categoriaActiva === 'Todos' ? 'Categoría' : categoriaActiva"></span>
                                <span class="material-symbols-outlined text-[18px] text-uch-muted">keyboard_arrow_down</span>
                            </button>
                            <div x-show="filtroAbierto === 'categoria'" x-cloak class="absolute left-0 top-full mt-2 w-52 bg-uch-surface rounded-xl shadow-uch-card-hover border border-uch-border p-2 z-30">
                                <template x-for="categoria in categorias" :key="categoria.slug">
                                    <button type="button" @click="categoriaActiva = categoria.nombre; filtroAbierto = null"
                                            :class="categoriaActiva === categoria.nombre ? 'bg-uch-blue/10 text-uch-blue' : 'text-uch-text hover:bg-uch-bg'"
                                            class="w-full text-left px-3 py-2 rounded-lg text-sm font-open font-medium transition-colors"
                                            x-text="categoria.nombre"></button>
                                </template>
                            </div>
                        </div>

                        <div class="relative">
                            <button type="button" @click="filtroAbierto = filtroAbierto === 'precio' ? null : 'precio'"
                                    :class="precioFiltro !== 'todos' ? 'border-uch-blue text-uch-blue' : 'border-uch-border text-uch-navy'"
                                    class="bg-uch-surface border shadow-sm rounded-full px-4 py-2 text-sm font-open font-semibold flex items-center gap-2 hover:border-uch-blue transition-colors">
                                <span x-text="precioFiltro === 'todos' ? 'Precio' : etiquetaPrecio"></span>
                                <span class="material-symbols-outlined text-[18px] text-uch-muted">keyboard_arrow_down</span>
                            </button>
                            <div x-show="filtroAbierto === 'precio'" x-cloak class="absolute left-0 top-full mt-2 w-52 bg-uch-surface rounded-xl shadow-uch-card-hover border border-uch-border p-2 z-30">
                                <template x-for="opcion in [
                                    { valor: 'todos', etiqueta: 'Todos los precios' },
                                    { valor: 'hasta5', etiqueta: 'Hasta S/ 5.00' },
                                    { valor: '5a10', etiqueta: 'S/ 5.00 - S/ 10.00' },
                                    { valor: 'desde10', etiqueta: 'Desde S/ 10.00' },
                                ]" :key="opcion.valor">
                                    <button type="button" @click="precioFiltro = opcion.valor; filtroAbierto = null"
                                            :class="precioFiltro === opcion.valor ? 'bg-uch-blue/10 text-uch-blue' : 'text-uch-text hover:bg-uch-bg'"
                                            class="w-full text-left px-3 py-2 rounded-lg text-sm font-open font-medium transition-colors"
                                            x-text="opcion.etiqueta"></button>
                                </template>
                            </div>
                        </div>

                        <div class="relative">
                            <button type="button" @click="filtroAbierto = filtroAbierto === 'etiqueta' ? null : 'etiqueta'"
                                    :class="etiquetaFiltro !== 'todas' ? 'border-uch-blue text-uch-blue' : 'border-uch-border text-uch-navy'"
                                    class="bg-uch-surface border shadow-sm rounded-full px-4 py-2 text-sm font-open font-semibold flex items-center gap-2 hover:border-uch-blue transition-colors">
                                <span x-text="etiquetaFiltro === 'todas' ? 'Etiqueta' : etiquetaEtiqueta"></span>
                                <span class="material-symbols-outlined text-[18px] text-uch-muted">keyboard_arrow_down</span>
                            </button>
                            <div x-show="filtroAbierto === 'etiqueta'" x-cloak class="absolute left-0 top-full mt-2 w-52 bg-uch-surface rounded-xl shadow-uch-card-hover border border-uch-border p-2 z-30">
                                <template x-for="opcion in [
                                    { valor: 'todas', etiqueta: 'Todas' },
                                    { valor: 'popular', etiqueta: 'Popular' },
                                    { valor: 'rapido', etiqueta: 'Rápido' },
                                    { valor: 'disponible', etiqueta: 'Disponible' },
                                    { valor: 'agotado', etiqueta: 'Agotado' },
                                ]" :key="opcion.valor">
                                    <button type="button" @click="etiquetaFiltro = opcion.valor; filtroAbierto = null"
                                            :class="etiquetaFiltro === opcion.valor ? 'bg-uch-blue/10 text-uch-blue' : 'text-uch-text hover:bg-uch-bg'"
                                            class="w-full text-left px-3 py-2 rounded-lg text-sm font-open font-medium transition-colors"
                                            x-text="opcion.etiqueta"></button>
                                </template>
                            </div>
                        </div>

                        <span class="text-xs text-uch-muted font-medium ml-2">
                            Mostrando <span x-text="menusVisibles.length + filtrados.length"></span> opciones
                        </span>
                        <button type="button" x-show="hayFiltrosActivos" x-cloak @click="limpiarFiltros()"
                                class="text-xs font-bold text-uch-blue hover:text-uch-navy transition-colors">
                            Limpiar
                        </button>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <span class="text-[11px] font-bold tracking-wider text-uch-muted uppercase">ORDENAR POR</span>
                    <div class="relative">
                        <button type="button" @click="filtroAbierto = filtroAbierto === 'orden' ? null : 'orden'"
                                class="w-[240px] bg-uch-surface border border-uch-border rounded-full px-5 py-2 text-sm text-uch-navy font-open font-semibold flex items-center justify-between shadow-sm hover:border-uch-blue transition-colors">
                            <span x-text="etiquetaOrden"></span>
                            <span class="material-symbols-outlined text-[18px] text-uch-muted">keyboard_arrow_down</span>
                        </button>
                        <div x-show="filtroAbierto === 'orden'" x-cloak class="absolute right-0 top-full mt-2 w-60 bg-uch-surface rounded-xl shadow-uch-card-hover border border-uch-border p-2 z-30">
                            <template x-for="opcion in [
                                { valor: 'popular', etiqueta: 'Más pedidos' },
                                { valor: 'precio-asc', etiqueta: 'Precio: menor a mayor' },
                                { valor: 'precio-desc', etiqueta: 'Precio: mayor a menor' },
                                { valor: 'nombre', etiqueta: 'Nombre A-Z' },
                            ]" :key="opcion.valor">
                                <button type="button" @click="ordenFiltro = opcion.valor; filtroAbierto = null"
                                        :class="ordenFiltro === opcion.valor ? 'bg-uch-blue/10 text-uch-blue' : 'text-uch-text hover:bg-uch-bg'"
                                        class="w-full text-left px-3 py-2 rounded-lg text-sm font-open font-medium transition-colors"
                                        x-text="opcion.etiqueta"></button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <template x-for="producto in filtrados" :key="producto.id">
                    <article class="bg-uch-surface rounded-2xl overflow-hidden shadow-uch-card hover:shadow-uch-card-hover transition-all group flex flex-col">
                        <div class="relative h-44 w-full overflow-hidden bg-uch-bg">
                            <template x-if="producto.imagen">
                                <img :src="producto.imagen"
                                     :alt="producto.nombre"
                                     loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </template>
                            <template x-if="!producto.imagen">
                                <div class="w-full h-full flex items-center justify-center text-uch-muted">
                                    <x-icon name="restaurant" class="w-10 h-10" />
                                </div>
                            </template>
                            <span class="absolute top-3 left-3 text-white text-[11px] font-bold px-2.5 py-0.5 rounded-full shadow"
                                  :style="{ backgroundColor: producto.color }"
                                  x-text="producto.categoria"></span>
                            <div x-show="!producto.disponible" x-cloak class="absolute inset-0 bg-uch-navy/45 flex items-center justify-center">
                                <span class="px-3 py-1.5 rounded-full bg-white text-uch-navy text-xs font-bold shadow">Agotado</span>
                            </div>
                        </div>
                        <div class="p-4 flex flex-col justify-between flex-1">
                            <div>
                                <h4 class="font-rubik text-base text-uch-navy font-bold leading-snug line-clamp-1" x-text="producto.nombre"></h4>
                                <p class="font-open text-xs text-uch-muted mt-1 line-clamp-1" x-text="producto.descripcion"></p>
                            </div>
                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-uch-border">
                                <span class="font-rubik text-lg text-uch-navy font-bold" x-text="'S/ ' + producto.precio.toFixed(2)"></span>
                                <button type="button"
                                        @click="agregar(producto)"
                                        :disabled="!producto.disponible"
                                        :class="!producto.disponible ? 'bg-uch-muted cursor-not-allowed' : (feedbackId === producto.id ? 'bg-uch-blue' : 'bg-uch-coral hover:bg-uch-coral-dark')"
                                        class="flex items-center gap-1 text-white font-open font-semibold text-xs px-3.5 py-2 rounded-lg transition-all shadow-sm disabled:opacity-70">
                                    <x-icon name="check" class="w-4 h-4" x-show="feedbackId === producto.id" />
                                    <x-icon name="add" class="w-4 h-4" x-show="feedbackId !== producto.id" />
                                    <span x-text="!producto.disponible ? 'Agotado' : (feedbackId === producto.id ? feedbackMensaje : 'Agregar')"></span>
                                </button>
                            </div>
                        </div>
                    </article>
                </template>
            </div>

            <div x-show="filtrados.length === 0" x-cloak class="flex-col items-center justify-center py-space-xl text-center">
                <div class="w-16 h-16 rounded-full bg-uch-cyan/15 flex items-center justify-center mb-space-sm">
                    <x-icon name="restaurant" class="w-8 h-8 text-uch-blue" />
                </div>
                <p class="font-rubik text-headline-sm text-uch-navy">Sin resultados</p>
            </div>
        </section>

        <!-- Carrito flotante -->
        <button type="button"
                x-show="items.length > 0"
                x-cloak
                @click="abierto = true"
                class="fixed bottom-6 right-6 z-40 flex items-center gap-3 bg-uch-navy text-white pl-5 pr-3 py-3 rounded-full shadow-uch-card-hover hover:bg-uch-navy-dark transition-colors">
            <x-icon name="shopping_bag" class="w-5 h-5" />
            <span class="font-rubik text-sm font-semibold"><span x-text="items.length"></span> items</span>
            <span class="w-px h-5 bg-white/25"></span>
            <span class="font-rubik text-sm font-bold">S/ <span x-text="total().toFixed(2)"></span></span>
        </button>

        <!-- Panel del pedido -->
        <div x-show="abierto"
             x-cloak
             class="fixed inset-0 z-[60] bg-uch-navy/50 flex justify-end"
             @click.self="abierto = false"
             x-transition.opacity>
            <aside class="w-full max-w-sm h-full bg-uch-surface p-space-lg overflow-y-auto shadow-2xl">
                <div class="flex justify-between items-center mb-space-lg">
                    <h2 class="font-rubik text-xl font-bold text-uch-navy">Tu pedido</h2>
                    <button type="button" @click="abierto = false" aria-label="Cerrar pedido" class="text-uch-muted hover:text-uch-navy transition-colors">
                        <x-icon name="close" class="w-5 h-5" />
                    </button>
                </div>

                <div class="flex flex-col gap-space-md">
                    <template x-for="(item, index) in items" :key="item.id">
                        <div class="flex justify-between items-start gap-space-sm border-b border-uch-border pb-space-md">
                            <div class="min-w-0">
                                <p class="font-rubik font-semibold text-sm text-uch-text truncate" x-text="item.nombre"></p>
                                <p class="font-open text-xs text-uch-muted truncate" x-text="item.detalle || ('S/ ' + item.precio.toFixed(2) + ' c/u')"></p>
                                <p class="font-open text-xs text-uch-blue font-semibold">S/ <span x-text="item.precio.toFixed(2)"></span> c/u</p>
                            </div>
                            <div class="flex items-center gap-space-sm shrink-0">
                                <button type="button" @click="cambiarCantidad(index, -1)" aria-label="Quitar una unidad" class="w-7 h-7 rounded-full border border-uch-border text-uch-navy hover:bg-uch-bg">−</button>
                                <span class="font-rubik text-sm font-bold w-4 text-center" x-text="item.cantidad"></span>
                                <button type="button" @click="cambiarCantidad(index, 1)" aria-label="Agregar una unidad" class="w-7 h-7 rounded-full border border-uch-border text-uch-navy hover:bg-uch-bg">+</button>
                                <button type="button" @click="quitar(index)" aria-label="Eliminar producto" class="text-uch-coral hover:text-uch-coral-dark transition-colors">
                                    <x-icon name="delete" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="items.length > 0" x-cloak class="mt-space-lg">
                    <div class="flex justify-between items-center font-rubik text-lg font-bold text-uch-navy">
                        <span>Total</span>
                        <span>S/ <span x-text="total().toFixed(2)"></span></span>
                    </div>
                    <button type="button"
                            @click="irAPagar()"
                            class="w-full mt-space-md bg-uch-coral text-white py-3 rounded-lg font-rubik font-semibold hover:bg-uch-coral-dark active:scale-95 transition-all">
                        Ir a pagar
                    </button>
                </div>
            </aside>
        </div>

        <!-- Modal para armar menú -->
        <div x-show="modalMenuAbierto" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-uch-navy/60 backdrop-blur-sm" @click="modalMenuAbierto = false"></div>
            <div class="relative w-full max-w-lg bg-uch-surface rounded-card shadow-uch-card-hover max-h-[90vh] overflow-y-auto" @click.outside="modalMenuAbierto = false">
                <template x-if="menuSeleccion">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <span class="text-label-md uppercase tracking-widest text-uch-blue" x-text="menuSeleccion.etiqueta || 'Menú del día'"></span>
                                <h2 class="font-rubik text-headline-md text-uch-navy" x-text="menuSeleccion.titulo"></h2>
                            </div>
                            <button type="button" @click="modalMenuAbierto = false" class="p-2 rounded-lg bg-uch-bg text-uch-muted hover:text-uch-navy shrink-0">
                                <x-icon name="close" class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="paso in [
                                { tiempo: 'entrada', titulo: '1. Elige tu entrada' },
                                { tiempo: 'fondo', titulo: '2. Elige tu plato principal' },
                                { tiempo: 'refresco', titulo: '3. Elige tu refresco' },
                            ]" :key="paso.tiempo">
                                <div class="rounded-xl border border-uch-border p-4">
                                    <p class="font-open text-body-sm font-semibold text-uch-navy mb-3" x-text="paso.titulo"></p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <template x-for="opcion in menuSeleccion.opciones[paso.tiempo]" :key="opcion.id">
                                            <label class="flex items-center justify-between gap-2 rounded-lg border px-3 py-2.5 cursor-pointer transition-colors"
                                                   :class="seleccionMenu[paso.tiempo] === opcion.id ? 'border-uch-blue bg-uch-blue/5' : 'border-uch-border hover:bg-uch-bg'">
                                                <span class="flex items-start gap-2 min-w-0">
                                                    <input type="radio" :name="'opcion-' + paso.tiempo" :value="opcion.id" x-model.number="seleccionMenu[paso.tiempo]" class="mt-0.5 text-uch-blue focus:ring-uch-blue">
                                                    <span class="min-w-0">
                                                        <span class="block font-open text-body-sm text-uch-navy font-semibold truncate" x-text="opcion.nombre"></span>
                                                        <span class="block text-[11px] text-uch-muted" x-text="opcion.stock === null ? 'Sin límite' : (opcion.stock > 0 ? opcion.stock + ' raciones' : 'Agotado')"></span>
                                                    </span>
                                                </span>
                                                <span class="font-open text-body-sm font-bold text-uch-navy shrink-0" x-text="opcion.precio !== null ? 'S/ ' + opcion.precio.toFixed(2) : ''"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center justify-between gap-3 mt-5 pt-4 border-t border-uch-border">
                            <div>
                                <span class="block text-[11px] uppercase tracking-wide text-uch-muted" x-text="menuSeleccion.esPrecioVariable ? 'Precio según tu plato' : 'Precio del menú'"></span>
                                <span class="font-rubik text-price-tag text-uch-navy" x-text="'S/ ' + precioMenu.toFixed(2)"></span>
                            </div>
                            <button type="button"
                                    @click="agregarMenu()"
                                    :disabled="!menuListoParaAgregar"
                                    class="inline-flex items-center gap-2 bg-uch-coral text-white px-5 py-2.5 rounded-xl font-semibold text-body-sm hover:bg-uch-coral-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <x-icon name="add_shopping_cart" class="w-5 h-5" />
                                Agregar al pedido
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <footer class="w-full bg-uch-navy text-white relative mt-8">
        <div class="w-full flex h-1.5">
            <div class="w-1/3 bg-uch-cyan"></div>
            <div class="w-1/3 bg-uch-teal"></div>
            <div class="w-1/3 bg-uch-coral"></div>
        </div>
        <div class="max-w-7xl mx-auto px-6 lg:px-12 py-8 flex flex-col md:flex-row items-center justify-between gap-4 text-center md:text-left">
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-center md:justify-start gap-2">
                    <img src="{{ asset('images/uch-logo-blanco.svg') }}" alt="UCH" class="h-7 w-auto object-contain">
                    <span class="px-2 py-0.5 rounded text-[11px] bg-uch-cyan/20 text-uch-cyan font-semibold border border-uch-cyan/30">Campus Los Olivos</span>
                </div>
                <p class="text-white/70 text-xs flex items-center justify-center md:justify-start gap-1.5 mt-1">
                    <x-icon name="schedule" class="w-4 h-4 text-uch-amber" />
                    Horario de atención: Lun - Sáb: 7:30 a.m. - 6:00 p.m. • Pabellón A
                </p>
            </div>
            <div class="text-white/60 text-xs text-center md:text-right">
                <p>© {{ date('Y') }} Universidad de Ciencias y Humanidades (UCH). Todos los derechos reservados.</p>
                <p class="text-[11px] text-white/40 mt-0.5">Comedor Universitario • Servicio Estudiantil</p>
            </div>
        </div>
    </footer>

    <script>
        function catalogoUCH() {
            return {
                productos: [],
                categorias: [],
                menus: [],
                menuHero: null,
                heroTarjetas: [],
                indiceHero: 0,
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
                modalMenuAbierto: false,
                menuSeleccion: null,
                seleccionMenu: { entrada: null, fondo: null, refresco: null },

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

                get fondoSeleccionado() {
                    if (!this.menuSeleccion || !this.seleccionMenu.fondo) {
                        return null;
                    }

                    return this.menuSeleccion.opciones.fondo.find((opcion) => opcion.id === this.seleccionMenu.fondo) || null;
                },

                get precioMenu() {
                    if (!this.menuSeleccion) {
                        return 0;
                    }

                    if (!this.menuSeleccion.esPrecioVariable) {
                        return this.menuSeleccion.precioBase;
                    }

                    return this.fondoSeleccionado?.precio ?? this.menuSeleccion.precioBase;
                },

                get menuListoParaAgregar() {
                    if (!this.menuSeleccion) {
                        return false;
                    }

                    const tiempos = ['entrada', 'fondo', 'refresco'];

                    return tiempos.every((tiempo) => {
                        const id = this.seleccionMenu[tiempo];
                        if (!id) {
                            return false;
                        }

                        const opcion = this.menuSeleccion.opciones[tiempo].find((item) => item.id === id);

                        return opcion && (opcion.stock === null || opcion.stock > 0);
                    });
                },

                init() {
                    this.productos = window.productosMenu || [];
                    this.categorias = window.categoriasMenu || [];
                    this.menus = window.menusMenu || [];
                    this.menuHero = window.menuHero || null;
                    this.heroTarjetas = window.heroTarjetas || [];
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

                cambiarHero(paso) {
                    if (this.heroTarjetas.length === 0) {
                        return;
                    }

                    this.indiceHero = (this.indiceHero + paso + this.heroTarjetas.length) % this.heroTarjetas.length;
                },

                colorMenu(tipo) {
                    return {
                        universitario: '#0066B3',
                        ejecutivo: '#F59E0B',
                        dieta: '#00B4A6',
                    }[tipo] || '#1B3668';
                },

                racionesMenu(menu) {
                    const opciones = menu.opciones.fondo || [];
                    if (opciones.length === 0) {
                        return 'Sin raciones';
                    }

                    const cantidades = opciones
                        .map((opcion) => opcion.stock)
                        .filter((stock) => stock !== null);

                    if (cantidades.length === 0) {
                        return 'Sin límite';
                    }

                    const minimo = Math.min(...cantidades);

                    return minimo > 0 ? minimo + ' raciones disponibles' : 'Agotado';
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
                            detalle: '',
                        });
                    }

                    this.mostrarFeedback(producto.id, '✓ Agregado');
                },

                abrirMenu(menu) {
                    if (menu.agotado) {
                        return;
                    }

                    const primeroDisponible = (opciones) => {
                        const opcion = opciones.find((item) => item.stock === null || item.stock > 0);
                        return opcion ? opcion.id : null;
                    };

                    this.menuSeleccion = menu;
                    this.seleccionMenu = {
                        entrada: primeroDisponible(menu.opciones.entrada),
                        fondo: primeroDisponible(menu.opciones.fondo),
                        refresco: primeroDisponible(menu.opciones.refresco),
                    };
                    this.modalMenuAbierto = true;
                },

                agregarMenu() {
                    if (!this.menuListoParaAgregar) {
                        return;
                    }

                    const menu = this.menuSeleccion;
                    const entrada = menu.opciones.entrada.find((item) => item.id === this.seleccionMenu.entrada);
                    const fondo = this.fondoSeleccionado;
                    const refresco = menu.opciones.refresco.find((item) => item.id === this.seleccionMenu.refresco);
                    const id = `menu-${menu.id}-${entrada.id}-${fondo.id}-${refresco.id}`;
                    const existente = this.items.find((item) => item.id === id);
                    const stock = fondo.stock;

                    if (existente && stock !== null && existente.cantidad >= stock) {
                        this.modalMenuAbierto = false;
                        this.mostrarFeedback(id, 'Sin stock');
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
                            precio: this.precioMenu,
                            cantidad: 1,
                            stock,
                            opciones: {
                                entrada: entrada.id,
                                fondo: fondo.id,
                                refresco: refresco.id,
                            },
                        });
                    }

                    this.modalMenuAbierto = false;
                    this.mostrarFeedback(id, '✓ Menú agregado');
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
