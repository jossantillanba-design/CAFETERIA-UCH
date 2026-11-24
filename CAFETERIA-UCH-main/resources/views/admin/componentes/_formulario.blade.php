@php
    $tiempoSeleccionado = old('tiempo', $componente?->tiempo ?? 'entrada');
    $enModal = (bool) ($modal ?? false);
@endphp

<form method="POST" action="{{ $accion }}"
      class="{{ $enModal ? '' : 'overflow-hidden rounded-card bg-uch-surface shadow-uch-card' }}">
    @csrf
    @if ($metodo === 'PUT')
        @method('PUT')
    @endif

    @unless ($enModal)
        <div class="flex h-2 w-full bg-uch-blue">
            <div class="h-full w-28 bg-uch-cyan"></div>
        </div>
    @endunless

    <div class="space-y-6 {{ $enModal ? 'p-space-lg pt-space-xs' : 'p-6' }}">
        <div class="space-y-1.5">
            <label for="nombre" class="block text-label-md font-bold text-uch-navy">
                Nombre del componente <span class="font-bold text-uch-coral">*</span>
            </label>
            <div class="relative">
                <input id="nombre" name="nombre" type="text" required maxlength="120"
                       value="{{ old('nombre', $componente?->nombre) }}" placeholder="Ej. Ají de gallina, arroz con pollo..."
                       class="w-full rounded-lg border border-uch-border bg-uch-surface py-2.5 pl-3.5 pr-10 text-body-md text-uch-text transition-all focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none">
                <span class="material-symbols-outlined absolute right-3 top-2.5 text-[20px] text-uch-muted">lunch_dining</span>
            </div>
            @error('nombre')
                <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 gap-space-md sm:grid-cols-2">
            <div class="space-y-1.5">
                <label for="tiempo" class="block text-label-md font-bold text-uch-navy">
                    Tiempo del menú <span class="font-bold text-uch-coral">*</span>
                </label>
                <div class="relative">
                    <select id="tiempo" name="tiempo" required
                            class="w-full cursor-pointer appearance-none rounded-lg border border-uch-border bg-uch-surface py-2.5 pl-3.5 pr-10 text-body-md text-uch-text transition-all focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none">
                        <option value="entrada" @selected($tiempoSeleccionado === 'entrada')>Entrada</option>
                        <option value="fondo" @selected($tiempoSeleccionado === 'fondo')>Plato principal</option>
                        <option value="refresco" @selected($tiempoSeleccionado === 'refresco')>Refresco</option>
                    </select>
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[20px] text-uch-muted material-symbols-outlined">expand_more</span>
                </div>
                <p class="text-body-sm text-uch-muted">Define en qué paso del menú se podrá elegir.</p>
                @error('tiempo')
                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label for="descripcion" class="block text-label-md font-bold text-uch-navy">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3" maxlength="200"
                          placeholder="Breve referencia para el estudiante..."
                          class="w-full resize-none rounded-lg border border-uch-border bg-uch-surface px-3.5 py-2 text-body-md text-uch-text transition-all focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none">{{ old('descripcion', $componente?->descripcion) }}</textarea>
                @error('descripcion')
                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-between gap-space-md rounded-xl bg-uch-bg p-3.5">
            <div class="space-y-0.5">
                <p class="text-label-lg font-bold leading-tight text-uch-navy">Disponible en el catálogo</p>
                <p class="text-body-sm leading-snug text-uch-muted">
                    Al desactivarlo se retira de los menús habilitados.
                </p>
            </div>
            <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                <input type="hidden" name="activo_catalogo" value="0">
                <input type="checkbox" name="activo_catalogo" value="1" class="peer sr-only" @checked(old('activo_catalogo', $componente?->activo_catalogo ?? true))>
                <span class="block h-6 w-12 rounded-full bg-uch-border transition-colors after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-uch-teal peer-checked:after:translate-x-full"></span>
            </label>
        </div>

        <p class="rounded-lg border border-uch-border bg-uch-bg px-3.5 py-2.5 text-body-sm text-uch-muted">
            <span class="material-symbols-outlined mr-1 align-[-3px] text-[18px] text-uch-blue">info</span>
            Se habilita desde Menús del día.
        </p>
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-uch-border {{ $enModal ? 'bg-uch-surface px-space-lg py-space-md' : 'bg-uch-bg px-6 py-4' }}">
        @if ($enModal)
            <button type="button" @click="modalAbierto = false"
                    class="rounded-[10px] px-space-md py-2.5 text-label-md font-bold text-uch-muted transition-colors hover:bg-uch-border hover:text-uch-navy">
                Cancelar
            </button>
        @else
            <a href="/admin/componentes"
               class="rounded-[10px] px-space-md py-2.5 text-label-md font-bold text-uch-muted transition-colors hover:bg-uch-border hover:text-uch-navy">
                Cancelar
            </a>
        @endif
        <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-[10px] bg-uch-navy px-space-lg py-2.5 text-label-md font-bold text-white shadow-md transition-all hover:bg-uch-blue hover:shadow-lg">
            <span class="material-symbols-outlined text-[18px]">save</span>
            {{ $boton }}
        </button>
    </div>
</form>
