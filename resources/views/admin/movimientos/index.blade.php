<x-layouts.app>
    <x-admin-nav />

    <div class="mx-auto flex w-full max-w-[1400px] flex-col gap-space-xl px-4 py-6 sm:px-6 lg:px-8">
        <x-flash-message />

        @php
            $estilosOperacion = [
                'ingreso' => ['icono' => 'arrow_downward', 'clase' => 'bg-uch-teal/15 text-[#008779]', 'etiqueta' => 'Ingreso'],
                'salida' => ['icono' => 'arrow_upward', 'clase' => 'bg-uch-coral/10 text-uch-coral-dark', 'etiqueta' => 'Salida'],
                'venta' => ['icono' => 'shopping_cart', 'clase' => 'bg-uch-blue/10 text-uch-blue', 'etiqueta' => 'Venta'],
            ];
        @endphp

        <!-- CABECERA DE SECCIÓN -->
        <header class="flex flex-col gap-space-md md:flex-row md:items-center md:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-space-xs text-label-md uppercase tracking-wider text-uch-blue">
                    <span class="material-symbols-outlined text-[18px]">history</span>
                    <span>Control y Operación / Historial de movimientos</span>
                </div>
                <h1 class="font-rubik text-headline-lg-mobile tracking-tight text-uch-navy lg:text-headline-lg">Historial de movimientos</h1>
                <p class="text-body-md text-uch-muted">
                    Entradas, salidas y ventas de productos y raciones.
                </p>
            </div>
            <a href="/admin/inventario"
               class="inline-flex shrink-0 items-center gap-space-xs rounded-[10px] border border-uch-border bg-uch-surface px-5 py-2.5 text-label-lg font-semibold text-uch-navy transition-all hover:bg-uch-bg hover:border-uch-blue">
                <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                Ir a inventario
            </a>
        </header>

        <!-- MÉTRICAS DEL KARDEX -->
        <section class="grid grid-cols-1 gap-space-md sm:grid-cols-2 lg:grid-cols-4">
            <article class="flex items-center justify-between rounded-xl bg-uch-surface p-space-md shadow-sm">
                <div class="space-y-0.5">
                    <p class="text-label-md uppercase tracking-wider text-uch-muted">Total movimientos</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">{{ $metricas['total'] }}</p>
                    <span class="text-body-sm font-medium text-uch-blue">Registros en el kardex</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-uch-bg text-uch-navy">
                    <span class="material-symbols-outlined text-[24px]">history</span>
                </div>
            </article>

            <article class="flex items-center justify-between rounded-xl bg-uch-surface p-space-md shadow-sm">
                <div class="space-y-0.5">
                    <p class="text-label-md uppercase tracking-wider text-uch-muted">Ingresos</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">{{ $metricas['ingresos'] }}</p>
                    <span class="text-body-sm font-medium text-[#008779]">Reposiciones y entradas</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-uch-teal/15 text-[#008779]">
                    <span class="material-symbols-outlined text-[24px]">arrow_downward</span>
                </div>
            </article>

            <article class="flex items-center justify-between rounded-xl bg-uch-surface p-space-md shadow-sm">
                <div class="space-y-0.5">
                    <p class="text-label-md uppercase tracking-wider text-uch-muted">Salidas y ventas</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">{{ $metricas['salidas'] }}</p>
                    <span class="text-body-sm font-medium text-uch-coral-dark">Consumo y mermas</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-uch-coral/10 text-uch-coral-dark">
                    <span class="material-symbols-outlined text-[24px]">arrow_upward</span>
                </div>
            </article>

            <article class="flex items-center justify-between rounded-xl bg-uch-surface p-space-md shadow-sm">
                <div class="space-y-0.5">
                    <p class="text-label-md uppercase tracking-wider text-uch-muted">Registrados hoy</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">{{ $metricas['hoy'] }}</p>
                    <span class="text-body-sm font-medium text-uch-muted">Auditoría del día</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-uch-blue/10 text-uch-blue">
                    <span class="material-symbols-outlined text-[24px]">today</span>
                </div>
            </article>
        </section>

        <!-- BÚSQUEDA Y FILTROS -->
        <section class="rounded-card bg-uch-surface p-5 shadow-sm">
            <form method="GET" action="/admin/movimientos" class="grid grid-cols-12 items-end gap-space-md">
                <div class="col-span-12 space-y-1 lg:col-span-4">
                    <label for="search-input" class="block text-label-md text-uch-muted">Búsqueda rápida</label>
                    <div class="relative flex items-center">
                        <span class="pointer-events-none absolute left-3.5 text-[20px] text-uch-muted material-symbols-outlined">search</span>
                        <input id="search-input" name="buscar" type="search" value="{{ $buscar }}"
                               placeholder="Buscar por ítem, motivo o comentario..." onchange="this.form.submit()"
                               class="w-full rounded-lg bg-uch-bg py-2.5 pl-10 pr-4 text-body-md text-uch-text placeholder:text-uch-muted focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                    </div>
                </div>

                <div class="col-span-12 space-y-1 sm:col-span-6 lg:col-span-2">
                    <label for="operacion-select" class="block text-label-md text-uch-muted">Operación</label>
                    <div class="relative">
                        <select id="operacion-select" name="operacion" onchange="this.form.submit()"
                                class="w-full cursor-pointer appearance-none rounded-lg bg-uch-bg py-2.5 pl-3.5 pr-9 text-body-md text-uch-text focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                            <option value="all">Todas</option>
                            <option value="ingreso" @selected($operacionFiltro === 'ingreso')>Ingreso</option>
                            <option value="salida" @selected($operacionFiltro === 'salida')>Salida</option>
                            <option value="venta" @selected($operacionFiltro === 'venta')>Venta</option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[20px] text-uch-muted material-symbols-outlined">expand_more</span>
                    </div>
                </div>

                <div class="col-span-12 space-y-1 sm:col-span-6 lg:col-span-3">
                    <label for="motivo-select" class="block text-label-md text-uch-muted">Motivo</label>
                    <div class="relative">
                        <select id="motivo-select" name="motivo" onchange="this.form.submit()"
                                class="w-full cursor-pointer appearance-none rounded-lg bg-uch-bg py-2.5 pl-3.5 pr-9 text-body-md text-uch-text focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                            <option value="all">Todos los motivos</option>
                            @foreach ($motivos as $motivo)
                                <option value="{{ $motivo }}" @selected($motivoFiltro === $motivo)>{{ $motivo }}</option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[20px] text-uch-muted material-symbols-outlined">expand_more</span>
                    </div>
                </div>

                <div class="col-span-12 space-y-1 sm:col-span-6 lg:col-span-3">
                    <label for="tipo-select" class="block text-label-md text-uch-muted">Tipo de ítem</label>
                    <div class="relative">
                        <select id="tipo-select" name="tipo" onchange="this.form.submit()"
                                class="w-full cursor-pointer appearance-none rounded-lg bg-uch-bg py-2.5 pl-3.5 pr-9 text-body-md text-uch-text focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                            <option value="all">Todos los ítems</option>
                            <option value="producto" @selected($tipoFiltro === 'producto')>Producto</option>
                            <option value="opcion_menu" @selected($tipoFiltro === 'opcion_menu')>Ración de menú</option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[20px] text-uch-muted material-symbols-outlined">expand_more</span>
                    </div>
                </div>

                <button type="submit" class="sr-only">Filtrar movimientos</button>
            </form>

            @if ($buscar !== '' || $operacionFiltro !== '' || $motivoFiltro !== '' || $tipoFiltro !== '')
                <div class="mt-3 flex justify-end">
                    <a href="/admin/movimientos" class="text-label-md font-semibold text-uch-blue hover:underline">Limpiar filtros</a>
                </div>
            @endif
        </section>

        <!-- TABLA DE MOVIMIENTOS -->
        <section class="overflow-hidden rounded-card bg-uch-surface shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] border-collapse text-left text-body-md">
                    <thead>
                        <tr class="bg-[#E8F1FA] font-rubik text-body-md text-uch-navy">
                            <th scope="col" class="px-4 py-3.5 font-bold">Fecha</th>
                            <th scope="col" class="px-4 py-3.5 font-bold">Ítem</th>
                            <th scope="col" class="px-4 py-3.5 font-bold">Operación</th>
                            <th scope="col" class="px-4 py-3.5 font-bold">Motivo</th>
                            <th scope="col" class="px-4 py-3.5 font-bold">Variación</th>
                            <th scope="col" class="px-4 py-3.5 font-bold">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-uch-border">
                        @forelse ($movimientos as $movimiento)
                            @php
                                $estilo = $estilosOperacion[$movimiento->operacion] ?? $estilosOperacion['salida'];
                            @endphp
                            <tr class="transition-colors hover:bg-uch-bg/60">
                                <td class="whitespace-nowrap px-4 py-4 text-body-sm text-uch-muted">
                                    {{ $movimiento->created_at?->format('d/m H:i') }}
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-body-md font-bold text-uch-navy">{{ $movimiento->nombre_item }}</p>
                                    <p class="max-w-xs truncate text-body-sm text-uch-muted" title="{{ $movimiento->comentario }}">
                                        {{ $movimiento->comentario }}
                                    </p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $estilo['clase'] }}">
                                        <span class="material-symbols-outlined text-[14px]">{{ $estilo['icono'] }}</span>
                                        {{ $estilo['etiqueta'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full bg-uch-bg px-2.5 py-1 text-[11px] font-semibold text-uch-muted">
                                        {{ $movimiento->motivo_categoria }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-body-sm text-uch-navy">
                                    {{ $movimiento->stock_anterior }} → <strong>{{ $movimiento->stock_nuevo }}</strong>
                                    <span class="{{ $movimiento->operacion === 'ingreso' ? 'font-semibold text-[#008779]' : 'font-semibold text-uch-coral-dark' }}">
                                        ({{ $movimiento->operacion === 'ingreso' ? '+' : '-' }}{{ $movimiento->cantidad }})
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-body-sm text-uch-muted">
                                    {{ $movimiento->usuario->name ?? 'Sistema' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-body-sm text-uch-muted">
                                    No hay movimientos para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <footer class="flex flex-col items-center justify-between gap-space-sm px-5 py-4 sm:flex-row">
                <p class="text-body-md text-uch-muted">
                    Mostrando <span class="font-semibold text-uch-text">{{ $movimientos->firstItem() ?? 0 }}</span>
                    a <span class="font-semibold text-uch-text">{{ $movimientos->lastItem() ?? 0 }}</span>
                    de <span class="font-semibold text-uch-text">{{ $movimientos->total() }}</span> movimientos
                </p>
                {{ $movimientos->onEachSide(1)->links('admin.productos.pagination') }}
            </footer>
        </section>
    </div>
</x-layouts.app>
