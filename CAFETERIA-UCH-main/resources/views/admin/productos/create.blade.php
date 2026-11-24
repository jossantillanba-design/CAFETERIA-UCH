<x-layouts.app>
    <x-admin-nav />

    @php
        $producto = $producto ?? null;
        $esEdicion = $producto !== null;
        $nombre = old('nombre', $producto?->nombre ?? '');
        $sku = old('sku', $producto?->sku ?? '');
        $categoriaId = old('categoria_id', $producto?->categoria_id ?? '');
        $precio = old('precio', $producto?->precio ?? '');
        $stockActual = old('stock_actual', $producto?->stock_actual ?? 0);
        $stockMinimo = old('stock_minimo', $producto?->stock_minimo ?? 5);
        $descripcion = old('descripcion', $producto?->descripcion ?? '');
        $alergenos = old('alergenos', $producto?->alergenos ?? '');
        $imagenActual = $producto?->imagen_url ?? asset('images/productos/pan-con-pollo.jpg');

        $alergenosIniciales = collect(explode(',', $alergenos))
            ->map(fn (string $alergeno): string => trim($alergeno))
            ->filter()
            ->values()
            ->all();

        $switches = [
            [
                'nombre' => 'activo',
                'titulo' => 'Visible en menú',
                'detalle' => 'Disponible en la tienda para los alumnos',
                'marcado' => (bool) old('activo', $producto?->activo ?? true),
                'color' => 'peer-checked:bg-uch-blue',
            ],
            [
                'nombre' => 'destacado',
                'titulo' => 'Destacar producto',
                'detalle' => 'Aparece en recomendados principales',
                'marcado' => (bool) old('destacado', $producto?->destacado ?? false),
                'color' => 'peer-checked:bg-uch-navy',
            ],
            [
                'nombre' => 'es_nuevo',
                'titulo' => 'Etiqueta “Nuevo”',
                'detalle' => 'Para lanzamientos de semana',
                'marcado' => (bool) old('es_nuevo', $producto?->es_nuevo ?? false),
                'color' => 'peer-checked:bg-uch-blue',
            ],
            [
                'nombre' => 'es_popular',
                'titulo' => 'Etiqueta “Popular”',
                'detalle' => 'Marca de alta demanda',
                'marcado' => (bool) old('es_popular', $producto?->es_popular ?? false),
                'color' => 'peer-checked:bg-uch-coral',
            ],
        ];
    @endphp

    <div class="mx-auto w-full max-w-[1360px] px-gutter py-space-lg">
        <x-flash-message />

        @if ($errors->any())
            <div class="mb-space-lg rounded-lg border border-uch-coral/30 bg-uch-coral/10 p-space-md text-body-sm text-uch-coral-dark">
                <p class="mb-1 font-semibold">Revisa los campos marcados:</p>
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col gap-space-xs">
            <a href="/admin/productos"
               class="group inline-flex w-fit items-center gap-1.5 text-label-md text-uch-blue transition-colors hover:text-uch-navy">
                <span class="material-symbols-outlined text-[18px] transition-transform group-hover:-translate-x-0.5">arrow_back</span>
                Volver a productos
            </a>

            <div class="flex flex-wrap items-center justify-between gap-space-md pt-space-xs">
                <div class="flex flex-wrap items-center gap-space-sm">
                    <h1 class="font-rubik text-headline-lg tracking-tight text-uch-navy">
                        {{ $esEdicion ? 'Editar producto' : 'Nuevo producto' }}
                    </h1>
                    <span class="inline-flex items-center gap-1 rounded-full bg-uch-blue/10 px-3 py-1 text-label-md text-uch-navy">
                        <span class="h-1.5 w-1.5 rounded-full bg-uch-blue"></span>
                        {{ $esEdicion ? 'Modo edición · '.$producto->nombre : 'Modo creación' }}
                    </span>
                </div>
                <div class="flex items-center gap-space-sm">
                    <a href="/admin/productos"
                       class="rounded-lg bg-uch-border px-4 py-2 text-label-lg text-uch-navy transition-colors hover:bg-uch-muted/30">
                        Descartar cambios
                    </a>
                    <button type="submit" form="product-creation-form"
                            class="inline-flex items-center gap-2 rounded-lg bg-uch-navy px-5 py-2 text-label-lg text-white shadow-sm transition-all hover:bg-uch-blue">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Guardar producto
                    </button>
                </div>
            </div>

            <p class="text-body-md text-uch-muted">
                @if ($esEdicion)
                    Foto, categoría, precio, stock y etiquetas del producto.
                @else
                    Foto, precio, stock inicial y etiquetas del producto.
                @endif
            </p>
        </div>

        <div class="mt-space-lg flex flex-col gap-space-lg"
             x-data="{
                 imagenPreview: @js($imagenActual),
                 precio: {{ is_numeric($precio) ? (float) $precio : 0 }},
                 categoria: @js((string) $categoriaId),
                 caracteres: {{ mb_strlen($descripcion) }},
                 alergenos: @js($alergenosIniciales),
                 nuevoAlergeno: '',
                 agregarAlergeno() {
                     const valor = this.nuevoAlergeno.trim();

                     if (valor !== '' && ! this.alergenos.some((alergeno) => alergeno.toLowerCase() === valor.toLowerCase())) {
                         this.alergenos.push(valor);
                     }

                     this.nuevoAlergeno = '';
                 },
                 quitarAlergeno(indice) {
                     this.alergenos.splice(indice, 1);
                 }
             }">
            <form id="product-creation-form" method="POST" enctype="multipart/form-data"
                  action="{{ $esEdicion ? '/admin/productos/'.$producto->id : '/admin/productos' }}"
                  class="grid grid-cols-12 gap-gutter items-start">
                @csrf
                @if ($esEdicion)
                    @method('PUT')
                @endif

                <!-- COLUMNA IZQUIERDA: IMAGEN Y ETIQUETAS -->
                <div class="col-span-12 flex flex-col gap-gutter lg:col-span-4">
                    <section class="flex flex-col gap-space-md rounded-[14px] bg-uch-surface p-space-md shadow-sm">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-uch-blue/10 text-uch-blue">
                                <span class="material-symbols-outlined text-[20px]">photo_camera</span>
                            </div>
                            <h2 class="font-rubik text-title-dish text-uch-navy">Imagen del producto</h2>
                        </div>

                        <div class="group relative flex flex-col items-center justify-center overflow-hidden rounded-xl bg-uch-bg p-space-md text-center transition-all">
                            <div class="relative aspect-[4/3] w-full overflow-hidden rounded-lg bg-uch-border shadow-sm">
                                <img :src="imagenPreview" alt="Vista previa del producto"
                                     class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                <div class="absolute inset-0 flex items-center justify-center bg-uch-navy/20 opacity-0 transition-opacity group-hover:opacity-100">
                                    <button type="button" @click="$refs.imagenArchivo.click()"
                                            class="inline-flex items-center gap-1.5 rounded-full bg-uch-surface px-3 py-1.5 text-label-md text-uch-navy shadow-md transition-colors hover:bg-uch-bg">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                        Cambiar foto
                                    </button>
                                </div>
                            </div>

                            <div class="mt-space-md flex w-full flex-col gap-space-xs">
                                <input type="file" name="imagen_archivo" accept=".jpg,.jpeg,.png,.webp"
                                       x-ref="imagenArchivo" class="sr-only"
                                       @change="if ($event.target.files[0]) { imagenPreview = URL.createObjectURL($event.target.files[0]); }">
                                <button type="button" @click="$refs.imagenArchivo.click()"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-uch-blue px-4 py-2 text-label-lg text-white shadow-sm transition-colors hover:bg-uch-navy">
                                    <span class="material-symbols-outlined text-[18px]">cloud_upload</span>
                                    Subir imagen desde PC
                                </button>
                                <p class="pt-1 text-body-sm text-uch-muted">Formatos: JPG, PNG o WebP • Máx. 2 MB</p>
                                @error('imagen_archivo')
                                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 rounded-lg bg-uch-blue/10 p-space-sm">
                            <span class="material-symbols-outlined mt-0.5 shrink-0 text-[20px] text-uch-blue">lightbulb</span>
                            <p class="text-body-sm text-uch-navy">
                                <strong class="text-label-md">Tip UCH:</strong> usa fotos claras y con buena luz natural; esta toma se presentará directamente en el catálogo digital del estudiante.
                            </p>
                        </div>
                    </section>

                    <section class="flex flex-col gap-space-md rounded-[14px] bg-uch-surface p-space-md shadow-sm">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-uch-navy/10 text-uch-navy">
                                <span class="material-symbols-outlined text-[20px]">sell</span>
                            </div>
                            <h2 class="font-rubik text-title-dish text-uch-navy">Visibilidad y Etiquetas</h2>
                        </div>

                        <div class="flex flex-col gap-space-sm">
                            @foreach ($switches as $switch)
                                <label class="flex cursor-pointer select-none items-center justify-between rounded-lg p-2 transition-colors hover:bg-uch-bg">
                                    <div class="pr-2">
                                        <p class="text-label-lg leading-tight text-uch-navy">{{ $switch['titulo'] }}</p>
                                        <p class="text-body-sm text-uch-muted">{{ $switch['detalle'] }}</p>
                                    </div>
                                    <span class="relative shrink-0">
                                        <input type="hidden" name="{{ $switch['nombre'] }}" value="0">
                                        <input type="checkbox" name="{{ $switch['nombre'] }}" value="1" class="peer sr-only" {{ $switch['marcado'] ? 'checked' : '' }}>
                                        <span class="relative block h-6 w-11 rounded-full bg-uch-border transition-colors {{ $switch['color'] }} after:absolute after:left-[2px] after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full"></span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                </div>

                <!-- COLUMNA DERECHA: DATOS PRINCIPALES -->
                <div class="col-span-12 lg:col-span-8">
                    <section class="flex flex-col gap-space-lg rounded-[14px] bg-uch-surface p-space-md shadow-sm sm:p-space-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-uch-navy/10 text-uch-navy">
                                    <span class="material-symbols-outlined text-[20px]">info</span>
                                </div>
                                <div>
                                    <h2 class="font-rubik text-title-dish text-uch-navy">Información del producto</h2>
                                    <p class="text-body-sm text-uch-muted">Campos marcados con asterisco (*) son obligatorios</p>
                                </div>
                            </div>
                            <span class="hidden items-center gap-1 rounded-md bg-uch-bg px-2.5 py-1 text-label-md text-uch-muted sm:inline-flex">
                                <span class="material-symbols-outlined text-[16px] text-uch-blue">verified</span>
                                Campus Los Olivos
                            </span>
                        </div>

                        <div class="flex flex-col gap-space-md">
                            <div class="flex flex-col gap-1.5">
                                <label for="prod-name" class="text-label-lg text-uch-navy">
                                    Nombre del producto <span class="text-uch-coral">*</span>
                                </label>
                                <input id="prod-name" name="nombre" type="text" required maxlength="120" value="{{ $nombre }}"
                                       placeholder="Ej. Sándwich de pollo deshilachado"
                                       class="w-full rounded-lg bg-uch-surface px-3.5 py-2.5 text-body-md text-uch-text shadow-sm focus:outline-none focus:ring-2 focus:ring-uch-blue/30">
                                @error('nombre')
                                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 gap-space-md md:grid-cols-2">
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center justify-between">
                                        <label for="prod-sku" class="text-label-lg text-uch-navy">SKU (Código)</label>
                                        <span class="text-body-sm font-semibold text-uch-blue">Generación automática</span>
                                    </div>
                                    <div class="relative flex items-center">
                                        <span class="material-symbols-outlined pointer-events-none absolute left-3 text-[18px] text-uch-muted">barcode</span>
                                        <input id="prod-sku" type="text" value="{{ $sku }}" readonly
                                               class="w-full cursor-default rounded-lg bg-uch-bg py-2.5 pl-9 pr-9 text-label-md text-uch-muted select-all">
                                        <span class="material-symbols-outlined pointer-events-none absolute right-3 text-[18px] text-uch-blue">lock</span>
                                    </div>
                                    <input type="hidden" name="sku" value="{{ $sku }}">
                                    @error('sku')
                                        <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="flex items-center justify-between text-label-lg text-uch-navy">
                                    <span>Categoría <span class="text-uch-coral">*</span></span>
                                    <span class="text-body-sm font-normal text-uch-muted">Elige una opción</span>
                                </label>
                                <input type="hidden" name="categoria_id" :value="categoria" required>
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4" role="radiogroup" aria-label="Categoría">
                                    @foreach ($categorias as $categoria)
                                        <button type="button" role="radio"
                                                :aria-checked="categoria === '{{ $categoria->id }}' ? 'true' : 'false'"
                                                @click="categoria = '{{ $categoria->id }}'"
                                                :class="categoria === '{{ $categoria->id }}'
                                                    ? 'border-uch-blue bg-uch-blue/10 text-uch-blue'
                                                    : 'border-uch-border bg-uch-surface text-uch-navy hover:border-uch-blue'"
                                                class="flex items-center gap-2 rounded-[10px] border-2 px-3 py-2.5 text-left text-label-lg font-semibold transition-colors">
                                            <span class="min-w-0 flex-1 truncate">{{ $categoria->nombre }}</span>
                                            <span x-show="categoria === '{{ $categoria->id }}'" x-cloak class="material-symbols-outlined text-[18px]">check_circle</span>
                                        </button>
                                    @endforeach
                                </div>
                                @if ($categorias->isEmpty())
                                    <p class="text-body-sm text-uch-muted">No hay categorías activas. Crea una en Categorías.</p>
                                @endif
                                @error('categoria_id')
                                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 gap-space-md sm:grid-cols-3">
                                <div class="flex flex-col gap-1.5">
                                    <label for="prod-price" class="text-label-lg text-uch-navy">
                                        Precio (S/) <span class="text-uch-coral">*</span>
                                    </label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-3.5 font-rubik text-price-tag text-uch-navy">S/</span>
                                        <input id="prod-price" name="precio" type="number" step="0.01" min="0" required value="{{ $precio }}"
                                               x-model.number="precio"
                                               class="w-full rounded-lg bg-uch-surface py-2.5 pl-10 pr-3.5 font-rubik text-price-tag text-uch-navy shadow-sm focus:outline-none focus:ring-2 focus:ring-uch-blue/30">
                                    </div>
                                    @error('precio')
                                        <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center justify-between">
                                        <label for="prod-stock" class="text-label-lg text-uch-navy">
                                            @if ($esEdicion)
                                                Stock actual
                                            @else
                                                Stock inicial <span class="text-uch-coral">*</span>
                                            @endif
                                        </label>
                                        <span class="text-body-sm text-uch-muted">Unidades</span>
                                    </div>
                                    <div class="relative flex items-center">
                                        <span class="material-symbols-outlined pointer-events-none absolute left-3 text-[18px] text-uch-muted">inventory_2</span>
                                        <input id="prod-stock" name="stock_actual" type="number" min="0" required value="{{ $stockActual }}"
                                               class="w-full rounded-lg bg-uch-surface py-2.5 pl-9 pr-3.5 text-body-md text-uch-text shadow-sm focus:outline-none focus:ring-2 focus:ring-uch-blue/30">
                                    </div>
                                    @if ($esEdicion)
                                        <p class="text-body-sm text-uch-muted">Si lo cambias, queda registrado en el historial de movimientos.</p>
                                    @endif
                                    @error('stock_actual')
                                        <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center justify-between">
                                        <label for="prod-min-stock" class="text-label-lg text-uch-navy">Stock mínimo</label>
                                        <span class="text-body-sm text-uch-coral">Alerta</span>
                                    </div>
                                    <div class="relative flex items-center">
                                        <span class="material-symbols-outlined pointer-events-none absolute left-3 text-[18px] text-uch-muted">notifications_active</span>
                                        <input id="prod-min-stock" name="stock_minimo" type="number" min="0" required value="{{ $stockMinimo }}"
                                               class="w-full rounded-lg bg-uch-surface py-2.5 pl-9 pr-3.5 text-body-md text-uch-text shadow-sm focus:outline-none focus:ring-2 focus:ring-uch-blue/30">
                                    </div>
                                    @error('stock_minimo')
                                        <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="prod-allergens" class="flex items-center justify-between text-label-lg text-uch-navy">
                                    <span>Alérgenos conocidos (opcional)</span>
                                    <span class="text-body-sm font-normal text-uch-muted">Información nutricional UCH</span>
                                </label>
                                <div class="flex flex-wrap items-center gap-2 rounded-lg bg-uch-bg p-2.5">
                                    <template x-for="(alergeno, indice) in alergenos" :key="alergeno">
                                        <span class="inline-flex items-center gap-1.5 rounded-md bg-uch-surface px-2.5 py-1 text-label-md text-uch-text shadow-sm">
                                            <span class="material-symbols-outlined text-[15px] text-uch-blue">grain</span>
                                            <span x-text="alergeno"></span>
                                            <button type="button" @click="quitarAlergeno(indice)" aria-label="Quitar alérgeno"
                                                    class="text-uch-muted transition-colors hover:text-uch-coral">
                                                <span class="material-symbols-outlined text-[14px]">close</span>
                                            </button>
                                        </span>
                                    </template>
                                    <input type="hidden" name="alergenos" :value="alergenos.join(', ')">
                                    <input id="prod-allergens" type="text" x-model="nuevoAlergeno" @keydown.enter.prevent="agregarAlergeno()"
                                           placeholder="Añadir otro (ej. maní, soya)..."
                                           class="min-w-[150px] flex-1 bg-transparent px-2 py-1 text-body-sm text-uch-text focus:outline-none">
                                    <button type="button" @click="agregarAlergeno()"
                                            class="rounded-md bg-uch-blue/10 px-2.5 py-1 text-label-md font-semibold text-uch-blue transition-colors hover:bg-uch-blue/20">
                                        Agregar
                                    </button>
                                </div>
                                @error('alergenos')
                                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <div class="flex items-center justify-between">
                                    <label for="prod-desc" class="text-label-lg text-uch-navy">Descripción detallada</label>
                                    <span class="font-mono text-body-sm text-uch-muted" x-text="caracteres + '/300'"></span>
                                </div>
                                <textarea id="prod-desc" name="descripcion" rows="4" maxlength="300" x-model="descripcion"
                                          @input="caracteres = $event.target.value.length"
                                          class="w-full rounded-lg bg-uch-surface p-3 text-body-md leading-relaxed text-uch-text shadow-sm focus:outline-none focus:ring-2 focus:ring-uch-blue/30">{{ $descripcion }}</textarea>
                                @error('descripcion')
                                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-start gap-space-sm rounded-lg bg-uch-bg p-space-sm sm:p-space-md">
                                <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-uch-blue/15 text-uch-navy">
                                    <span class="material-symbols-outlined text-[18px]">history</span>
                                </div>
                                <div class="flex flex-col">
                                    <p class="text-label-md font-bold text-uch-navy">Control de inventario</p>
                                    <p class="text-body-sm text-uch-muted">
                                        @if ($esEdicion)
                                            Si cambias el stock, el ajuste queda registrado en el
                                        @else
                                            El stock inicial queda registrado en el
                                        @endif
                                        <a href="/admin/movimientos" class="font-semibold text-uch-blue hover:underline">historial de movimientos</a>.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-space-md pt-space-md">
                            <a href="/admin/productos" class="inline-flex items-center gap-1.5 rounded px-2 py-1 text-label-md text-uch-muted transition-colors hover:text-uch-coral-dark">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                                Descartar borrador
                            </a>
                            <div class="ml-auto flex items-center gap-space-sm">
                                <a href="/admin/productos"
                                   class="rounded-lg bg-uch-bg px-5 py-2.5 text-label-lg text-uch-navy transition-colors hover:bg-uch-border">
                                    Cancelar
                                </a>
                                <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-lg bg-uch-navy px-6 py-2.5 text-label-lg text-white shadow-sm transition-all hover:bg-uch-blue hover:shadow-md">
                                    <span class="material-symbols-outlined text-[20px]">check</span>
                                    {{ $esEdicion ? 'Guardar cambios' : 'Guardar y publicar' }}
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </form>

            <!-- VISTA PREVIA EN VIVO -->
            <section class="flex flex-col items-center justify-between gap-space-md rounded-[14px] bg-uch-surface p-space-md shadow-sm md:flex-row">
                <div class="flex items-center gap-space-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-uch-blue/10 text-uch-blue">
                        <span class="material-symbols-outlined text-[24px]">devices</span>
                    </div>
                    <div>
                        <h3 class="font-rubik text-title-dish text-uch-navy">Vista previa en vivo para el estudiante</h3>
                        <p class="text-body-sm text-uch-muted">Así es como verán este ítem en el smartphone o kiosco de autoservicio.</p>
                    </div>
                </div>
                <div class="flex w-full items-center justify-end gap-space-md md:w-auto">
                    <div class="flex items-center gap-2 rounded-lg bg-uch-bg px-3 py-1.5">
                        <span class="text-label-md text-uch-muted">Precio final:</span>
                        <span class="font-rubik text-price-tag text-uch-navy" x-text="'S/ ' + Number(precio || 0).toFixed(2)"></span>
                    </div>
                    <a href="/menu"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-uch-blue px-4 py-2 text-label-md text-white transition-colors hover:bg-uch-navy">
                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                        Simular compra
                    </a>
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>

