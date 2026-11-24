<x-layouts.app>
    <x-admin-nav />

    <div class="mx-auto flex w-full max-w-3xl flex-col gap-space-lg px-4 py-6 sm:px-6 lg:px-8">
        <x-flash-message />

        <header class="space-y-1">
            <div class="flex items-center gap-space-xs text-label-md uppercase tracking-wider text-uch-blue">
                <span class="material-symbols-outlined text-[18px]">format_list_bulleted</span>
                <span>Menús y Catálogo / Componentes del menú / Editar</span>
            </div>
            <h1 class="font-rubik text-headline-lg-mobile tracking-tight text-uch-navy lg:text-headline-lg">Editar componente</h1>
            <p class="text-body-md text-uch-muted">
                Nombre, tiempo y descripción del componente.
            </p>
        </header>

        @include('admin.componentes._formulario', [
            'accion' => '/admin/componentes/'.$componente->id,
            'metodo' => 'PUT',
            'componente' => $componente,
            'boton' => 'Guardar cambios',
        ])
    </div>
</x-layouts.app>
