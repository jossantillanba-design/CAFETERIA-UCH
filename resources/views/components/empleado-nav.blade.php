@auth
    @if (auth()->user()->rol === 'empleado')
        @php
            $enlaces = [
                [
                    'ruta' => route('empleado.tomar-orden.create'),
                    'patron' => 'empleado/tomar-orden*',
                    'etiqueta' => 'Tomar orden',
                    'icono' => 'point_of_sale',
                ],
                [
                    'ruta' => route('empleado.pedidos.index'),
                    'patron' => 'empleado/pedidos*',
                    'etiqueta' => 'Pedidos en vivo',
                    'icono' => 'shopping_bag',
                    'badge' => true,
                ],
                [
                    'ruta' => route('empleado.historial.index'),
                    'patron' => 'empleado/historial*',
                    'etiqueta' => 'Historial de pedidos',
                    'icono' => 'history',
                ],
            ];

            $pendientes = $pedidosPendientes ?? 0;

            $partesNombreEmpleado = preg_split('/\s+/', trim(auth()->user()->name));
            $inicialesEmpleado = mb_strtoupper(mb_substr($partesNombreEmpleado[0] ?? '', 0, 1).mb_substr($partesNombreEmpleado[1] ?? '', 0, 1));
        @endphp

        {{-- Mismo fondo claro que el cliente para todas las pantallas del empleado --}}
        <style>
            body.bg-uch-bg { background-color: var(--hasp-bg, #F9F9F7); }
            .emp-link { color: var(--hasp-muted, #6E6E6E); text-decoration: none; }
            .emp-link:hover { background: var(--hasp-bg, #F9F9F7); color: var(--hasp-text, #1C1C1C); }
            .emp-link.emp-activo { background: var(--hasp-brand-lt, #FDF0E8); color: var(--hasp-brand, #C8511B); font-weight: 600; }
            .emp-salir { color: var(--hasp-brand, #C8511B); background: none; border: 0; cursor: pointer; font-family: inherit; }
            .emp-salir:hover { background: var(--hasp-brand-lt, #FDF0E8); }
        </style>

        <aside class="no-print fixed inset-y-0 left-0 z-50 flex w-[76px] flex-col overflow-hidden lg:w-[248px]"
              style="background: #FFFFFF; border-right: 1px solid var(--hasp-border, #EBEBEB);">
            <div class="flex items-center justify-center gap-3 px-3 lg:justify-start lg:px-4"
                 style="height: 64px; border-bottom: 1px solid var(--hasp-border, #EBEBEB);">
                <span class="flex shrink-0 items-center justify-center"
                      style="width: 36px; height: 36px; border-radius: 8px; background: var(--hasp-brand, #C8511B); color: #fff; font-size: 1.05rem;">☕</span>
                <div class="hidden min-w-0 lg:block" style="line-height: 1.15;">
                    <p class="truncate" style="font-size: 0.875rem; font-weight: 700; letter-spacing: -0.01em; color: var(--hasp-text, #1C1C1C);">GRUPO HASP SERVICE</p>
                    <p class="truncate" style="font-size: 0.65rem; color: var(--hasp-muted, #6E6E6E);">Cafetería UCH · Comedor Universitario</p>
                </div>
            </div>

            <nav class="no-scrollbar flex-1 overflow-y-auto px-2 py-4 lg:px-3" aria-label="Navegación del empleado">
                <p class="mb-2 hidden px-2 text-label-md lg:block" style="color: var(--hasp-subtle, #ABABAB);">Panel del empleado</p>
                <div class="space-y-1">
                    @foreach ($enlaces as $enlace)
                        @php $activo = request()->is($enlace['patron']); @endphp
                        <a href="{{ $enlace['ruta'] }}"
                           @if ($activo) aria-current="page" @endif
                           title="{{ $enlace['etiqueta'] }}"
                           class="emp-link flex items-center justify-center gap-2.5 rounded-lg px-3 py-2.5 text-body-md transition-colors lg:justify-start {{ $activo ? 'emp-activo' : '' }}">
                            <span class="material-symbols-outlined shrink-0 text-[20px]">{{ $enlace['icono'] }}</span>
                            <span class="hidden truncate lg:inline">{{ $enlace['etiqueta'] }}</span>
                            @if (! empty($enlace['badge']) && $pendientes > 0)
                                <span class="hidden rounded-full px-2 py-0.5 text-label-md lg:inline"
                                      style="background: var(--hasp-brand, #C8511B); color: #fff;">{{ $pendientes }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </nav>

            <div class="px-2 py-4 lg:px-4" style="border-top: 1px solid var(--hasp-border, #EBEBEB);">
                <div class="flex items-center justify-center gap-2.5 lg:justify-start">
                    <span class="flex shrink-0 items-center justify-center"
                          style="width: 34px; height: 34px; border-radius: 9999px; background: var(--hasp-brand-lt, #FDF0E8); color: var(--hasp-brand, #C8511B); font-size: 0.75rem; font-weight: 700;">{{ $inicialesEmpleado }}</span>
                    <div class="hidden min-w-0 lg:block">
                        <p class="truncate text-body-md font-semibold" style="color: var(--hasp-text, #1C1C1C);">{{ auth()->user()->name }}</p>
                        <p class="truncate text-body-sm capitalize" style="color: var(--hasp-muted, #6E6E6E);">{{ auth()->user()->rol }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3 hidden lg:block">
                    @csrf
                    <button type="submit" class="emp-salir flex w-full items-center gap-2 rounded-lg px-2 py-2 text-body-md font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                        Cerrar sesión
                    </button>
                </form>
                <form method="POST" action="{{ route('logout') }}" class="mt-3 flex justify-center lg:hidden">
                    @csrf
                    <button type="submit" aria-label="Cerrar sesión" class="emp-salir rounded-lg p-2 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                    </button>
                </form>
            </div>
        </aside>
    @endif
@endauth
