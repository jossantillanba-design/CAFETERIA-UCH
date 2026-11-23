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
        @endphp

        <aside class="no-print fixed inset-y-0 left-0 z-50 flex w-[76px] flex-col overflow-hidden border-r border-white/5 bg-uch-navy shadow-uch-card lg:w-[248px]">
            <div class="flex items-center gap-3 bg-uch-navy-dark px-3 py-5 lg:px-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-uch-teal font-rubik font-bold text-white">UCH</span>
                <div class="hidden min-w-0 lg:block">
                    <p class="truncate font-rubik text-body-md font-bold text-white">Comedor Operación</p>
                    <p class="truncate text-body-sm text-white/60">Campus Los Olivos</p>
                </div>
            </div>

            <nav class="no-scrollbar flex-1 overflow-y-auto px-2 py-4 lg:px-3" aria-label="Navegación del empleado">
                <p class="mb-2 hidden px-2 text-label-md uppercase tracking-widest text-white/45 lg:block">Operación</p>
                <div class="space-y-1">
                    @foreach ($enlaces as $enlace)
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
                    @endforeach
                </div>
            </nav>

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
