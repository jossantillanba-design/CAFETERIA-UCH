<x-layouts.app>
    <x-empleado-nav />

    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">
        <x-flash-message />

        @php
            $estilosEstado = [
                'entregado' => ['etiqueta' => 'bg-uch-teal/15 text-[#008779]', 'punto' => 'bg-uch-teal', 'texto' => 'Entregado'],
                'cancelado' => ['etiqueta' => 'bg-uch-coral/10 text-uch-coral-dark', 'punto' => 'bg-uch-coral', 'texto' => 'Anulado'],
            ];

            $metodosPago = [
                'efectivo' => 'Efectivo',
                'yape_plin' => 'Yape/Plin',
                'tarjeta' => 'Tarjeta POS',
            ];

            $origenes = [
                'todos' => 'Todos los orígenes',
                'web' => 'Solo web',
                'caja' => 'Solo mostrador',
            ];

            $metodosFiltro = [
                'todos' => 'Todos los métodos',
                'efectivo' => 'Efectivo',
                'yape_plin' => 'Yape/Plin',
                'tarjeta' => 'Tarjeta POS',
            ];

            $tarjetasArqueo = [
                ['etiqueta' => 'Efectivo', 'valor' => $arqueo['efectivo'], 'icono' => 'payments', 'acento' => 'bg-uch-teal'],
                ['etiqueta' => 'Yape/Plin', 'valor' => $arqueo['billeteras'], 'icono' => 'account_balance_wallet', 'acento' => 'bg-uch-blue'],
                ['etiqueta' => 'Tarjeta POS', 'valor' => $arqueo['tarjeta'], 'icono' => 'credit_card', 'acento' => 'bg-uch-cyan'],
            ];
        @endphp

        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="flex items-center gap-2 text-label-md font-bold uppercase tracking-widest text-uch-teal">
                    <span class="material-symbols-outlined text-[18px]">history</span>
                    Módulo 3 · Historial y arqueo
                </p>
                <h1 class="mt-1 font-rubik text-headline-lg-mobile tracking-tight text-uch-navy lg:text-headline-lg">Historial de pedidos</h1>
                <p class="text-body-md text-uch-muted">
                    Operaciones finalizadas del {{ \Illuminate\Support\Carbon::parse($fechaHistorial)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}.
                </p>
            </div>
            <a href="{{ route('empleado.pedidos.index') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-[10px] border border-uch-border bg-uch-surface px-4 py-2.5 text-label-lg font-semibold text-uch-navy transition-colors hover:border-uch-blue">
                <span class="material-symbols-outlined text-[20px]">shopping_bag</span>
                Pedidos en vivo
            </a>
        </header>

        <section class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Arqueo de la fecha">
            @foreach ($tarjetasArqueo as $tarjeta)
                <article class="relative overflow-hidden rounded-card bg-uch-surface p-5 shadow-uch-card">
                    <div class="absolute inset-y-0 left-0 w-1 {{ $tarjeta['acento'] }}"></div>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-label-md font-semibold uppercase tracking-wider text-uch-muted">{{ $tarjeta['etiqueta'] }}</p>
                            <p class="mt-2 font-rubik text-headline-md text-uch-navy">S/ {{ number_format((float) $tarjeta['valor'], 2) }}</p>
                        </div>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-uch-bg text-uch-blue">
                            <span class="material-symbols-outlined text-[20px]">{{ $tarjeta['icono'] }}</span>
                        </span>
                    </div>
                </article>
            @endforeach

            <article class="relative overflow-hidden rounded-card bg-uch-navy p-5 text-white shadow-uch-card">
                <div class="absolute inset-y-0 left-0 w-1 bg-uch-coral"></div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-label-md font-semibold uppercase tracking-wider text-white/70">Tickets entregados</p>
                        <p class="mt-2 font-rubik text-headline-md">{{ (int) $arqueo['tickets'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/10">
                        <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                    </span>
                </div>
            </article>
        </section>

        <section class="mt-5 rounded-card bg-uch-surface p-5 shadow-uch-card">
            <form method="GET" action="{{ route('empleado.historial.index') }}" class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 xl:grid-cols-12">
                <label class="block xl:col-span-4">
                    <span class="mb-1 block text-label-md font-semibold text-uch-muted">Búsqueda rápida</span>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3.5 text-[20px] text-uch-muted">search</span>
                        <input id="buscar-historial" name="buscar" type="search" value="{{ $buscar }}"
                               placeholder="#ticket, nombre o código de operación"
                               class="w-full rounded-lg bg-uch-bg py-2.5 pl-10 pr-4 text-body-md text-uch-text placeholder:text-uch-muted focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                    </div>
                </label>

                <label class="block xl:col-span-2">
                    <span class="mb-1 block text-label-md font-semibold text-uch-muted">Fecha</span>
                    <input id="fecha-historial" name="fecha" type="date" value="{{ $fechaHistorial }}"
                           class="w-full rounded-lg bg-uch-bg px-3 py-2.5 text-body-md font-semibold text-uch-navy focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                </label>

                <label class="block xl:col-span-3">
                    <span class="mb-1 block text-label-md font-semibold text-uch-muted">Origen</span>
                    <select id="origen-historial" name="origen"
                            class="w-full rounded-lg bg-uch-bg px-3 py-2.5 text-body-md text-uch-navy focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                        @foreach ($origenes as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected($origenFiltro === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block xl:col-span-3">
                    <span class="mb-1 block text-label-md font-semibold text-uch-muted">Método de pago</span>
                    <select id="metodo-historial" name="metodo_pago"
                            class="w-full rounded-lg bg-uch-bg px-3 py-2.5 text-body-md text-uch-navy focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                        @foreach ($metodosFiltro as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected($metodoFiltro === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="flex items-center gap-3 md:col-span-2 xl:col-span-12">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-uch-blue px-4 py-2.5 text-label-lg font-bold text-white transition-colors hover:bg-uch-navy">
                        <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                        Aplicar filtros
                    </button>
                    <a href="{{ route('empleado.historial.index') }}" class="text-label-md font-semibold text-uch-muted transition-colors hover:text-uch-blue">Limpiar</a>
                </div>
            </form>
        </section>

        <section class="mt-5 overflow-hidden rounded-card bg-uch-surface shadow-uch-card">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-body-md">
                    <thead>
                        <tr class="bg-uch-bg text-label-lg text-uch-navy">
                            <th class="px-4 py-3 font-bold">Ticket</th>
                            <th class="px-4 py-3 font-bold">Cliente</th>
                            <th class="px-4 py-3 font-bold">Resumen</th>
                            <th class="px-4 py-3 font-bold">Pago</th>
                            <th class="px-4 py-3 font-bold">Total</th>
                            <th class="px-4 py-3 font-bold">Estado</th>
                            <th class="px-4 py-3 font-bold">Hora</th>
                            <th class="px-4 py-3 text-right font-bold">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-uch-border">
                        @forelse ($historial as $pedido)
                            @php
                                $estilo = $estilosEstado[$pedido->estado] ?? $estilosEstado['entregado'];
                                $nombreCliente = $pedido->cliente_nombre_caja ?: ($pedido->cliente->name ?? 'Mostrador');
                                $esCaja = $pedido->origen === \App\Models\Pedido::ORIGEN_CAJA;
                            @endphp
                            <tr class="transition-colors hover:bg-uch-bg">
                                <td class="whitespace-nowrap px-4 py-3.5 font-rubik text-label-lg font-bold text-uch-navy">
                                    #{{ str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT) }}
                                    @if ($pedido->codigo_transaccion)
                                        <span class="block text-body-sm font-normal text-uch-muted">{{ $pedido->codigo_transaccion }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="block text-label-lg font-semibold text-uch-text">{{ $nombreCliente }}</span>
                                    <span class="text-body-sm text-uch-muted">{{ $esCaja ? 'Mostrador' : 'Web' }}</span>
                                </td>
                                <td class="max-w-[280px] px-4 py-3.5 text-body-sm text-uch-text">
                                    <span class="block truncate" title="{{ $pedido->resumen }}">{{ $pedido->resumen }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-uch-bg px-2.5 py-1 text-label-md text-uch-muted">
                                        <span class="material-symbols-outlined text-[14px] text-uch-blue">payments</span>
                                        {{ $metodosPago[$pedido->metodo_pago] ?? ($pedido->metodo_pago ?: 'Sin registro') }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 font-rubik text-body-md font-bold text-uch-navy">S/ {{ number_format((float) $pedido->total, 2) }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-label-md font-bold {{ $estilo['etiqueta'] }}">
                                        <span class="h-2 w-2 rounded-full {{ $estilo['punto'] }}"></span>
                                        {{ $estilo['texto'] }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-body-sm text-uch-muted">{{ $pedido->updated_at->format('h:i a') }}</td>
                                <td class="px-4 py-3.5 text-right">
                                    <a href="{{ route('empleado.pedidos.ticket', $pedido) }}"
                                       class="inline-flex items-center gap-1 rounded-md px-3 py-1 text-label-md font-bold text-uch-blue transition-colors hover:bg-uch-bg">
                                        Ver ticket
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-body-sm text-uch-muted">
                                    No hay pedidos finalizados para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 bg-uch-bg px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-body-sm text-uch-muted">
                    Mostrando <strong class="text-uch-navy">{{ $historial->count() }}</strong> de
                    <strong class="text-uch-navy">{{ $historial->total() }}</strong> pedidos finalizados
                </p>
                {{ $historial->links() }}
            </div>
        </section>
    </div>
</x-layouts.app>
