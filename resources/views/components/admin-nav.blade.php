@auth
    @if (in_array(auth()->user()->rol, ['administrador', 'empleado'], true))
        @php
            $secciones = [
                [
                    'titulo' => 'Resumen',
                    'enlaces' => [
                        ['ruta' => '/admin/dashboard', 'patron' => 'admin/dashboard*', 'etiqueta' => 'Inicio', 'icono' => 'dashboard', 'roles' => ['administrador']],
                        ['ruta' => '/admin/pedidos', 'patron' => 'admin/pedidos*', 'etiqueta' => 'Pedidos en vivo', 'icono' => 'shopping_bag', 'roles' => ['administrador', 'empleado'], 'badge' => true],
                    ],
                ],
                [
                    'titulo' => 'Menús y catálogo',
                    'enlaces' => [
                        ['ruta' => '/admin/menus', 'patron' => 'admin/menus*', 'etiqueta' => 'Menús del día', 'icono' => 'restaurant', 'roles' => ['administrador']],
                        ['ruta' => '/admin/menus#componentes', 'patron' => 'admin/menus*', 'etiqueta' => 'Componentes de menú', 'icono' => 'format_list_bulleted', 'roles' => ['administrador']],
                        ['ruta' => '/admin/productos', 'patron' => 'admin/productos*', 'etiqueta' => 'Productos', 'icono' => 'local_cafe', 'roles' => ['administrador']],
                        ['ruta' => '/admin/categorias', 'patron' => 'admin/categorias*', 'etiqueta' => 'Categorías', 'icono' => 'label', 'roles' => ['administrador']],
                    ],
                ],
                [
                    'titulo' => 'Control y operación',
                    'enlaces' => [
                        ['ruta' => '/admin/inventario', 'patron' => 'admin/inventario', 'etiqueta' => 'Inventario', 'icono' => 'inventory_2', 'roles' => ['administrador']],
                        ['ruta' => '/admin/inventario#historial', 'patron' => 'admin/inventario*', 'etiqueta' => 'Historial de movimientos', 'icono' => 'history', 'roles' => ['administrador']],
                        ['ruta' => '/admin/clientes', 'patron' => 'admin/clientes*', 'etiqueta' => 'Clientes', 'icono' => 'group', 'roles' => ['administrador']],
                    ],
                ],
            ];

            $pendientes = $pedidosPendientes ?? 0;
        @endphp

        <aside class="no-print fixed inset-y-0 left-0 z-50 flex w-[76px] flex-col overflow-hidden border-r border-white/5 bg-uch-navy shadow-uch-card lg:w-[248px]">
            <!-- Marca -->
            <div class="flex items-center gap-3 bg-uch-navy-dark px-3 py-5 lg:px-4">
                <a href="/" aria-label="Ir al inicio" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white font-rubik font-bold text-uch-navy">UCH</a>
                <div class="hidden min-w-0 lg:block">
                    <p class="truncate font-rubik text-body-md font-bold text-white">Comedor Admin</p>
                    <p class="truncate text-body-sm text-white/60">Campus Los Olivos</p>
                </div>
            </div>

            <!-- Navegación -->
            <nav class="no-scrollbar flex-1 overflow-y-auto px-2 py-4 lg:px-3">
                @foreach ($secciones as $seccion)
                    @php
                        $tieneEnlaces = collect($seccion['enlaces'])
                            ->contains(fn (array $enlace): bool => in_array(auth()->user()->rol, $enlace['roles'], true));
                    @endphp

                    @if ($tieneEnlaces)
                        <p class="mb-2 hidden px-2 text-label-md uppercase tracking-widest text-white/45 lg:block">{{ $seccion['titulo'] }}</p>
                        <div class="mb-4 space-y-1 lg:mb-5">
                            @foreach ($seccion['enlaces'] as $enlace)
                                @if (in_array(auth()->user()->rol, $enlace['roles'], true))
                                    @php $activo = request()->is($enlace['patron']); @endphp
                                    <a href="{{ $enlace['ruta'] }}"
                                       @if ($activo) aria-current="page" @endif
                                       title="{{ $enlace['etiqueta'] }}"
                                       class="flex items-center justify-center gap-2.5 rounded-lg px-3 py-2.5 text-body-sm transition-colors lg:justify-start {{ $activo ? 'bg-uch-blue font-semibold text-white shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                        <span class="material-symbols-outlined shrink-0 text-[20px]">{{ $enlace['icono'] }}</span>
                                        <span class="hidden truncate lg:inline">{{ $enlace['etiqueta'] }}</span>
                                        @if (! empty($enlace['badge']) && $pendientes > 0)
                                            <span class="hidden rounded-full bg-uch-coral px-2 py-0.5 text-label-md font-bold text-white lg:inline">{{ $pendientes }}</span>
                                        @endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </nav>

            <!-- Usuario -->
            <div class="bg-uch-navy-dark px-2 py-4 lg:px-4">
                <div class="flex items-center justify-center gap-2.5 lg:justify-start">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10">
                        <span class="material-symbols-outlined text-[19px] text-white">person</span>
                    </div>
                    <div class="hidden min-w-0 lg:block">
                        <p class="truncate text-body-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate text-body-sm capitalize text-white/55">{{ auth()->user()->rol }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3 hidden lg:block">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 text-body-sm text-white/70 transition-colors hover:text-white">
                        <span class="material-symbols-outlined text-[16px]">logout</span>
                        Cerrar sesión
                    </button>
                </form>
                <form method="POST" action="{{ route('logout') }}" class="mt-3 flex justify-center lg:hidden">
                    @csrf
                    <button type="submit" aria-label="Cerrar sesión" class="rounded-lg p-2 text-white/70 transition-colors hover:bg-white/10 hover:text-white">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                    </button>
                </form>
            </div>
        </aside>
    @endif
@endauth
