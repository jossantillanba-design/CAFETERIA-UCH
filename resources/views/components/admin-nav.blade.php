@auth
    @if (in_array(auth()->user()->rol, ['administrador'], true))
        @php
            $secciones = [
                [
                    'titulo' => 'Resumen',
                    'enlaces' => [
                        ['ruta' => '/admin/dashboard', 'patron' => 'admin/dashboard*', 'etiqueta' => 'Inicio', 'icono' => 'dashboard', 'roles' => ['administrador']],
                        ['ruta' => '/admin/pedidos', 'patron' => 'admin/pedidos*', 'etiqueta' => 'Pedidos en vivo', 'icono' => 'shopping_bag', 'roles' => ['administrador'], 'badge' => true],
                        ['ruta' => '/admin/historial-pedidos', 'patron' => 'admin/historial-pedidos*', 'etiqueta' => 'Historial de pedidos', 'icono' => 'history', 'roles' => ['administrador']],
                    ],
                ],
                [
                    'titulo' => 'Menús y catálogo',
                    'enlaces' => [
                        ['ruta' => '/admin/menus', 'patron' => 'admin/menus*', 'etiqueta' => 'Menús del día', 'icono' => 'restaurant', 'roles' => ['administrador']],
                        ['ruta' => '/admin/componentes', 'patron' => 'admin/componentes*', 'etiqueta' => 'Componentes del menú', 'icono' => 'format_list_bulleted', 'roles' => ['administrador']],
                        ['ruta' => '/admin/productos', 'patron' => 'admin/productos*', 'etiqueta' => 'Productos', 'icono' => 'local_cafe', 'roles' => ['administrador']],
                        ['ruta' => '/admin/categorias', 'patron' => 'admin/categorias*', 'etiqueta' => 'Categorías', 'icono' => 'label', 'roles' => ['administrador']],
                    ],
                ],
                [
                    'titulo' => 'Control y operación',
                    'enlaces' => [
                        ['ruta' => '/admin/inventario', 'patron' => 'admin/inventario', 'etiqueta' => 'Inventario', 'icono' => 'inventory_2', 'roles' => ['administrador']],
                        ['ruta' => '/admin/movimientos', 'patron' => 'admin/movimientos*', 'etiqueta' => 'Historial de movimientos', 'icono' => 'history', 'roles' => ['administrador']],
                        ['ruta' => '/admin/clientes', 'patron' => 'admin/clientes*', 'etiqueta' => 'Clientes', 'icono' => 'group', 'roles' => ['administrador']],
                    ],
                ],
            ];

            $pendientes = $pedidosPendientes ?? 0;
        @endphp

        {{-- Mismo tema claro que el empleado y el cliente (blanco, naranja de marca y fondo cálido) --}}
        <style>
            body.bg-uch-bg { background-color: var(--hasp-bg, #F9F9F7); }
            .adm-link { color: var(--hasp-muted, #6E6E6E); text-decoration: none; }
            .adm-link:hover { background: var(--hasp-bg, #F9F9F7); color: var(--hasp-text, #1C1C1C); }
            .adm-link.adm-activo { background: var(--hasp-brand-lt, #FDF0E8); color: var(--hasp-brand, #C8511B); font-weight: 600; }
            .adm-salir { color: var(--hasp-brand, #C8511B); background: none; border: 0; cursor: pointer; font-family: inherit; }
            .adm-salir:hover { background: var(--hasp-brand-lt, #FDF0E8); }

            /* Las pantallas del admin conservan su estructura; solo cambia la paleta al tema del empleado. */
            body:has(aside.adm-shell) :is(h1, h2, h3, h4, h5) { color: var(--hasp-text, #1C1C1C); }
            body:has(aside.adm-shell) .text-uch-navy { color: var(--hasp-text, #1C1C1C); }
            body:has(aside.adm-shell) .text-uch-blue,
            body:has(aside.adm-shell) .hover\:text-uch-blue:hover,
            body:has(aside.adm-shell) .hover\:text-uch-navy:hover { color: var(--hasp-brand, #C8511B); }
            body:has(aside.adm-shell) :is(.bg-uch-navy, .bg-uch-blue, .bg-uch-cyan, .hover\:bg-uch-blue:hover, .hover\:bg-uch-navy:hover, .hover\:bg-uch-navy-dark:hover, .hover\:bg-uch-coral-dark:hover, .bg-uch-coral) { background-color: var(--hasp-brand, #C8511B); color: #fff; }
            body:has(aside.adm-shell) :is(.bg-uch-blue\/10, .bg-uch-blue\/5, .bg-uch-blue\/15, .bg-uch-cyan\/10, .bg-uch-cyan\/15, .hover\:bg-uch-blue\/10:hover, .hover\:bg-uch-blue\/20:hover) { background-color: var(--hasp-brand-lt, #FDF0E8); }
            body:has(aside.adm-shell) :is(.border-uch-blue, .border-t-uch-blue, .hover\:border-uch-blue:hover, .focus\:border-uch-blue:focus) { border-color: var(--hasp-brand, #C8511B); }
            body:has(aside.adm-shell) :is(.focus\:ring-uch-blue:focus, .focus\:ring-uch-blue\/30:focus) { --tw-ring-color: var(--hasp-brand, #C8511B); }
            body:has(aside.adm-shell) .bg-\[\#E8F1FA\] { background-color: var(--hasp-brand-lt, #FDF0E8); color: var(--hasp-brand, #C8511B); }
            body:has(aside.adm-shell) .bg-\[\#E8F1FA\] th { color: var(--hasp-brand, #C8511B); }
            body:has(aside.adm-shell) .text-\[\#0060A9\] { color: var(--hasp-brand, #C8511B); }
            body:has(aside.adm-shell) .text-uch-coral-dark { color: #B3261E; }
            body:has(aside.adm-shell) .peer:checked ~ .peer-checked\:bg-uch-blue { background-color: var(--hasp-brand, #C8511B); }
        </style>

        <aside class="adm-shell no-print fixed inset-y-0 left-0 z-50 flex w-[76px] flex-col overflow-hidden lg:w-[248px]"
               style="background: #FFFFFF; border-right: 1px solid var(--hasp-border, #EBEBEB);">
            <!-- Marca -->
            <div class="flex items-center justify-center gap-3 px-3 lg:justify-start lg:px-4"
                 style="height: 64px; border-bottom: 1px solid var(--hasp-border, #EBEBEB);">
                <a href="/" aria-label="Ir al inicio" class="flex shrink-0 items-center justify-center"
                   style="width: 36px; height: 36px; border-radius: 8px; background: var(--hasp-brand, #C8511B); color: #fff; font-size: 1.05rem; text-decoration: none;">☕</a>
                <div class="hidden min-w-0 lg:block" style="line-height: 1.15;">
                    <p class="truncate" style="font-size: 0.875rem; font-weight: 700; letter-spacing: -0.01em; color: var(--hasp-text, #1C1C1C);">Comedor Admin</p>
                    <p class="truncate" style="font-size: 0.65rem; color: var(--hasp-muted, #6E6E6E);">Campus Los Olivos</p>
                </div>
            </div>

            <!-- Navegación -->
            <nav class="no-scrollbar flex-1 overflow-y-auto px-2 py-4 lg:px-3" aria-label="Navegación del administrador">
                @foreach ($secciones as $seccion)
                    @php
                        $tieneEnlaces = collect($seccion['enlaces'])
                            ->contains(fn (array $enlace): bool => in_array(auth()->user()->rol, $enlace['roles'], true));
                    @endphp

                    @if ($tieneEnlaces)
                        <p class="mb-2 hidden px-2 text-label-md lg:block" style="color: var(--hasp-subtle, #ABABAB);">{{ $seccion['titulo'] }}</p>
                        <div class="mb-4 space-y-1 lg:mb-5">
                            @foreach ($seccion['enlaces'] as $enlace)
                                @if (in_array(auth()->user()->rol, $enlace['roles'], true))
                                    @php $activo = request()->is($enlace['patron']); @endphp
                                    <a href="{{ $enlace['ruta'] }}"
                                       @if ($activo) aria-current="page" @endif
                                       title="{{ $enlace['etiqueta'] }}"
                                       class="adm-link flex items-center justify-center gap-2.5 rounded-lg px-3 py-2.5 text-body-md transition-colors lg:justify-start {{ $activo ? 'adm-activo' : '' }}">
                                        <span class="material-symbols-outlined shrink-0 text-[20px]">{{ $enlace['icono'] }}</span>
                                        <span class="hidden truncate lg:inline">{{ $enlace['etiqueta'] }}</span>
                                        @if (! empty($enlace['badge']) && $pendientes > 0)
                                            <span class="hidden rounded-full px-2 py-0.5 text-label-md lg:inline"
                                                  style="background: var(--hasp-brand, #C8511B); color: #fff;">{{ $pendientes }}</span>
                                        @endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </nav>

            <!-- Usuario -->
            <div class="px-2 py-4 lg:px-4" style="border-top: 1px solid var(--hasp-border, #EBEBEB);">
                <div class="flex items-center justify-center gap-2.5 lg:justify-start">
                    <span class="flex shrink-0 items-center justify-center"
                          style="width: 34px; height: 34px; border-radius: 9999px; background: var(--hasp-brand-lt, #FDF0E8); color: var(--hasp-brand, #C8511B);">
                        <span class="material-symbols-outlined text-[19px]">person</span>
                    </span>
                    <div class="hidden min-w-0 lg:block">
                        <p class="truncate text-body-md font-semibold" style="color: var(--hasp-text, #1C1C1C);">{{ auth()->user()->name }}</p>
                        <p class="truncate text-body-sm capitalize" style="color: var(--hasp-muted, #6E6E6E);">{{ auth()->user()->rol }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3 hidden lg:block">
                    @csrf
                    <button type="submit" class="adm-salir flex w-full items-center gap-2 rounded-lg px-2 py-2 text-body-md font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                        Cerrar sesión
                    </button>
                </form>
                <form method="POST" action="{{ route('logout') }}" class="mt-3 flex justify-center lg:hidden">
                    @csrf
                    <button type="submit" aria-label="Cerrar sesión" class="adm-salir rounded-lg p-2 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                    </button>
                </form>
            </div>
        </aside>
    @endif
@endauth
