<x-layouts.app>
    <x-admin-nav />

    <div class="mx-auto flex w-full max-w-[1400px] flex-col gap-space-xl px-4 py-6 sm:px-6 lg:px-8"
         x-data="{ modalAbierto: {{ $errors->any() ? 'true' : 'false' }} }"
         @keydown.escape.window="modalAbierto = false">
        <x-flash-message />

        @php
            $estilosTiempo = [
                'entrada' => ['icono' => 'lunch_dining', 'clase' => 'bg-uch-teal/15 text-[#008779]', 'etiqueta' => 'Entrada'],
                'fondo' => ['icono' => 'restaurant', 'clase' => 'bg-uch-blue/10 text-uch-blue', 'etiqueta' => 'Plato principal'],
                'refresco' => ['icono' => 'local_drink', 'clase' => 'bg-uch-cyan/15 text-[#0060A9]', 'etiqueta' => 'Refresco'],
            ];
        @endphp

        <!-- CABECERA DE SECCIÓN -->
        <header class="flex flex-col gap-space-md md:flex-row md:items-center md:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-space-xs text-label-md uppercase tracking-wider text-uch-blue">
                    <span class="material-symbols-outlined text-[18px]">format_list_bulleted</span>
                    <span>Menús y Catálogo / Componentes del menú</span>
                </div>
                <h1 class="font-rubik text-headline-lg-mobile tracking-tight text-uch-navy lg:text-headline-lg">Componentes del menú</h1>
                <p class="text-body-md text-uch-muted">
                    Banco de entradas, platos principales y refrescos.
                </p>
            </div>
            <button type="button" @click="modalAbierto = true"
                    class="inline-flex shrink-0 items-center gap-space-xs rounded-[10px] bg-uch-navy px-5 py-2.5 text-label-lg text-white shadow-sm transition-all hover:bg-uch-blue active:scale-[0.98]">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Nuevo componente
            </button>
        </header>

        <!-- MÉTRICAS DEL BANCO DE OPCIONES -->
        <section class="grid grid-cols-1 gap-space-md sm:grid-cols-2 lg:grid-cols-4">
            <article class="flex items-center justify-between rounded-xl bg-uch-surface p-space-md shadow-sm">
                <div class="space-y-0.5">
                    <p class="text-label-md uppercase tracking-wider text-uch-muted">Total componentes</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">{{ $metricas['total'] }}</p>
                    <span class="text-body-sm font-medium text-uch-blue">Banco de opciones</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-uch-bg text-uch-navy">
                    <span class="material-symbols-outlined text-[24px]">format_list_bulleted</span>
                </div>
            </article>

            <article class="flex items-center justify-between rounded-xl bg-uch-surface p-space-md shadow-sm">
                <div class="space-y-0.5">
                    <p class="text-label-md uppercase tracking-wider text-uch-muted">Activos</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">{{ $metricas['activos'] }}</p>
                    <span class="text-body-sm font-medium text-[#008779]">Disponibles para armar menús</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-uch-teal/15 text-[#008779]">
                    <span class="material-symbols-outlined text-[24px]">check_circle</span>
                </div>
            </article>

            <article class="flex items-center justify-between rounded-xl bg-uch-surface p-space-md shadow-sm">
                <div class="space-y-0.5">
                    <p class="text-label-md uppercase tracking-wider text-uch-muted">En menús del día</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">{{ $metricas['enMenus'] }}</p>
                    <span class="text-body-sm font-medium text-uch-blue">Ya habilitados en algún menú</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-uch-blue/10 text-uch-blue">
                    <span class="material-symbols-outlined text-[24px]">restaurant_menu</span>
                </div>
            </article>

            <article class="flex items-center justify-between rounded-xl bg-uch-surface p-space-md shadow-sm">
                <div class="space-y-0.5">
                    <p class="text-label-md uppercase tracking-wider text-uch-muted">Inactivos</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">{{ $metricas['inactivos'] }}</p>
                    <span class="text-body-sm font-medium text-uch-muted">Fuera del catálogo</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-uch-bg text-uch-muted">
                    <span class="material-symbols-outlined text-[24px]">do_not_disturb_on</span>
                </div>
            </article>
        </section>

        <!-- BÚSQUEDA Y FILTROS -->
        <section class="rounded-card bg-uch-surface p-5 shadow-sm">
            <form method="GET" action="/admin/componentes" class="grid grid-cols-12 items-end gap-space-md">
                <div class="col-span-12 space-y-1 lg:col-span-6">
                    <label for="search-input" class="block text-label-md text-uch-muted">Búsqueda rápida</label>
                    <div class="relative flex items-center">
                        <span class="pointer-events-none absolute left-3.5 text-[20px] text-uch-muted material-symbols-outlined">search</span>
                        <input id="search-input" name="buscar" type="search" value="{{ $buscar }}"
                               placeholder="Buscar por nombre o descripción..." onchange="this.form.submit()"
                               class="w-full rounded-lg bg-uch-bg py-2.5 pl-10 pr-4 text-body-md text-uch-text placeholder:text-uch-muted focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                    </div>
                </div>

                <div class="col-span-12 space-y-1 sm:col-span-6 lg:col-span-3">
                    <label for="tiempo-select" class="block text-label-md text-uch-muted">Tiempo</label>
                    <div class="relative">
                        <select id="tiempo-select" name="tiempo" onchange="this.form.submit()"
                                class="w-full cursor-pointer appearance-none rounded-lg bg-uch-bg py-2.5 pl-3.5 pr-9 text-body-md text-uch-text focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                            <option value="all">Todos los tiempos</option>
                            <option value="entrada" @selected($tiempoFiltro === 'entrada')>Entrada</option>
                            <option value="fondo" @selected($tiempoFiltro === 'fondo')>Plato principal</option>
                            <option value="refresco" @selected($tiempoFiltro === 'refresco')>Refresco</option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[20px] text-uch-muted material-symbols-outlined">expand_more</span>
                    </div>
                </div>

                <div class="col-span-12 space-y-1 sm:col-span-6 lg:col-span-3">
                    <label for="estado-select" class="block text-label-md text-uch-muted">Estado</label>
                    <div class="relative">
                        <select id="estado-select" name="estado" onchange="this.form.submit()"
                                class="w-full cursor-pointer appearance-none rounded-lg bg-uch-bg py-2.5 pl-3.5 pr-9 text-body-md text-uch-text focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                            <option value="all">Todos los estados</option>
                            <option value="activo" @selected($estadoFiltro === 'activo')>Activo</option>
                            <option value="inactivo" @selected($estadoFiltro === 'inactivo')>Inactivo</option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[20px] text-uch-muted material-symbols-outlined">expand_more</span>
                    </div>
                </div>

                <button type="submit" class="sr-only">Filtrar componentes</button>
            </form>

            @if ($buscar !== '' || $tiempoFiltro !== '' || $estadoFiltro !== 'all')
                <div class="mt-3 flex justify-end">
                    <a href="/admin/componentes" class="text-label-md font-semibold text-uch-blue hover:underline">Limpiar filtros</a>
                </div>
            @endif
        </section>

        <!-- TABLA DE COMPONENTES -->
        <section class="overflow-hidden rounded-card bg-uch-surface shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] border-collapse text-left text-body-md">
                    <thead>
                        <tr class="bg-[#E8F1FA] font-rubik text-body-md text-uch-navy">
                            <th scope="col" class="px-4 py-3.5 font-bold">Componente</th>
                            <th scope="col" class="px-4 py-3.5 font-bold">Tiempo</th>
                            <th scope="col" class="px-4 py-3.5 font-bold">En menús</th>
                            <th scope="col" class="px-4 py-3.5 font-bold">Estado</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-bold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-uch-border">
                        @forelse ($componentes as $componente)
                            @php $estiloTiempo = $estilosTiempo[$componente->tiempo]; @endphp
                            <tr class="transition-colors hover:bg-uch-bg/60">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-space-sm">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $estiloTiempo['clase'] }}">
                                            <span class="material-symbols-outlined shrink-0 text-[20px] leading-none">{{ $estiloTiempo['icono'] }}</span>
                                        </span>
                                        <div class="flex min-w-0 flex-col">
                                            <span class="text-body-lg font-bold text-uch-navy">{{ $componente->nombre }}</span>
                                            <span class="text-body-sm text-uch-muted">{{ $componente->descripcion ?: 'Sin descripción' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $estiloTiempo['clase'] }}">
                                        {{ $estiloTiempo['etiqueta'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-rubik text-price-tag font-bold text-uch-navy">{{ $componente->opciones_habilitadas_count }}</span>
                                    <span class="text-body-sm text-uch-muted">{{ $componente->opciones_habilitadas_count === 1 ? 'menú' : 'menús' }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $componente->activo_catalogo ? 'bg-uch-teal/15 text-[#008779]' : 'bg-uch-bg text-uch-muted' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $componente->activo_catalogo ? 'bg-uch-teal' : 'bg-uch-muted' }}"></span>
                                        {{ $componente->activo_catalogo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="inline-flex items-center gap-space-xs">
                                        <a href="/admin/componentes/{{ $componente->id }}/editar"
                                           class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-label-md font-semibold text-uch-blue transition-colors hover:bg-uch-blue/10"
                                           title="Editar componente">
                                            <span class="material-symbols-outlined text-[16px]">edit</span>
                                            Editar
                                        </a>
                                        <form method="POST" action="/admin/componentes/{{ $componente->id }}/toggle">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-lg px-3 py-1.5 text-label-md text-uch-muted transition-colors hover:bg-uch-bg"
                                                    title="{{ $componente->activo_catalogo ? 'Desactivar componente' : 'Activar componente' }}">
                                                {{ $componente->activo_catalogo ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-body-sm text-uch-muted">
                                    No hay componentes para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <footer class="flex flex-col items-center justify-between gap-space-sm px-5 py-4 sm:flex-row">
                <p class="text-body-md text-uch-muted">
                    Mostrando <span class="font-semibold text-uch-text">{{ $componentes->firstItem() ?? 0 }}</span>
                    a <span class="font-semibold text-uch-text">{{ $componentes->lastItem() ?? 0 }}</span>
                    de <span class="font-semibold text-uch-text">{{ $componentes->total() }}</span> componentes
                </p>
                {{ $componentes->onEachSide(1)->links('admin.productos.pagination') }}
            </footer>
        </section>

        <!-- MODAL FLOTANTE: NUEVO COMPONENTE -->
        <div x-show="modalAbierto" x-cloak
             class="fixed inset-0 z-[70] flex items-center justify-center bg-uch-navy/60 p-space-md backdrop-blur-sm"
             @click="modalAbierto = false">
            <div @click.stop
                 role="dialog" aria-modal="true" aria-labelledby="tituloModalComponente"
                 class="relative flex max-h-[90vh] w-full max-w-[560px] flex-col overflow-hidden rounded-[16px] bg-uch-surface shadow-2xl">
                <div class="h-[6px] w-full shrink-0 bg-gradient-to-r from-uch-navy via-uch-blue to-uch-cyan"></div>

                <div class="flex items-start justify-between gap-space-md px-space-lg pb-space-sm pt-space-lg">
                    <div class="flex flex-col gap-1">
                        <span class="font-rubik text-label-md font-bold uppercase tracking-wider text-uch-blue">Banco de componentes</span>
                        <h2 id="tituloModalComponente" class="font-rubik text-headline-sm font-bold text-uch-navy">Nuevo componente</h2>
                        <p class="text-body-md text-uch-muted">Nueva opción para los menús del día.</p>
                    </div>
                    <button type="button" @click="modalAbierto = false" aria-label="Cerrar ventana modal"
                            class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-uch-muted transition-colors hover:bg-uch-border hover:text-uch-navy">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <div class="overflow-y-auto">
                    @include('admin.componentes._formulario', [
                        'accion' => '/admin/componentes',
                        'metodo' => 'POST',
                        'componente' => null,
                        'boton' => 'Guardar componente',
                        'modal' => true,
                    ])
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
