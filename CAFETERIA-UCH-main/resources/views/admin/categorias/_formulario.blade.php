@php
    $paleta = [
        ['hex' => '#1B3668', 'nombre' => 'Azul Marino'],
        ['hex' => '#0066B3', 'nombre' => 'Azul Medio'],
        ['hex' => '#00AEEF', 'nombre' => 'Cian Solar'],
        ['hex' => '#00B4A6', 'nombre' => 'Turquesa Campus'],
        ['hex' => '#F59E0B', 'nombre' => 'Ámbar Cálido'],
        ['hex' => '#E63946', 'nombre' => 'Rojo Coral'],
    ];
    $colorSeleccionado = old('color_hex', $categoria?->color_hex ?? '#00AEEF');
@endphp

<form method="POST" action="{{ $accion }}" class="overflow-hidden rounded-card bg-uch-surface shadow-uch-card">
    @csrf
    @if ($metodo === 'PUT')
        @method('PUT')
    @endif
    @if ($categoria)
        <input type="hidden" name="slug" value="{{ old('slug', $categoria->slug) }}">
    @endif

    <div class="flex h-2 w-full bg-uch-blue">
        <div class="h-full w-28 bg-uch-cyan"></div>
    </div>

    <div class="space-y-6 p-6">
        <div class="space-y-1.5">
            <label for="nombre" class="block text-label-md font-bold text-uch-navy">
                Nombre de la categoría <span class="font-bold text-uch-coral">*</span>
            </label>
            <div class="relative">
                <input id="nombre" name="nombre" type="text" required maxlength="80"
                       value="{{ old('nombre', $categoria?->nombre) }}" placeholder="Ej. Platos a la Carta"
                       class="w-full rounded-lg border border-uch-border bg-uch-surface py-2.5 pl-3.5 pr-10 text-body-md text-uch-text transition-all focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none">
                <span class="material-symbols-outlined absolute right-3 top-2.5 text-[20px] text-uch-muted">lunch_dining</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-space-md sm:grid-cols-2">
            <div class="space-y-1.5">
                <label for="orden" class="block text-label-md font-bold text-uch-navy">Orden en catálogo</label>
                <div class="relative">
                    <input id="orden" name="orden" type="number" min="0" max="9999"
                           value="{{ old('orden', $categoria?->orden ?? 0) }}"
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

        <div class="space-y-2">
            <label class="block text-label-md font-bold text-uch-navy">Color identificativo en tarjeta y filtros</label>
            <div class="flex flex-wrap items-center gap-3 rounded-xl bg-uch-bg p-3">
                @foreach ($paleta as $color)
                    <label class="relative cursor-pointer" title="{{ $color['nombre'] }}">
                        <input type="radio" name="color_hex" value="{{ $color['hex'] }}" class="peer sr-only" @checked($colorSeleccionado === $color['hex'])>
                        <span class="block h-8 w-8 rounded-full transition-transform hover:scale-110 peer-checked:scale-105 peer-checked:ring-2 peer-checked:ring-offset-2"
                              style="background-color: {{ $color['hex'] }}; --tw-ring-color: {{ $color['hex'] }}"></span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="space-y-1.5">
            <label for="descripcion" class="block text-label-md font-bold text-uch-navy">
                Descripción corta <span class="font-normal text-uch-muted">(opcional)</span>
            </label>
            <textarea id="descripcion" name="descripcion" rows="2" maxlength="255"
                      placeholder="Breve referencia para los alumnos..."
                      class="w-full resize-none rounded-lg border border-uch-border bg-uch-surface px-3.5 py-2 text-body-md text-uch-text transition-all focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none">{{ old('descripcion', $categoria?->descripcion) }}</textarea>
        </div>

        <div class="flex items-center justify-between gap-space-md rounded-xl bg-uch-bg p-3.5">
            <div class="space-y-0.5">
                <p class="text-label-lg font-bold leading-tight text-uch-navy">Categoría visible para clientes</p>
                <p class="text-body-sm leading-snug text-uch-muted">
                    Los estudiantes podrán ver y filtrar por esta sección en el catálogo digital.
                </p>
            </div>
            <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" class="peer sr-only" @checked(old('activo', $categoria?->activo ?? true))>
                <span class="block h-6 w-12 rounded-full bg-uch-border transition-colors after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-uch-teal peer-checked:after:translate-x-full"></span>
            </label>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-uch-border bg-uch-bg px-6 py-4">
        <a href="{{ route('categorias.index') }}"
           class="rounded-[10px] px-space-md py-2.5 text-label-md font-bold text-uch-muted transition-colors hover:bg-uch-border hover:text-uch-navy">
            Cancelar
        </a>
        <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-[10px] bg-uch-navy px-space-lg py-2.5 text-label-md font-bold text-white shadow-md transition-all hover:bg-uch-blue hover:shadow-lg">
            <span class="material-symbols-outlined text-[18px]">save</span>
            {{ $boton }}
        </button>
    </div>
</form>
