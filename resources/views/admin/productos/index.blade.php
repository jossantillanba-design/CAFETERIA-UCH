<x-layouts.app>
    <x-admin-nav />

    <div class="mx-auto flex w-full max-w-[1400px] flex-col gap-space-xl px-4 py-6 sm:px-6 lg:px-8">
        <x-flash-message />

        @php
            $estilosDisponibilidad = [
                'disponible' => ['etiqueta' => 'bg-uch-teal/15 text-[#008779]', 'punto' => 'bg-uch-teal', 'texto' => 'Disponible'],
                'stock-bajo' => ['etiqueta' => 'bg-uch-amber/15 text-[#D97706]', 'punto' => 'bg-uch-amber', 'texto' => 'Stock bajo'],
                'agotado' => ['etiqueta' => 'bg-uch-coral/10 text-uch-coral-dark', 'punto' => 'bg-uch-coral', 'texto' => 'Agotado'],
                'inactivo' => ['etiqueta' => 'bg-uch-bg text-uch-muted', 'punto' => 'bg-uch-muted', 'texto' => 'Inactivo'],
            ];
        @endphp

        <!-- CABECERA DE SECCIÓN -->
        <header class="flex flex-col gap-space-md md:flex-row md:items-center md:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-space-xs text-label-md uppercase tracking-wider text-uch-blue">
                    <span class="material-symbols-outlined text-[18px]">storefront</span>
                    <span>Menús y Catálogo / Productos</span>
                </div>
                <h1 class="font-rubik text-headline-lg-mobile tracking-tight text-uch-navy lg:text-headline-lg">Productos</h1>
                <p class="text-body-md text-uch-muted">
                    Administra los adicionales, bebidas, precios, etiquetas y disponibilidad en tienda.
                </p>
            </div>
            <a href="/admin/productos/crear"
               class="inline-flex shrink-0 items-center gap-space-xs rounded-[10px] bg-uch-navy px-5 py-2.5 text-label-lg text-white shadow-sm transition-all hover:bg-uch-blue active:scale-[0.98]">
                <span class="material-symbols-outlined text-[20px]">add</span>
                + Nuevo producto
            </a>
        </header>

        <!-- MÉTRICAS RÁPIDAS DEL CATÁLOGO -->
        <section class="grid grid-cols-1 gap-space-md sm:grid-cols-2 lg:grid-cols-4">
            <article class="flex items-center justify-between rounded-xl bg-uch-surface p-space-md shadow-sm">
                <div class="space-y-0.5">
                    <p class="text-label-md uppercase tracking-wider text-uch-muted">Total productos</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">{{ $metricas['total'] }}</p>
                    <span class="text-body-sm font-medium text-uch-blue">{{ $metricas['categoriasActivas'] }} categorías activas</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-uch-bg text-uch-navy">
                    <span class="material-symbols-outlined text-[24px]">inventory_2</span>
                </div>
            </article>

            <article class="flex items-center justify-between rounded-xl bg-uch-surface p-space-md shadow-sm">
                <div class="space-y-0.5">
                    <p class="text-label-md uppercase tracking-wider text-uch-muted">Disponibles</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">{{ $metricas['disponibles'] }}</p>
                    <span class="text-body-sm font-medium text-[#008779]">Listos para pedido</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-uch-teal/15 text-[#008779]">
                    <span class="material-symbols-outlined text-[24px]">check_circle</span>
                </div>
            </article>

            <article class="flex items-center justify-between rounded-xl bg-uch-surface p-space-md shadow-sm">
                <div class="space-y-0.5">
                    <p class="text-label-md uppercase tracking-wider text-uch-muted">Stock crítico</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">{{ $metricas['stockCritico'] }}</p>
                    <span class="text-body-sm font-medium text-[#D97706]">Requiere reposición</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-uch-amber/15 text-[#D97706]">
                    <span class="material-symbols-outlined text-[24px]">warning</span>
                </div>
            </article>

            <article class="flex items-center justify-between rounded-xl bg-uch-surface p-space-md shadow-sm">
                <div class="space-y-0.5">
                    <p class="text-label-md uppercase tracking-wider text-uch-muted">Agotados / Pausados</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">{{ $metricas['agotadosPausados'] }}</p>
                    <span class="text-body-sm font-medium text-uch-muted">Sin venta temporal</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-uch-bg text-uch-muted">
                    <span class="material-symbols-outlined text-[24px]">do_not_disturb_on</span>
                </div>
            </article>
        </section>

        <!-- BÚSQUEDA Y FILTROS -->
        <section class="rounded-card bg-uch-surface p-5 shadow-sm">
            <form method="GET" action="/admin/productos" class="grid grid-cols-12 items-end gap-space-md">
                <div class="col-span-12 space-y-1 lg:col-span-6">
                    <label for="search-input" class="block text-label-md text-uch-muted">Búsqueda rápida</label>
                    <div class="relative flex items-center">
                        <span class="pointer-events-none absolute left-3.5 text-[20px] text-uch-muted material-symbols-outlined">search</span>
                        <input id="search-input" name="buscar" type="search" value="{{ $buscar }}"
                               placeholder="Buscar por nombre o SKU..." onchange="this.form.submit()"
                               class="w-full rounded-lg bg-uch-bg py-2.5 pl-10 pr-4 text-body-md text-uch-text placeholder:text-uch-muted focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                    </div>
                </div>

                <div class="col-span-12 space-y-1 sm:col-span-6 lg:col-span-3">
                    <label for="category-select" class="block text-label-md text-uch-muted">Categoría</label>
                    <div class="relative">
                        <select id="category-select" name="categoria" onchange="this.form.submit()"
                                class="w-full cursor-pointer appearance-none rounded-lg bg-uch-bg py-2.5 pl-3.5 pr-9 text-body-md text-uch-text focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                            <option value="all">Todas las categorías</option>
                            @foreach ($categoriasFiltro as $categoria)
                                <option value="{{ $categoria->slug }}" @selected($categoriaFiltro === $categoria->slug)>{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[20px] text-uch-muted material-symbols-outlined">expand_more</span>
                    </div>
                </div>

                <div class="col-span-12 space-y-1 sm:col-span-6 lg:col-span-3">
                    <label for="status-select" class="block text-label-md text-uch-muted">Estado</label>
                    <div class="relative">
                        <select id="status-select" name="estado" onchange="this.form.submit()"
                                class="w-full cursor-pointer appearance-none rounded-lg bg-uch-bg py-2.5 pl-3.5 pr-9 text-body-md text-uch-text focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                            <option value="all">Todos los estados</option>
                            <option value="disponible" @selected($estadoFiltro === 'disponible')>Disponible</option>
                            <option value="stock-bajo" @selected($estadoFiltro === 'stock-bajo')>Stock bajo</option>
                            <option value="agotado" @selected($estadoFiltro === 'agotado')>Agotado</option>
                            <option value="inactivo" @selected($estadoFiltro === 'inactivo')>Inactivo</option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[20px] text-uch-muted material-symbols-outlined">expand_more</span>
                    </div>
                </div>

                <button type="submit" class="sr-only">Filtrar productos</button>
            </form>

            @if ($buscar !== '' || $categoriaFiltro !== '' || $estadoFiltro !== 'all')
                <div class="mt-3 flex justify-end">
                    <a href="/admin/productos" class="text-label-md font-semibold text-uch-blue hover:underline">Limpiar filtros</a>
                </div>
            @endif
        </section>

        <!-- TABLA DE PRODUCTOS -->
        <section class="overflow-hidden rounded-card bg-uch-surface shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[840px] border-collapse text-left text-body-md">
                    <thead>
                        <tr class="bg-[#E8F1FA] font-rubik text-body-md text-uch-navy">
                            <th scope="col" class="px-4 py-3.5 font-bold">SKU</th>
                            <th scope="col" class="px-4 py-3.5 font-bold">Producto</th>
                            <th scope="col" class="px-4 py-3.5 font-bold">Categoría</th>
                            <th scope="col" class="px-4 py-3.5 font-bold">Precio</th>
                            <th scope="col" class="px-4 py-3.5 font-bold">Stock</th>
                            <th scope="col" class="px-4 py-3.5 font-bold">Disponibilidad</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-bold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-uch-border">
                        @forelse ($productos as $producto)
                            @php
                                $claveDisponibilidad = ! $producto->activo
                                    ? 'inactivo'
                                    : ($producto->stock_actual <= 0
                                        ? 'agotado'
                                        : ($producto->stock_actual <= $producto->stock_minimo ? 'stock-bajo' : 'disponible'));

                                $estiloDisponibilidad = $estilosDisponibilidad[$claveDisponibilidad];
                                $minimoStock = max(1, (int) $producto->stock_minimo);
                                $porcentajeStock = (int) min(100, round(((int) $producto->stock_actual / $minimoStock) * 100));
                                $barraStock = $porcentajeStock === 0
                                    ? 'bg-uch-coral'
                                    : ($claveDisponibilidad === 'stock-bajo' ? 'bg-uch-amber' : 'bg-uch-blue');
                            @endphp
                            <tr class="transition-colors hover:bg-uch-bg/60">
                                <td class="px-4 py-4 font-mono text-[13px] text-uch-muted">
                                    <span class="rounded bg-uch-bg px-2 py-1 text-uch-text">{{ $producto->sku }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-space-sm">
                                        @if ($producto->imagen_url)
                                            <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}"
                                                 class="h-10 w-10 shrink-0 rounded-lg bg-uch-bg object-cover shadow-sm">
                                        @else
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-uch-bg text-uch-muted">
                                                <span class="material-symbols-outlined text-[20px]">restaurant</span>
                                            </span>
                                        @endif
                                        <div class="flex min-w-0 flex-col">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span class="text-body-lg font-bold text-uch-navy">{{ $producto->nombre }}</span>
                                                @if ($producto->es_nuevo)
                                                    <span class="rounded-full bg-uch-cyan/15 px-2 py-0.5 text-[11px] font-semibold text-[#0060A9]">Nuevo</span>
                                                @endif
                                                @if ($producto->es_popular)
                                                    <span class="rounded-full bg-uch-coral/10 px-2 py-0.5 text-[11px] font-semibold text-uch-coral-dark">Popular</span>
                                                @endif
                                                @if ($producto->destacado)
                                                    <span class="rounded-full bg-uch-amber/15 px-2 py-0.5 text-[11px] font-semibold text-[#D97706]">Destacado</span>
                                                @endif
                                            </div>
                                            <span class="text-body-sm text-uch-muted">{{ $producto->descripcion ?: 'Producto del comedor UCH' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 font-medium text-uch-muted">{{ $producto->categoria->nombre ?? 'Sin categoría' }}</td>
                                <td class="px-4 py-4 font-rubik text-price-tag font-bold text-uch-navy">S/ {{ number_format((float) $producto->precio, 2) }}</td>
                                <td class="min-w-[130px] px-4 py-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between text-[11px] font-semibold {{ $claveDisponibilidad === 'agotado' ? 'text-uch-coral-dark' : ($claveDisponibilidad === 'stock-bajo' ? 'text-[#D97706]' : 'text-uch-muted') }}">
                                            <span>{{ $producto->stock_actual }} / mín. {{ $producto->stock_minimo }}</span>
                                            <span>{{ $porcentajeStock }}%</span>
                                        </div>
                                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-uch-border">
                                            <div class="h-full rounded-full {{ $barraStock }}" style="width: {{ $porcentajeStock }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $estiloDisponibilidad['etiqueta'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $estiloDisponibilidad['punto'] }}"></span>
                                        {{ $estiloDisponibilidad['texto'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="inline-flex items-center gap-space-xs">
                                        <a href="/admin/productos/{{ $producto->id }}/editar"
                                           class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-label-md font-semibold text-uch-blue transition-colors hover:bg-uch-blue/10"
                                           title="Editar producto">
                                            <span class="material-symbols-outlined text-[16px]">edit</span>
                                            Editar
                                        </a>
                                        <form method="POST" action="/admin/productos/{{ $producto->id }}/toggle">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-lg px-3 py-1.5 text-label-md text-uch-muted transition-colors hover:bg-uch-bg"
                                                    title="{{ $producto->activo ? 'Desactivar producto' : 'Activar producto' }}">
                                                {{ $producto->activo ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-body-sm text-uch-muted">
                                    No hay productos para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <footer class="flex flex-col items-center justify-between gap-space-sm px-5 py-4 sm:flex-row">
                <p class="text-body-md text-uch-muted">
                    Mostrando <span class="font-semibold text-uch-text">{{ $productos->firstItem() ?? 0 }}</span>
                    a <span class="font-semibold text-uch-text">{{ $productos->lastItem() ?? 0 }}</span>
                    de <span class="font-semibold text-uch-text">{{ $productos->total() }}</span> productos
                </p>
                {{ $productos->onEachSide(1)->links('admin.productos.pagination') }}
            </footer>
        </section>
    </div>
</x-layouts.app>
