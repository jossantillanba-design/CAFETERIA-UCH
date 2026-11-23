<x-layouts.app>
    <x-admin-nav />

    <div class="mx-auto flex w-full max-w-3xl flex-col gap-space-lg px-4 py-6 sm:px-6 lg:px-8">
        <x-flash-message />

        <header class="space-y-1">
            <div class="flex items-center gap-space-xs text-label-md uppercase tracking-wider text-uch-blue">
                <span class="material-symbols-outlined text-[18px]">label</span>
                <span>Menús y Catálogo / Categorías / Editar</span>
            </div>
            <h1 class="font-rubik text-headline-lg-mobile tracking-tight text-uch-navy lg:text-headline-lg">Editar categoría</h1>
            <p class="text-body-md text-uch-muted">{{ $categoria->nombre }}</p>
        </header>

        @include('admin.categorias._formulario', [
            'accion' => route('categorias.update', $categoria),
            'metodo' => 'PUT',
            'categoria' => $categoria,
            'boton' => 'Guardar cambios',
        ])
    </div>
</x-layouts.app>
