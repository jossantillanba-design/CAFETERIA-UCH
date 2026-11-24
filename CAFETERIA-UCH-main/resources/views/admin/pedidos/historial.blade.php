<x-layouts.app>
    <x-admin-nav />

    <div class="mx-auto flex w-full max-w-[1600px] flex-col gap-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-flash-message />

        @php
            $estilosEstado = [
                'entregado' => ['etiqueta' => 'bg-emerald-50 text-emerald-700', 'punto' => 'bg-uch-teal', 'texto' => 'Entregado'],
                'cancelado' => ['etiqueta' => 'bg-uch-coral/10 text-uch-coral-dark', 'punto' => 'bg-uch-coral', 'texto' => 'Cancelado'],
            ];
        @endphp

        <!-- CABECERA DE SECCIÓN -->
        <header class="flex flex-col gap-space-md md:flex-row md:items-center md:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-space-xs text-label-md uppercase tracking-wider text-uch-blue">
                    <span class="material-symbols-outlined text-[18px]">history</span>
                    <span>Operación / Historial de pedidos</span>
                </div>
                <h1 class="font-rubik text-headline-lg-mobile tracking-tight text-uch-navy lg:text-headline-lg">Historial de pedidos</h1>
                <p class="text-body-md text-uch-muted">
                    Entregas y tickets cancelados por fecha.
                </p>
            </div>
            <a href="/admin/pedidos"
               class="inline-flex shrink-0 items-center gap-space-xs rounded-[10px] border border-uch-border bg-uch-surface px-5 py-2.5 text-label-lg font-semibold text-uch-navy transition-all hover:bg-uch-bg hover:border-uch-blue">
                <span class="material-symbols-outlined text-[20px]">shopping_bag</span>
                Ir a pedidos en vivo
            </a>
        </header>

        <!-- BÚSQUEDA Y FECHA -->
        <section class="rounded-card bg-uch-surface p-5 shadow-sm">
            <form method="GET" action="/admin/historial-pedidos" class="grid grid-cols-12 items-end gap-space-md">
                <div class="col-span-12 space-y-1 lg:col-span-8">
                    <label for="buscar-historial" class="block text-label-md text-uch-muted">Búsqueda rápida</label>
                    <div class="relative flex items-center">
                        <span class="pointer-events-none absolute left-3.5 text-[20px] text-uch-muted material-symbols-outlined">search</span>
                        <input id="buscar-historial" name="buscar" type="search" value="{{ $buscar }}"
                               placeholder="Buscar por # ticket, nombre o correo del cliente..."
                               onchange="this.form.submit()"
                               class="w-full rounded-lg bg-uch-bg py-2.5 pl-10 pr-4 text-body-md text-uch-text placeholder:text-uch-muted focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                    </div>
                </div>

                <div class="col-span-12 space-y-1 sm:col-span-6 lg:col-span-4">
                    <label for="fecha-historial" class="block text-label-md text-uch-muted">Fecha del historial</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[20px] text-uch-blue material-symbols-outlined">calendar_today</span>
                        <input id="fecha-historial" name="fecha" type="date" value="{{ $fechaHistorial }}"
                               onchange="this.form.submit()"
                               class="w-full rounded-lg bg-uch-bg py-2.5 pl-10 pr-4 text-body-md font-semibold text-uch-navy focus:bg-uch-surface focus:ring-2 focus:ring-uch-blue/30">
                    </div>
                </div>
            </form>
        </section>

        <!-- TABLA DE HISTORIAL -->
        <section class="overflow-hidden rounded-card bg-uch-surface shadow-uch-card">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-body-md">
                    <thead>
                        <tr class="bg-uch-bg text-label-lg text-uch-navy">
                            <th class="px-4 py-3 font-bold">Ticket</th>
                            <th class="px-4 py-3 font-bold">Cliente</th>
                            <th class="px-4 py-3 font-bold">Resumen del pedido</th>
                            <th class="px-4 py-3 font-bold">Método de pago</th>
                            <th class="px-4 py-3 font-bold">Total</th>
                            <th class="px-4 py-3 font-bold">Estado</th>
                            <th class="px-4 py-3 font-bold">Hora finalización</th>
                            <th class="px-4 py-3 text-right font-bold">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-uch-border">
                        @forelse ($historial as $pedido)
                            @php $estilo = $estilosEstado[$pedido->estado] ?? $estilosEstado['entregado']; @endphp
                            <tr class="transition-colors hover:bg-uch-bg">
                                <td class="px-4 py-3.5 font-rubik text-label-lg font-bold text-uch-navy">
                                    #{{ str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex flex-col">
                                        <span class="text-label-lg font-semibold text-uch-text">{{ $pedido->cliente->name ?? 'Mostrador' }}</span>
                                        <span class="text-body-sm text-uch-muted">
                                            {{ $pedido->cliente?->rol === 'cliente' ? 'Estudiante' : ($pedido->cliente?->rol ?? 'Mostrador') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="max-w-[280px] px-4 py-3.5 text-body-sm text-uch-text">
                                    <span class="block truncate" title="{{ $pedido->resumen }}">{{ $pedido->resumen }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-uch-bg px-2.5 py-1 text-label-md text-uch-muted">
                                        <span class="material-symbols-outlined text-[14px] text-uch-blue">payments</span>
                                        {{ $pedido->metodo_pago ?: 'Efectivo' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 font-rubik text-body-md font-bold text-uch-navy">
                                    S/ {{ number_format((float) $pedido->total, 2) }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-label-md font-bold {{ $estilo['etiqueta'] }}">
                                        <span class="h-2 w-2 rounded-full {{ $estilo['punto'] }}"></span>
                                        {{ $estilo['texto'] }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-body-sm text-uch-muted">
                                    {{ $pedido->updated_at->format('h:i a') }}
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <a href="/admin/pedidos/{{ $pedido->id }}"
                                       class="inline-flex items-center gap-1 rounded-md px-3 py-1 text-label-md font-bold text-uch-blue transition-colors hover:bg-uch-bg">
                                        Ver detalle
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-body-sm text-uch-muted">
                                    No hay pedidos finalizados para la fecha seleccionada.
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
