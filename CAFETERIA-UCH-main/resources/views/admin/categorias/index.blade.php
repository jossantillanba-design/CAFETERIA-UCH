<x-layouts.app>
    <x-admin-nav />

    @php
        $categoriasEditables = $estructura->where('es_menu', false)->values();
        $datosError = [
            'hay' => $errors->any(),
            'id' => old('categoria_id'),
            'nombre' => old('nombre'),
            'slug' => old('slug'),
            'descripcion' => old('descripcion'),
            'color_hex' => old('color_hex'),
            'orden' => old('orden'),
            'activo' => old('activo'),
        ];
    @endphp

    <div x-data="categoriaCatalogo()" class="mx-auto flex w-full max-w-[1400px] flex-col gap-space-xl px-4 py-6 sm:px-6 lg:px-8">
        <x-flash-message />

        <!-- CABECERA DE SECCIÓN -->
        <header class="flex flex-col gap-space-md md:flex-row md:items-end md:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-space-xs text-label-md uppercase tracking-wider text-uch-blue">
                    <span class="material-symbols-outlined text-[18px]">label</span>
                    <span>Menús y Catálogo / Categorías</span>
                </div>
                <h1 class="font-rubik text-headline-lg-mobile tracking-tight text-uch-navy lg:text-headline-lg">Categorías</h1>
                <p class="text-body-md text-uch-muted">
                    Secciones y colores del menú digital.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-space-sm">
                <span class="hidden items-center gap-1.5 rounded-lg bg-uch-bg px-3 py-1.5 text-label-md text-uch-muted sm:inline-flex">
                    <span class="material-symbols-outlined text-[16px]">folder_copy</span>
                    {{ $metricas['totalRegistros'] }} categorías registradas
                </span>
                <button type="button" @click="abrirNueva()"
                        class="inline-flex shrink-0 items-center gap-space-xs rounded-[10px] bg-uch-navy px-5 py-2.5 text-label-lg text-white shadow-sm transition-all hover:bg-uch-blue active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Nueva categoría
                </button>
            </div>
        </header>

        <!-- MÉTRICAS RÁPIDAS -->
        <section class="grid grid-cols-1 gap-space-md sm:grid-cols-3">
            <article class="flex items-center justify-between rounded-card bg-uch-surface p-space-md shadow-uch-card">
                <div class="space-y-0.5">
                    <p class="text-label-md text-uch-muted">Catálogos de Menú</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">
                        {{ $metricas['catalogosMenu'] }} <span class="font-normal text-body-sm text-uch-muted">tipos fijados</span>
                    </p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#E8F1FA] text-uch-blue">
                    <span class="material-symbols-outlined text-[22px]">restaurant_menu</span>
                </div>
            </article>

            <article class="flex items-center justify-between rounded-card bg-uch-surface p-space-md shadow-uch-card">
                <div class="space-y-0.5">
                    <p class="text-label-md text-uch-muted">Productos Individuales</p>
                    <p class="font-rubik text-headline-md font-bold text-uch-navy">
                        {{ $metricas['productosIndividuales'] }} <span class="font-normal text-body-sm text-uch-muted">items activos</span>
                    </p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#D3E4FF] text-[#004881]">
                    <span class="material-symbols-outlined text-[22px]">fastfood</span>
                </div>
            </article>

            <article class="flex items-center justify-between rounded-card bg-uch-surface p-space-md shadow-uch-card">
                <div class="space-y-0.5">
                    <p class="text-label-md text-uch-muted">Visibilidad en Tótems</p>
                    <p class="font-rubik text-headline-md font-bold text-[#008F84]">
                        {{ $metricas['visibilidad'] }}% <span class="font-normal text-body-sm text-uch-muted">público</span>
                    </p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#E0F7F6] text-[#008F84]">
                    <span class="material-symbols-outlined text-[22px]">check_circle</span>
                </div>
            </article>
        </section>

        <!-- ESTRUCTURA DEL MENÚ -->
        <section class="overflow-hidden rounded-card bg-uch-surface shadow-uch-card">
            <div class="flex flex-col gap-space-sm border-b border-uch-border px-space-md py-space-md sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-space-sm">
                    <h2 class="font-rubik text-headline-sm text-uch-navy">Estructura del Menú</h2>
                    <span class="rounded-full bg-[#D3E4FF] px-2 py-0.5 text-label-md font-bold text-[#004881]">
                        Activo para Campus Los Olivos
                    </span>
                </div>
                <div class="flex items-center gap-1.5 text-body-sm text-uch-muted">
                    <span class="material-symbols-outlined text-[18px]">drag_indicator</span>
                    <span>Arrastra las categorías de productos para reordenar la posición</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] border-collapse text-left text-body-md">
                    <thead>
                        <tr class="bg-[#E8F1FA] font-rubik text-body-md uppercase tracking-wider text-uch-navy">
                            <th scope="col" class="w-16 px-space-md py-3.5 text-center">Orden</th>
                            <th scope="col" class="w-20 px-space-md py-3.5 text-center">Color</th>
                            <th scope="col" class="px-space-md py-3.5">Categoría</th>
                            <th scope="col" class="px-space-md py-3.5">Tipo</th>
                            <th scope="col" class="px-space-md py-3.5">Productos asociados</th>
                            <th scope="col" class="px-space-md py-3.5">Estado</th>
                            <th scope="col" class="px-space-md py-3.5 pr-space-lg text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="estructura-body" class="divide-y divide-uch-border">
                        @forelse ($estructura as $indice => $item)
                            <tr @if (! $item['es_menu']) draggable="true" data-categoria-id="{{ $item['id'] }}" class="cursor-grab transition-colors hover:bg-uch-bg/70" @else class="transition-colors hover:bg-uch-bg/70" @endif>
                                <td class="px-space-md py-4 text-center font-bold text-uch-muted">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded bg-uch-bg text-body-sm font-bold">{{ $indice + 1 }}</span>
                                </td>
                                <td class="px-space-md py-4 text-center">
                                    <span class="inline-block h-4 w-4 rounded-full shadow-sm" style="background-color: {{ $item['color'] }}"></span>
                                </td>
                                <td class="px-space-md py-4">
                                    <div class="font-rubik text-body-lg font-bold leading-tight text-uch-navy">{{ $item['nombre'] }}</div>
                                    @if ($item['descripcion'])
                                        <div class="mt-0.5 text-body-sm text-uch-muted">{{ $item['descripcion'] }}</div>
                                    @endif
                                </td>
                                <td class="px-space-md py-4">
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-label-md font-bold {{ $item['estilo_tipo'] }}">
                                        {{ $item['tipo'] }}
                                    </span>
                                </td>
                                <td class="px-space-md py-4 text-uch-muted">
                                    {{ $item['asociados'] }}
                                </td>
                                <td class="px-space-md py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-label-md font-bold {{ $item['activo'] ? 'bg-[#E0F7F6] text-[#008F84]' : 'bg-uch-bg text-uch-muted' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $item['activo'] ? 'bg-uch-teal' : 'bg-uch-muted' }}"></span>
                                        {{ $item['activo'] ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="px-space-md py-4 pr-space-lg">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if ($item['es_menu'])
                                            <a href="/admin/menus"
                                               class="inline-flex items-center gap-1 rounded-lg py-1 px-3 text-label-md font-bold text-uch-blue transition-colors hover:bg-uch-bg hover:text-uch-navy">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                                Editar
                                            </a>
                                        @else
                                            <button type="button" @click="abrirEditar({{ $item['id'] }})"
                                                    class="inline-flex items-center gap-1 rounded-lg py-1 px-3 text-label-md font-bold text-uch-blue transition-colors hover:bg-uch-bg hover:text-uch-navy">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                                Editar
                                            </button>
                                            <form method="POST" action="/admin/categorias/{{ $item['id'] }}" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Eliminar categoría"
                                                        class="rounded-lg p-1.5 text-uch-coral transition-colors hover:bg-uch-coral/10">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-space-md py-10 text-center text-uch-muted">No hay secciones registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-2 bg-uch-bg px-space-md py-space-md text-body-sm text-uch-muted sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-uch-blue">info</span>
                    <span>Los colores asignados se reflejan en el banner de categoría de la app estudiantil UCH.</span>
                </div>
                <div class="text-label-md font-bold text-uch-navy">
                    Total: {{ $metricas['totalRegistros'] }} categorías habilitadas
                </div>
            </div>
        </section>

        <form id="form-orden" method="POST" action="{{ route('categorias.reordenar') }}" class="hidden">
            @csrf
        </form>

        <!-- MODAL DE ALTA / EDICIÓN -->
        <div x-show="modalAbierto" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-uch-navy/60 backdrop-blur-sm" @click="cerrar()"></div>
            <div class="relative flex max-h-[90vh] w-full max-w-[520px] flex-col overflow-hidden rounded-[16px] bg-uch-surface shadow-uch-card-hover">
                <div class="flex h-2 w-full shrink-0 bg-uch-blue">
                    <div class="h-full w-28 bg-uch-cyan"></div>
                </div>

                <div class="flex shrink-0 items-start justify-between gap-4 border-b border-uch-border px-6 py-5">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-uch-cyan"></span>
                            <h2 class="font-rubik text-headline-sm leading-none text-uch-navy" x-text="editando ? 'Editar categoría' : 'Nueva categoría'"></h2>
                        </div>
                        <p class="text-body-sm text-uch-muted">
                            Modifica el nombre, color de etiqueta o visibilidad en tienda.
                        </p>
                    </div>
                    <button type="button" @click="cerrar()" aria-label="Cerrar modal"
                            class="rounded-full p-2 text-uch-muted transition-colors hover:bg-uch-bg hover:text-uch-navy">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form :action="formAction" method="POST" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    <input type="hidden" name="_method" :value="editando ? 'PUT' : 'POST'">
                    <input type="hidden" name="categoria_id" x-model="form.id">
                    <input type="hidden" name="slug" x-model="form.slug">
                    <input type="hidden" name="color_hex" x-model="form.color_hex">

                    <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-5">
                        <div class="space-y-1.5">
                            <label for="nombre-categoria" class="block text-label-md font-bold text-uch-navy">
                                Nombre de la categoría <span class="font-bold text-uch-coral">*</span>
                            </label>
                            <div class="relative">
                                <input id="nombre-categoria" name="nombre" type="text" x-ref="nombreInput" x-model="form.nombre"
                                       required maxlength="80" placeholder="Ej. Platos a la Carta"
                                       class="w-full rounded-lg border border-uch-border bg-uch-surface py-2.5 pl-3.5 pr-10 text-body-md text-uch-text transition-all focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none">
                                <span class="material-symbols-outlined absolute right-3 top-2.5 text-[20px] text-uch-muted">lunch_dining</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-space-md sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <label for="orden-categoria" class="block text-label-md font-bold text-uch-navy">Orden en catálogo</label>
                                <div class="relative">
                                    <input id="orden-categoria" name="orden" type="number" min="0" max="9999" x-model.number="form.orden"
                                           class="w-full rounded-lg border border-uch-border bg-uch-surface py-2.5 pl-3.5 pr-10 font-rubik text-title-dish font-bold text-uch-navy transition-all focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none">
                                    <span class="material-symbols-outlined absolute right-3 top-2.5 text-[20px] text-uch-muted">swap_vert</span>
                                </div>
                                <p class="text-[11px] text-uch-muted">Posición visual en los tótems UCH</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-md font-bold text-uch-navy">Tipo de sección</label>
                                <div class="flex w-full cursor-not-allowed items-center justify-between rounded-lg bg-uch-bg px-3.5 py-2.5">
                                    <span class="inline-flex rounded bg-uch-surface px-2 py-0.5 text-label-md font-bold text-uch-muted shadow-sm">
                                        Productos adicionales
                                    </span>
                                    <span class="material-symbols-outlined text-[18px] text-uch-muted" title="Tipo fijo según catálogo">lock</span>
                                </div>
                                <p class="text-[11px] text-uch-muted">Definido por la estructura del módulo</p>
                            </div>
                        </div>

                        <div class="space-y-2 pt-1">
                            <div class="flex items-center justify-between gap-3">
                                <label class="block text-label-md font-bold text-uch-navy">Color identificativo en tarjeta y filtros</label>
                                <span class="text-body-sm font-semibold text-uch-muted" x-text="nombreColor"></span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 rounded-xl bg-uch-bg p-3">
                                <template x-for="color in colores" :key="color.hex">
                                    <button type="button" @click="seleccionarColor(color)" :aria-label="'Color ' + color.nombre"
                                            class="h-8 w-8 rounded-full transition-transform hover:scale-110 focus:outline-none"
                                            :class="form.color_hex === color.hex ? 'ring-2 ring-offset-2 scale-105' : ''"
                                            :style="'background-color: ' + color.hex + '; --tw-ring-color: ' + color.hex"></button>
                                </template>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label for="descripcion-categoria" class="block text-label-md font-bold text-uch-navy">
                                Descripción corta <span class="font-normal text-uch-muted">(opcional)</span>
                            </label>
                            <textarea id="descripcion-categoria" name="descripcion" rows="2" maxlength="255" x-model="form.descripcion"
                                      placeholder="Breve referencia para los alumnos..."
                                      class="w-full resize-none rounded-lg border border-uch-border bg-uch-surface px-3.5 py-2 text-body-md text-uch-text transition-all focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none"></textarea>
                        </div>

                        <div class="flex items-center justify-between gap-space-md rounded-xl bg-uch-bg p-3.5">
                            <div class="space-y-0.5">
                                <p class="text-label-lg font-bold leading-tight text-uch-navy">Categoría visible para clientes</p>
                                <p class="text-body-sm leading-snug text-uch-muted">
                                    Los estudiantes podrán ver y filtrar por esta sección en el catálogo digital.
                                </p>
                            </div>
                            <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                                <input type="checkbox" class="peer sr-only" x-model="form.activo">
                                <span class="block h-6 w-12 rounded-full bg-uch-border transition-colors after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-uch-teal peer-checked:after:translate-x-full"></span>
                            </label>
                            <input type="hidden" name="activo" :value="form.activo ? 1 : 0">
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center justify-end gap-3 rounded-b-[16px] border-t border-uch-border bg-uch-bg px-6 py-4">
                        <button type="button" @click="cerrar()"
                                class="rounded-[10px] px-space-md py-2.5 text-label-md font-bold text-uch-muted transition-colors hover:bg-uch-border hover:text-uch-navy">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-[10px] bg-uch-navy px-space-lg py-2.5 text-label-md font-bold text-white shadow-md transition-all hover:bg-uch-blue hover:shadow-lg">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function categoriaCatalogo() {
                const colores = [
                    { hex: '#1B3668', nombre: 'Azul Marino' },
                    { hex: '#0066B3', nombre: 'Azul Medio' },
                    { hex: '#00AEEF', nombre: 'Cian Solar' },
                    { hex: '#00B4A6', nombre: 'Turquesa Campus' },
                    { hex: '#F59E0B', nombre: 'Ámbar Cálido' },
                    { hex: '#E63946', nombre: 'Rojo Coral' },
                ];

                return {
                    colores,
                    categorias: @js($categoriasEditables),
                    errores: @js($datosError),
                    modalAbierto: false,
                    editando: false,
                    form: {
                        id: null,
                        nombre: '',
                        slug: '',
                        descripcion: '',
                        color_hex: '#00AEEF',
                        orden: 0,
                        activo: true,
                    },

                    init() {
                        if (!this.errores.hay) {
                            return;
                        }

                        this.editando = Boolean(this.errores.id);
                        this.form = {
                            id: this.errores.id ?? null,
                            nombre: this.errores.nombre ?? '',
                            slug: this.errores.slug ?? '',
                            descripcion: this.errores.descripcion ?? '',
                            color_hex: this.errores.color_hex ?? '#00AEEF',
                            orden: this.errores.orden ?? 0,
                            activo: this.errores.activo == null ? true : Boolean(Number(this.errores.activo)),
                        };
                        this.modalAbierto = true;
                    },

                    get nombreColor() {
                        const seleccionado = this.colores.find((color) => color.hex === this.form.color_hex);

                        return seleccionado ? seleccionado.nombre : 'Personalizado';
                    },

                    get formAction() {
                        const rutaEdicion = '{{ route('categorias.update', ['categoria' => 'ID_CATEGORIA']) }}';

                        return this.editando
                            ? rutaEdicion.replace('ID_CATEGORIA', this.form.id)
                            : '{{ route('categorias.store') }}';
                    },

                    abrirNueva() {
                        this.editando = false;
                        this.form = {
                            id: null,
                            nombre: '',
                            slug: '',
                            descripcion: '',
                            color_hex: '#00AEEF',
                            orden: 0,
                            activo: true,
                        };
                        this.modalAbierto = true;
                        this.$nextTick(() => this.$refs.nombreInput?.focus());
                    },

                    abrirEditar(id) {
                        const categoria = this.categorias.find((item) => item.id === id);

                        if (!categoria) {
                            return;
                        }

                        this.editando = true;
                        this.form = {
                            id: categoria.id,
                            nombre: categoria.nombre,
                            slug: categoria.slug,
                            descripcion: categoria.descripcion ?? '',
                            color_hex: categoria.color_hex,
                            orden: categoria.orden_catalogo,
                            activo: Boolean(categoria.activo),
                        };
                        this.modalAbierto = true;
                        this.$nextTick(() => this.$refs.nombreInput?.focus());
                    },

                    cerrar() {
                        this.modalAbierto = false;
                    },

                    seleccionarColor(color) {
                        this.form.color_hex = color.hex;
                    },
                };
            }

            document.addEventListener('DOMContentLoaded', () => {
                const cuerpo = document.getElementById('estructura-body');
                const formularioOrden = document.getElementById('form-orden');

                if (!cuerpo || !formularioOrden) {
                    return;
                }

                let filaArrastrada = null;

                cuerpo.addEventListener('dragstart', (evento) => {
                    const fila = evento.target.closest('tr[data-categoria-id]');

                    if (!fila) {
                        return;
                    }

                    filaArrastrada = fila;
                    evento.dataTransfer.effectAllowed = 'move';
                    fila.classList.add('opacity-50');
                });

                cuerpo.addEventListener('dragend', () => {
                    if (filaArrastrada) {
                        filaArrastrada.classList.remove('opacity-50');
                    }

                    filaArrastrada = null;
                });

                cuerpo.addEventListener('dragover', (evento) => {
                    if (!filaArrastrada) {
                        return;
                    }

                    const destino = evento.target.closest('tr[data-categoria-id]');

                    if (!destino || destino === filaArrastrada) {
                        return;
                    }

                    evento.preventDefault();
                    const rectangulo = destino.getBoundingClientRect();
                    const despues = evento.clientY > rectangulo.top + rectangulo.height / 2;
                    cuerpo.insertBefore(filaArrastrada, despues ? destino.nextSibling : destino);
                });

                cuerpo.addEventListener('drop', (evento) => {
                    if (!filaArrastrada) {
                        return;
                    }

                    evento.preventDefault();
                    formularioOrden.querySelectorAll('input[name="ids[]"]').forEach((entrada) => entrada.remove());
                    cuerpo.querySelectorAll('tr[data-categoria-id]').forEach((fila) => {
                        const entrada = document.createElement('input');
                        entrada.type = 'hidden';
                        entrada.name = 'ids[]';
                        entrada.value = fila.dataset.categoriaId;
                        formularioOrden.appendChild(entrada);
                    });
                    formularioOrden.submit();
                });
            });
        </script>
    @endpush
</x-layouts.app>
