<x-layouts.app>
    <x-admin-nav />

    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
         x-data="{ pestana: '{{ $menus->first()?->tipo_menu ?? 'universitario' }}' }">
        <x-flash-message />

        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <span class="text-label-md font-bold uppercase tracking-[0.18em] text-uch-blue">Oferta del día</span>
                <h1 class="mt-1 font-rubik text-headline-lg-mobile lg:text-headline-lg text-uch-navy tracking-tight">
                    Menús del día
                </h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="/admin/componentes"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-uch-blue bg-uch-surface px-3.5 py-2 text-label-lg font-semibold text-uch-blue shadow-sm hover:bg-uch-bg transition-colors">
                    <span class="material-symbols-outlined text-[18px]">tune</span>
                    Gestionar componentes
                </a>
                <button type="submit" :form="'menu-form-' + pestana"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-uch-coral px-4 py-2 text-label-lg font-semibold text-white shadow-sm hover:bg-uch-coral-dark active:scale-[0.98] transition-all">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Guardar cambios
                </button>
            </div>
        </header>

        <nav class="flex items-center gap-2 p-1 rounded-xl bg-uch-border/50 overflow-x-auto no-scrollbar mb-4"
             aria-label="Modalidades de menú">
            @foreach ($menus as $menu)
                <button type="button"
                        @click="pestana = '{{ $menu->tipo_menu }}'"
                        :class="pestana === '{{ $menu->tipo_menu }}'
                            ? 'bg-uch-navy text-white shadow-sm'
                            : 'bg-uch-surface text-uch-navy hover:bg-uch-bg'"
                        class="shrink-0 inline-flex items-center gap-2 rounded-lg px-3.5 py-1.5 text-label-lg font-semibold transition-all">
                    <span>{{ $menu->titulo }}</span>
                    <span class="rounded-full px-2 py-0.5 text-label-md font-semibold
                        {{ $menu->activo ? 'bg-uch-teal/20 text-uch-teal' : 'bg-uch-bg text-uch-muted' }}">
                        {{ $menu->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </button>
            @endforeach
        </nav>

        @foreach ($menus as $menu)
            @php
                $opcionesPorComponente = $menu->opciones->keyBy('componente_id');
                $seleccionadosGuardados = array_map(
                    'intval',
                    old('habilitados', $menu->opciones->where('habilitado', true)->pluck('componente_id')->all()),
                );

                $estadoComponentes = [];
                $nombresComponentes = [];
                foreach ($componentes as $tiempo => $listaComponentes) {
                    $estadoComponentes[$tiempo] = [];
                    $nombresComponentes[$tiempo] = [];
                    foreach ($listaComponentes as $componente) {
                        $estadoComponentes[$tiempo][$componente->id] = in_array($componente->id, $seleccionadosGuardados, true);
                        $nombresComponentes[$tiempo][$componente->id] = $componente->nombre;
                    }
                }

                $titulosTiempo = [
                    'entrada' => ['1. Entradas', 'Añadir entrada', 'bg-uch-blue/10 text-uch-blue'],
                    'fondo' => ['2. Platos principales', 'Añadir plato principal', 'bg-uch-teal/15 text-uch-teal'],
                    'refresco' => ['3. Refrescos', 'Añadir refresco', 'bg-uch-cyan/15 text-[#0060A9]'],
                ];

                $capacidadTotal = $menu->opciones->where('habilitado', true)->sum('stock_raciones');
            @endphp

            <section x-show="pestana === '{{ $menu->tipo_menu }}'" x-cloak>
                <form id="menu-form-{{ $menu->tipo_menu }}" method="POST" action="/admin/menus/{{ $menu->id }}"
                      class="grid grid-cols-1 gap-4 lg:grid-cols-12 items-start">
                    @csrf
                    @method('PUT')

                    {{-- Columna izquierda: datos de la modalidad --}}
                    <div class="lg:col-span-4 flex flex-col gap-3.5 rounded-card border border-uch-border border-t-4 border-t-uch-blue bg-uch-surface p-5 shadow-uch-card">
                        <div class="flex items-center justify-between gap-3 border-b border-uch-border/70 pb-3">
                            <div class="flex min-w-0 items-center gap-2">
                                <span class="material-symbols-outlined text-[22px] text-uch-blue">restaurant</span>
                                <h2 class="truncate font-rubik text-headline-sm text-uch-navy">{{ $menu->titulo }}</h2>
                            </div>

                            <label class="flex shrink-0 cursor-pointer items-center gap-2">
                                <span class="text-label-md font-semibold text-uch-muted">Activo hoy</span>
                                <span class="relative inline-flex items-center">
                                    <input type="hidden" name="activo" value="0">
                                    <input type="checkbox" name="activo" value="1" class="sr-only peer"
                                           @checked(old('activo', $menu->activo))>
                                    <span class="w-9 h-5 rounded-full bg-uch-border transition-colors peer-checked:bg-uch-teal after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-full"></span>
                                </span>
                            </label>
                        </div>

                        <div class="flex flex-col gap-3">
                            <div class="flex flex-col gap-1">
                                <label for="titulo-{{ $menu->id }}" class="text-label-md font-semibold text-uch-muted">Título visible</label>
                                <input id="titulo-{{ $menu->id }}" type="text" name="titulo"
                                       value="{{ old('titulo', $menu->titulo) }}" required maxlength="100"
                                       class="w-full rounded-lg border-uch-border bg-uch-surface text-body-md text-uch-navy focus:border-uch-blue focus:ring-uch-blue">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex flex-col gap-1">
                                    <label for="precio-{{ $menu->id }}" class="text-label-md font-semibold text-uch-muted">
                                        {{ $menu->es_precio_variable ? 'Precio mínimo (S/)' : 'Precio fijo (S/)' }}
                                    </label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-2.5 font-rubik text-price-tag text-uch-navy">S/</span>
                                        <input id="precio-{{ $menu->id }}" type="number" step="0.01" min="0" name="precio_base"
                                               value="{{ old('precio_base', $menu->precio_base) }}" required
                                               class="w-full rounded-lg border-uch-border bg-uch-surface py-2 pl-8 pr-2 font-rubik text-price-tag text-uch-navy focus:border-uch-blue focus:ring-uch-blue">
                                    </div>
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label for="fecha-{{ $menu->id }}" class="text-label-md font-semibold text-uch-muted">Fecha vigente</label>
                                    <div class="relative flex items-center">
                                        <span class="material-symbols-outlined absolute left-2 text-[18px] text-uch-muted">calendar_today</span>
                                        <input id="fecha-{{ $menu->id }}" type="date" name="fecha_vigencia"
                                               value="{{ old('fecha_vigencia', $menu->fecha_vigencia?->toDateString()) }}"
                                               class="w-full rounded-lg border-uch-border bg-uch-surface py-2 pl-8 pr-2 text-body-md text-uch-navy focus:border-uch-blue focus:ring-uch-blue">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 items-center gap-3">
                                <div class="flex flex-col gap-1">
                                    <label for="etiqueta-{{ $menu->id }}" class="text-label-md font-semibold text-uch-muted">Etiqueta</label>
                                    <input id="etiqueta-{{ $menu->id }}" type="text" name="etiqueta"
                                           value="{{ old('etiqueta', $menu->etiqueta) }}" maxlength="40"
                                           class="w-full rounded-lg border-uch-border bg-uch-surface px-3 py-2 text-body-md text-uch-navy focus:border-uch-blue focus:ring-uch-blue">
                                </div>

                                <div class="flex flex-col gap-1 pt-1">
                                    <span class="text-label-md font-semibold text-uch-muted">Destacar</span>
                                    <label class="flex h-[38px] cursor-pointer items-center gap-2">
                                        <span class="relative inline-flex items-center">
                                            <input type="hidden" name="destacado" value="0">
                                            <input type="checkbox" name="destacado" value="1" class="sr-only peer"
                                                   @checked(old('destacado', $menu->destacado))>
                                            <span class="w-8 h-4 rounded-full bg-uch-border transition-colors peer-checked:bg-uch-teal after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:h-3 after:w-3 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-full"></span>
                                        </span>
                                        <span class="text-label-md font-semibold text-uch-navy">En portada</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label for="descripcion-{{ $menu->id }}" class="text-label-md font-semibold text-uch-muted">Descripción breve</label>
                                <input id="descripcion-{{ $menu->id }}" type="text" name="descripcion"
                                       value="{{ old('descripcion', $menu->descripcion) }}" maxlength="255"
                                       class="w-full rounded-lg border-uch-border bg-uch-surface px-3 py-2 text-body-md text-uch-navy focus:border-uch-blue focus:ring-uch-blue">
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 rounded-[10px] bg-uch-blue/5 p-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-uch-blue">inventory_2</span>
                                <span class="text-label-md font-semibold text-uch-blue">Capacidad total hoy:</span>
                            </div>
                            <span class="font-rubik text-price-tag font-bold text-uch-navy">
                                {{ $capacidadTotal }} raciones disponibles
                            </span>
                        </div>
                    </div>

                    {{-- Columna derecha: componentes por tiempo --}}
                    <div class="lg:col-span-8 flex flex-col gap-4">
                        @foreach (['entrada', 'fondo', 'refresco'] as $tiempo)
                            <div x-data="{
                                     seleccionados: @js($estadoComponentes[$tiempo] ?? []),
                                     nombres: @js($nombresComponentes[$tiempo] ?? []),
                                     abierto: false,
                                     expandido: {{ $tiempo === 'refresco' ? 'false' : 'true' }}
                                 }"
                                 class="flex flex-col gap-3 rounded-card border border-uch-border bg-uch-surface p-4 shadow-uch-card">
                                <div class="flex items-center justify-between gap-2 {{ $tiempo === 'fondo' ? 'border-b border-uch-border/70 pb-2' : '' }}">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <h2 class="truncate font-rubik text-title-dish text-uch-navy">
                                            {{ $titulosTiempo[$tiempo][0] }}
                                        </h2>
                                        <span class="rounded-full px-2.5 py-0.5 text-label-md font-bold {{ $titulosTiempo[$tiempo][2] }}"
                                              x-text="Object.values(seleccionados).filter(Boolean).length">{{ count(array_filter($estadoComponentes[$tiempo] ?? [])) }}</span>

                                        <span class="hidden min-w-0 truncate text-body-md text-uch-muted sm:inline"
                                              x-text="Object.entries(seleccionados).filter(([, activo]) => activo).map(([id]) => nombres[id]).join(', ')">{{ $componentes[$tiempo]->filter(fn ($componente) => in_array($componente->id, $seleccionadosGuardados, true))->pluck('nombre')->join(', ') ?: 'Sin componentes seleccionados' }}</span>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <button type="button" @click="expandido = !expandido"
                                                class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-label-md font-semibold text-uch-muted hover:bg-uch-bg hover:text-uch-navy transition-colors">
                                            <span x-text="expandido ? 'Contraer' : 'Expandir'">{{ $tiempo === 'refresco' ? 'Expandir' : 'Contraer' }}</span>
                                            <span class="material-symbols-outlined text-[16px]" :class="expandido ? 'rotate-180' : ''">expand_more</span>
                                        </button>

                                        <div class="relative">
                                            <button type="button" @click="abierto = !abierto" :aria-expanded="abierto"
                                                    class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-label-md font-bold text-uch-blue hover:bg-uch-bg transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">add</span>
                                                <span class="hidden md:inline">{{ $titulosTiempo[$tiempo][1] }}</span>
                                                <span class="material-symbols-outlined text-[16px]" :class="abierto ? 'rotate-180' : ''">expand_more</span>
                                            </button>

                                            <div x-show="abierto" @click.outside="abierto = false" x-cloak
                                                 class="absolute right-0 z-20 mt-2 max-h-64 w-60 overflow-y-auto rounded-xl border border-uch-border bg-uch-surface p-2 shadow-uch-card">
                                                @forelse ($componentes[$tiempo] as $componente)
                                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-uch-bg">
                                                        <input type="checkbox" name="habilitados[]" value="{{ $componente->id }}"
                                                               x-model="seleccionados[{{ $componente->id }}]"
                                                               class="rounded border-uch-border text-uch-blue focus:ring-uch-blue">
                                                        <span class="min-w-0 flex-1 truncate text-body-sm text-uch-navy">{{ $componente->nombre }}</span>
                                                    </label>
                                                @empty
                                                    <p class="px-2 py-1.5 text-body-sm text-uch-muted">No hay componentes de este tiempo.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="expandido" x-cloak
                                     class="{{ $tiempo === 'fondo' ? 'flex flex-col divide-y divide-uch-border/60' : 'flex flex-wrap items-center gap-2' }}">
                                    @foreach ($componentes[$tiempo] as $componente)
                                        @php $opcion = $opcionesPorComponente->get($componente->id); @endphp

                                        @if ($tiempo === 'fondo')
                                            <div x-show="seleccionados[{{ $componente->id }}]"
                                                 class="flex flex-col gap-2 rounded-lg px-2 py-2.5 transition-colors hover:bg-uch-bg sm:flex-row sm:items-center sm:justify-between">
                                                <div class="flex min-w-0 items-center gap-2.5">
                                                    <span class="h-2 w-2 shrink-0 rounded-full bg-uch-blue"></span>
                                                    <span class="truncate text-body-md font-semibold text-uch-navy">{{ $componente->nombre }}</span>
                                                    @if ($componente->descripcion)
                                                        <span class="hidden truncate text-body-sm text-uch-muted sm:inline">• {{ $componente->descripcion }}</span>
                                                    @endif
                                                </div>

                                                <div class="flex items-center gap-3">
                                                    <div class="flex items-center gap-2 rounded-lg border border-uch-border bg-uch-bg px-3 py-1.5">
                                                        <input type="number" min="0" inputmode="numeric"
                                                               aria-label="Raciones disponibles de {{ $componente->nombre }}"
                                                               name="stock_raciones[{{ $componente->id }}]"
                                                               value="{{ old('stock_raciones.'.$componente->id, $opcion->stock_raciones ?? '') }}"
                                                               class="input-numero-limpio w-14 min-w-0 bg-transparent text-right font-rubik text-label-lg font-bold text-uch-navy outline-none">
                                                        <span class="whitespace-nowrap text-label-md text-uch-muted">raciones</span>
                                                    </div>

                                                    @if ($menu->es_precio_variable)
                                                        <div class="flex items-center gap-2 rounded-lg border border-uch-border bg-uch-bg px-3 py-1.5">
                                                            <span class="text-label-md text-uch-muted">S/</span>
                                                            <input type="number" step="0.01" min="0"
                                                                   aria-label="Precio del menú para {{ $componente->nombre }}"
                                                                   name="precio_opcion[{{ $componente->id }}]"
                                                                   value="{{ old('precio_opcion.'.$componente->id, $opcion->precio_opcion ?? '') }}"
                                                                   class="input-numero-limpio w-14 min-w-0 bg-transparent font-rubik text-label-lg font-bold text-uch-navy outline-none">
                                                        </div>
                                                    @endif

                                                    <button type="button" @click="seleccionados[{{ $componente->id }}] = false"
                                                            class="flex h-6 w-6 items-center justify-center rounded text-uch-muted hover:text-uch-coral"
                                                            aria-label="Quitar {{ $componente->nombre }}">
                                                        <span class="material-symbols-outlined text-[16px]">close</span>
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <span x-show="seleccionados[{{ $componente->id }}]"
                                                  class="inline-flex items-center gap-2 rounded-full border border-uch-border bg-uch-bg px-3 py-1.5 text-label-md font-semibold text-uch-navy">
                                                <span>{{ $componente->nombre }}</span>
                                                <button type="button" @click="seleccionados[{{ $componente->id }}] = false"
                                                        class="flex items-center text-uch-muted hover:text-uch-coral"
                                                        aria-label="Quitar {{ $componente->nombre }}">
                                                    <span class="material-symbols-outlined text-[14px]">close</span>
                                                </button>
                                            </span>
                                        @endif
                                    @endforeach

                                    <template x-if="Object.values(seleccionados).every((activo) => !activo)">
                                        <p class="text-body-sm text-uch-muted">Aún no hay componentes seleccionados. Usa “Añadir” para elegir.</p>
                                    </template>
                                </div>
                            </div>
                        @endforeach

                        <div class="flex justify-end">
                            <a href="/menu" target="_blank"
                               class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-body-sm text-uch-muted hover:text-uch-blue transition-colors">
                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                Ver catálogo
                            </a>
                        </div>
                    </div>
                </form>
            </section>
        @endforeach
    </div>
</x-layouts.app>
