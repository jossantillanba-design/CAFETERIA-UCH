<x-layouts.app>
    <x-admin-nav />

    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ pestana: '{{ $menus->first()?->tipo_menu ?? 'universitario' }}', nuevo: window.location.hash === '#componentes' }">
        <x-flash-message />

        <header class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-7">
            <div>
                <span class="text-label-md uppercase tracking-widest text-uch-blue">Oferta del día</span>
                <h1 class="text-headline-lg-mobile lg:text-headline-lg font-rubik text-uch-navy tracking-tight">Menús del día</h1>
                <p class="text-body-md text-uch-muted mt-1">Configura entradas, platos principales y refrescos para cada modalidad.</p>
            </div>
            <button type="button" @click="nuevo = !nuevo" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-uch-navy text-white text-body-sm hover:bg-uch-navy-dark transition-colors">
                <span class="material-symbols-outlined text-[19px]" x-text="nuevo ? 'close' : 'add'">add</span>
                <span x-text="nuevo ? 'Cerrar' : 'Nuevo componente'"></span>
            </button>
        </header>

        <div id="componentes" x-show="nuevo" x-cloak class="scroll-mt-8 bg-uch-surface rounded-card shadow-uch-card p-5 mb-6">
            <h2 class="font-rubik text-headline-sm text-uch-navy mb-4">Añadir al banco de opciones</h2>
            <form method="POST" action="/admin/menus/componentes" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block text-body-sm font-semibold text-uch-navy mb-1.5">Nombre</label>
                    <input type="text" name="nombre" required maxlength="120" placeholder="Ej. Sopa criolla"
                           class="w-full rounded-lg border-uch-border focus:border-uch-blue focus:ring-uch-blue text-body-md">
                </div>
                <div>
                    <label class="block text-body-sm font-semibold text-uch-navy mb-1.5">Tiempo</label>
                    <select name="tiempo" required class="w-full rounded-lg border-uch-border focus:border-uch-blue focus:ring-uch-blue text-body-md">
                        <option value="entrada">Entrada</option>
                        <option value="fondo">Plato principal</option>
                        <option value="refresco">Refresco</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-uch-coral text-white text-body-sm font-semibold hover:bg-uch-coral-dark transition-colors">Guardar</button>
            </form>
        </div>

        <nav class="flex items-center gap-2 p-1.5 bg-uch-border/50 rounded-full overflow-x-auto no-scrollbar mb-6" aria-label="Modalidades de menú">
            @foreach ($menus as $menu)
                <button type="button"
                        @click="pestana = '{{ $menu->tipo_menu }}'"
                        :class="pestana === '{{ $menu->tipo_menu }}' ? 'bg-uch-navy text-white shadow-sm' : 'bg-uch-surface text-uch-navy hover:bg-uch-bg'"
                        class="shrink-0 px-5 py-2 rounded-full text-body-sm font-semibold transition-all flex items-center gap-2">
                    {{ $menu->titulo }}
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $menu->activo ? 'bg-uch-teal/20' : 'bg-uch-bg text-uch-muted' }}">
                        {{ $menu->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </button>
            @endforeach
        </nav>

        @foreach ($menus as $menu)
            @php
                $opcionesPorComponente = $menu->opciones->keyBy('componente_id');
                $titulosTiempo = [
                    'entrada' => ['Paso 1 · Entradas', 'Marca las entradas disponibles hoy.'],
                    'fondo' => ['Paso 2 · Platos principales', 'Define raciones y, en el Ejecutivo, el precio del menú.'],
                    'refresco' => ['Paso 3 · Refrescos', 'Bebidas incluidas en la modalidad.'],
                ];
            @endphp

            <section x-show="pestana === '{{ $menu->tipo_menu }}'" x-cloak class="space-y-6">
                <form method="POST" action="/admin/menus/{{ $menu->id }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="bg-uch-surface rounded-card shadow-uch-card p-6">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-5">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-rubik text-headline-md text-uch-navy">{{ $menu->titulo }}</h2>
                                    @if ($menu->etiqueta)
                                        <span class="px-2.5 py-0.5 rounded-full bg-uch-cyan/15 text-[#0060A9] text-[11px] font-semibold">{{ $menu->etiqueta }}</span>
                                    @endif
                                </div>
                                <p class="text-body-sm text-uch-muted mt-1">Tipo: {{ ucfirst($menu->tipo_menu) }}</p>
                            </div>
                            <label class="flex items-center gap-2 text-body-sm text-uch-navy">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" name="activo" value="1" {{ $menu->activo ? 'checked' : '' }} class="rounded border-uch-border text-uch-blue focus:ring-uch-blue">
                                Habilitar hoy
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-body-sm font-semibold text-uch-navy mb-1.5">Título</label>
                                <input type="text" name="titulo" value="{{ old('titulo', $menu->titulo) }}" required maxlength="100"
                                       class="w-full rounded-lg border-uch-border focus:border-uch-blue focus:ring-uch-blue text-body-md">
                            </div>
                            <div>
                                <label class="block text-body-sm font-semibold text-uch-navy mb-1.5">
                                    {{ $menu->es_precio_variable ? 'Precio mínimo referencial' : 'Precio fijo' }}
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-body-sm text-uch-muted">S/</span>
                                    <input type="number" step="0.01" min="0" name="precio_base" value="{{ old('precio_base', $menu->precio_base) }}" required
                                           class="w-full pl-9 rounded-lg border-uch-border focus:border-uch-blue focus:ring-uch-blue text-body-md">
                                </div>
                            </div>
                            <div>
                                <label class="block text-body-sm font-semibold text-uch-navy mb-1.5">Vigencia</label>
                                <input type="date" name="fecha_vigencia" value="{{ old('fecha_vigencia', $menu->fecha_vigencia?->toDateString()) }}"
                                       class="w-full rounded-lg border-uch-border focus:border-uch-blue focus:ring-uch-blue text-body-md">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-body-sm font-semibold text-uch-navy mb-1.5">Descripción</label>
                                <input type="text" name="descripcion" value="{{ old('descripcion', $menu->descripcion) }}" maxlength="255"
                                       class="w-full rounded-lg border-uch-border focus:border-uch-blue focus:ring-uch-blue text-body-md">
                            </div>
                            <div>
                                <label class="block text-body-sm font-semibold text-uch-navy mb-1.5">Etiqueta</label>
                                <input type="text" name="etiqueta" value="{{ old('etiqueta', $menu->etiqueta) }}" maxlength="40"
                                       class="w-full rounded-lg border-uch-border focus:border-uch-blue focus:ring-uch-blue text-body-md">
                            </div>
                            <label class="flex items-end gap-2 pb-2.5 text-body-sm text-uch-navy">
                                <input type="hidden" name="destacado" value="0">
                                <input type="checkbox" name="destacado" value="1" {{ $menu->destacado ? 'checked' : '' }} class="rounded border-uch-border text-uch-blue focus:ring-uch-blue">
                                Destacado
                            </label>
                        </div>
                    </div>

                    @foreach (['entrada', 'fondo', 'refresco'] as $tiempo)
                        <div class="bg-uch-surface rounded-card shadow-uch-card p-6">
                            <div class="mb-4">
                                <h3 class="font-rubik text-headline-sm text-uch-navy">{{ $titulosTiempo[$tiempo][0] }}</h3>
                                <p class="text-body-sm text-uch-muted">{{ $titulosTiempo[$tiempo][1] }}</p>
                            </div>

                            @if ($componentes[$tiempo]->isEmpty())
                                <p class="text-body-sm text-uch-muted">No hay componentes de este tiempo. Añade uno con el botón “Nuevo componente”.</p>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                    @foreach ($componentes[$tiempo] as $componente)
                                        @php $opcion = $opcionesPorComponente->get($componente->id); @endphp
                                        <label class="flex items-start gap-3 rounded-xl border border-uch-border p-3.5 hover:bg-uch-bg transition-colors cursor-pointer">
                                            <input type="checkbox" name="habilitados[]" value="{{ $componente->id }}"
                                                   {{ $opcion?->habilitado ? 'checked' : '' }}
                                                   class="mt-0.5 rounded border-uch-border text-uch-blue focus:ring-uch-blue">
                                            <span class="min-w-0 flex-1">
                                                <span class="block text-body-sm font-semibold text-uch-navy">{{ $componente->nombre }}</span>
                                                @if ($componente->descripcion)
                                                    <span class="block text-[11px] text-uch-muted">{{ $componente->descripcion }}</span>
                                                @endif

                                                @if ($tiempo === 'fondo')
                                                    <span class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
                                                        <span>
                                                            <span class="block text-[10px] uppercase tracking-wide text-uch-muted mb-1">Raciones</span>
                                                            <input type="number" min="0" name="stock_raciones[{{ $componente->id }}]"
                                                                   value="{{ old('stock_raciones.'.$componente->id, $opcion->stock_raciones ?? '') }}"
                                                                   placeholder="Sin límite"
                                                                   class="w-full rounded-lg border-uch-border text-body-sm py-1.5">
                                                        </span>
                                                        @if ($menu->es_precio_variable)
                                                            <span>
                                                                <span class="block text-[10px] uppercase tracking-wide text-uch-muted mb-1">Precio menú</span>
                                                                <input type="number" step="0.01" min="0" name="precio_opcion[{{ $componente->id }}]"
                                                                       value="{{ old('precio_opcion.'.$componente->id, $opcion->precio_opcion ?? '') }}"
                                                                       placeholder="S/ 0.00"
                                                                       class="w-full rounded-lg border-uch-border text-body-sm py-1.5">
                                                            </span>
                                                        @endif
                                                    </span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="flex items-center justify-end gap-3">
                        <a href="/menu" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-body-sm text-uch-muted hover:text-uch-blue">
                            <span class="material-symbols-outlined text-[18px]">visibility</span> Ver catálogo
                        </a>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-uch-coral text-white text-body-sm font-semibold hover:bg-uch-coral-dark transition-colors">
                            Guardar configuración
                        </button>
                    </div>
                </form>
            </section>
        @endforeach
    </div>
</x-layouts.app>
