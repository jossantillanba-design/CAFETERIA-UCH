<x-layouts.app>
    <x-admin-nav />

    @php
        $estilosEstado = [
            'pendiente' => ['etiqueta' => 'bg-uch-amber/15 text-[#B45309]', 'punto' => 'bg-uch-amber', 'texto' => 'Pendiente'],
            'en_preparacion' => ['etiqueta' => 'bg-uch-cyan/15 text-[#0060A9]', 'punto' => 'bg-uch-cyan', 'texto' => 'En preparación'],
            'listo' => ['etiqueta' => 'bg-uch-teal/15 text-[#008779]', 'punto' => 'bg-uch-teal', 'texto' => 'Listo en barra'],
            'entregado' => ['etiqueta' => 'bg-uch-teal/15 text-[#008779]', 'punto' => 'bg-uch-teal', 'texto' => 'Entregado'],
            'cancelado' => ['etiqueta' => 'bg-uch-coral/10 text-uch-coral-dark', 'punto' => 'bg-uch-coral', 'texto' => 'Cancelado'],
        ];

        $estilo = $estilosEstado[$pedido->estado] ?? $estilosEstado['pendiente'];

        $atencion = $pedido->atendidoPor?->name
            ? 'Mostrador · '.$pedido->atendidoPor->name
            : 'Canal web (Autoservicio)';

        $tipoCliente = $pedido->cliente
            ? ($pedido->cliente->rol === 'cliente' ? 'Estudiante UCH' : 'Usuario interno')
            : 'Mostrador';
    @endphp

    <div class="mx-auto flex w-full max-w-[1400px] flex-col gap-space-lg px-4 py-6 sm:px-6 lg:px-8">
        <x-flash-message />

        <!-- RETORNO Y ENCABEZADO -->
        <section class="flex flex-col gap-space-sm">
            <div class="no-print">
                <a href="/admin/pedidos" class="group inline-flex items-center gap-1.5 text-label-lg text-uch-blue transition-colors hover:text-uch-navy">
                    <span class="material-symbols-outlined text-[18px] transition-transform group-hover:-translate-x-1">arrow_back</span>
                    Volver a pedidos en vivo
                </a>
            </div>

            <div class="flex flex-col gap-space-md lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-col gap-1">
                    <div class="flex flex-wrap items-center gap-space-sm">
                        <h1 class="font-rubik text-headline-lg-mobile tracking-tight text-uch-navy lg:text-headline-lg">
                            Ticket #{{ str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT) }}
                        </h1>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-label-md {{ $estilo['etiqueta'] }}">
                            <span class="h-2 w-2 rounded-full {{ $estilo['punto'] }} {{ $pedido->estado === 'pendiente' ? 'animate-pulse' : '' }}"></span>
                            {{ $estilo['texto'] }}
                        </span>
                        <span class="rounded-full bg-uch-blue/10 px-2.5 py-0.5 text-label-md font-semibold text-uch-blue">Módulo Central</span>
                    </div>
                    <p class="flex items-center gap-1.5 text-body-md text-uch-muted">
                        <span class="material-symbols-outlined text-[16px] text-uch-muted">schedule</span>
                        Registrado el {{ $pedido->created_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }} a las {{ $pedido->created_at->format('h:i a') }} • Campus Los Olivos
                    </p>
                </div>

                <div class="no-print flex items-center gap-3 self-start lg:self-center">
                    <button type="button" onclick="window.print()"
                            class="inline-flex items-center gap-2 rounded-lg bg-uch-surface px-4 py-2.5 text-label-lg font-bold text-uch-navy shadow-uch-card transition-all hover:bg-uch-bg active:scale-95">
                        <span class="material-symbols-outlined text-[20px] text-uch-blue">print</span>
                        Imprimir ticket
                    </button>
                    <a href="/admin/pedidos/crear"
                       class="inline-flex items-center gap-2 rounded-lg bg-uch-coral px-5 py-2.5 text-label-lg font-bold text-white shadow-md transition-all hover:bg-uch-coral-dark hover:shadow-lg active:scale-95">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        Nuevo ticket
                    </a>
                </div>
            </div>
        </section>

        <!-- LAYOUT ASIMÉTRICO -->
        <div class="grid grid-cols-1 items-start gap-space-lg lg:grid-cols-12">
            <!-- COMPROBANTE FÍSICO SIMULADO -->
            <div class="flex flex-col lg:col-span-8">
                <article class="print-card overflow-hidden rounded-card bg-uch-surface shadow-[0_10px_30px_rgba(27,54,104,0.12)]">
                    <div class="h-3 w-full bg-gradient-to-r from-uch-navy via-uch-blue to-uch-cyan"></div>
                    <div class="h-1.5 w-full bg-uch-cyan"></div>

                    <div class="flex flex-col gap-6 p-6 md:p-8">
                        <!-- CABECERA DEL RECIBO -->
                        <header class="-mx-6 -mt-6 flex flex-col gap-4 border-b border-uch-border bg-uch-bg/60 p-6 sm:flex-row sm:items-center sm:justify-between md:-mx-8 md:-mt-8 md:p-8">
                            <div class="flex items-start gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-uch-navy font-rubik text-headline-md text-white shadow-md">
                                    UCH
                                </div>
                                <div class="flex flex-col">
                                    <p class="font-rubik text-headline-sm leading-tight tracking-tight text-uch-navy">COMEDOR UNIVERSITARIO</p>
                                    <p class="text-label-md font-bold uppercase tracking-wider text-uch-blue">Campus Los Olivos • Pabellón A</p>
                                    <p class="text-body-sm text-uch-muted">RUC: 20512836471 • Boleta Electrónica</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-start rounded-xl bg-uch-surface px-4 py-2.5 shadow-uch-card sm:items-end">
                                <span class="text-label-md font-semibold uppercase tracking-wider text-uch-muted">Comprobante Interno</span>
                                <span class="font-rubik text-headline-sm text-uch-navy">#{{ str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT) }}-{{ $pedido->created_at->year }}</span>
                                <span class="text-body-sm text-uch-muted">{{ $pedido->created_at->format('d/m/Y • h:i:s a') }}</span>
                            </div>
                        </header>

                        <!-- DATOS RÁPIDOS -->
                        <div class="grid grid-cols-1 gap-3 rounded-xl bg-uch-bg p-3.5 sm:grid-cols-3">
                            <div class="flex flex-col">
                                <span class="text-label-md text-uch-muted">Atención</span>
                                <span class="text-label-lg font-semibold text-uch-navy">{{ $atencion }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-label-md text-uch-muted">Modalidad</span>
                                <span class="text-label-lg font-semibold text-uch-navy">Consumo Inmediato</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-label-md text-uch-muted">Turno Operativo</span>
                                <span class="text-label-lg font-semibold text-uch-navy">{{ $turno }}</span>
                            </div>
                        </div>

                        <!-- DETALLE DE CONSUMOS -->
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between border-b border-uch-border pb-1">
                                <span class="text-label-md font-bold uppercase tracking-wider text-uch-blue">Detalle de Consumos</span>
                                <span class="text-label-md text-uch-muted">{{ $pedido->detalles->sum('cantidad') }} unidades registradas</span>
                            </div>

                            @forelse ($pedido->detalles as $detalle)
                                <div class="flex items-center justify-between rounded-xl p-3.5 transition-colors hover:bg-uch-bg">
                                    <div class="flex items-center gap-3.5">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-uch-blue/10 text-uch-blue shadow-sm">
                                            <span class="material-symbols-outlined text-[24px]">{{ $detalle->tipo_item === 'menu' ? 'restaurant' : 'local_cafe' }}</span>
                                        </div>
                                        <div class="flex min-w-0 flex-col">
                                            <span class="text-title-dish text-uch-text">
                                                {{ $detalle->cantidad }}x {{ $detalle->tipo_item === 'menu' ? ($detalle->menu->titulo ?? 'Menú del día') : ($detalle->producto->nombre ?? 'Producto') }}
                                            </span>
                                            <span class="truncate text-body-sm text-uch-muted">
                                                S/ {{ number_format((float) $detalle->precio_unitario, 2) }} c/u •
                                                {{ $detalle->tipo_item === 'menu' ? ($detalle->detalle_menu ?: 'Menú del día') : ($detalle->producto->descripcion ?: 'Consumo UCH') }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-price-tag text-uch-navy">S/ {{ number_format((float) $detalle->subtotal, 2) }}</span>
                                </div>
                            @empty
                                <p class="py-8 text-center text-body-sm text-uch-muted">Este ticket no tiene consumos registrados.</p>
                            @endforelse
                        </div>

                        <!-- SEPARADOR ESTILO TICKET PERFORADO -->
                        <div class="-mx-6 flex items-center justify-between overflow-hidden py-4 md:-mx-8">
                            <div class="-ml-3 h-6 w-6 rounded-full bg-uch-bg shadow-inner"></div>
                            <div class="mx-3 flex-1 border-b-2 border-dashed border-uch-border"></div>
                            <div class="-mr-3 h-6 w-6 rounded-full bg-uch-bg shadow-inner"></div>
                        </div>

                        <!-- DESGLOSE ECONÓMICO -->
                        <div class="flex flex-col gap-2.5 px-1 text-body-md text-uch-muted">
                            <div class="flex items-center justify-between">
                                <span>Subtotal afecto</span>
                                <span class="font-semibold text-uch-text">S/ {{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>IGV (18%)</span>
                                <span class="font-semibold text-uch-text">S/ {{ number_format($igv, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Descuento aplicado (Beca / Promoción)</span>
                                <span class="font-semibold text-uch-blue">S/ 0.00</span>
                            </div>

                            <div class="mt-2 flex items-center justify-between rounded-xl bg-uch-bg p-4 shadow-uch-card">
                                <div class="flex flex-col">
                                    <span class="text-title-dish uppercase tracking-tight text-uch-navy">Total del pedido</span>
                                    <span class="text-body-sm text-uch-muted">
                                        {{ $pedido->pagado ? 'Importe cancelado en moneda nacional (PEN)' : 'Pendiente de cobro en moneda nacional (PEN)' }}
                                    </span>
                                </div>
                                <span class="font-rubik text-headline-lg text-uch-navy">S/ {{ number_format((float) $pedido->total, 2) }}</span>
                            </div>
                        </div>

                        <!-- PIE DE RECIBO -->
                        <div class="flex flex-col items-center justify-center gap-3 rounded-xl bg-uch-bg/60 p-5">
                            <div class="flex w-full max-w-sm flex-col items-center gap-1.5">
                                <div class="flex h-12 w-full items-center justify-center gap-0.5 rounded-lg bg-uch-surface px-4 py-2 shadow-uch-card">
                                    <div class="h-full w-1 bg-uch-navy"></div>
                                    <div class="h-full w-0.5 bg-uch-surface"></div>
                                    <div class="h-full w-1.5 bg-uch-navy"></div>
                                    <div class="h-full w-0.5 bg-uch-navy"></div>
                                    <div class="h-full w-2 bg-uch-surface"></div>
                                    <div class="h-full w-1 bg-uch-navy"></div>
                                    <div class="h-full w-0.5 bg-uch-navy"></div>
                                    <div class="h-full w-2.5 bg-uch-navy"></div>
                                    <div class="h-full w-1 bg-uch-surface"></div>
                                    <div class="h-full w-1 bg-uch-navy"></div>
                                    <div class="h-full w-2 bg-uch-navy"></div>
                                    <div class="h-full w-0.5 bg-uch-surface"></div>
                                    <div class="h-full w-1 bg-uch-navy"></div>
                                    <div class="h-full w-1.5 bg-uch-navy"></div>
                                    <div class="h-full w-0.5 bg-uch-navy"></div>
                                    <div class="h-full w-2 bg-uch-surface"></div>
                                    <div class="h-full w-1.5 bg-uch-navy"></div>
                                    <div class="h-full w-1 bg-uch-navy"></div>
                                    <div class="h-full w-0.5 bg-uch-navy"></div>
                                    <div class="h-full w-1.5 bg-uch-surface"></div>
                                    <div class="h-full w-2.5 bg-uch-navy"></div>
                                    <div class="h-full w-0.5 bg-uch-navy"></div>
                                    <div class="h-full w-1 bg-uch-navy"></div>
                                    <div class="h-full w-2 bg-uch-surface"></div>
                                    <div class="h-full w-1.5 bg-uch-navy"></div>
                                    <div class="h-full w-0.5 bg-uch-navy"></div>
                                    <div class="h-full w-2 bg-uch-navy"></div>
                                </div>
                                <span class="text-label-md font-bold uppercase tracking-widest text-uch-navy">OP: {{ $codigoOperacion }}</span>
                            </div>
                            <p class="max-w-md text-center text-body-sm text-uch-muted">
                                Válido para recojo en barra • Comedor Central Pabellón A • Conservar ticket digital para reclamos o cambios de menú.
                            </p>
                        </div>
                    </div>
                </article>
            </div>

            <!-- TARJETAS DE INFORMACIÓN OPERATIVA -->
            <aside class="flex flex-col gap-space-lg lg:col-span-4">
                <section class="relative overflow-hidden rounded-card bg-uch-surface p-6 shadow-uch-card">
                    <div class="absolute inset-x-0 top-0 h-1 bg-uch-blue"></div>
                    <div class="flex items-center gap-2.5 border-b border-uch-border pb-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-uch-blue/10 text-uch-blue">
                            <span class="material-symbols-outlined text-[20px]">assignment</span>
                        </div>
                        <h2 class="text-title-dish text-uch-navy">Datos del pedido</h2>
                    </div>

                    <div class="flex flex-col gap-4 pt-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-uch-bg text-uch-navy">
                                <span class="material-symbols-outlined text-[20px]">person</span>
                            </div>
                            <div class="flex min-w-0 flex-col">
                                <span class="text-label-md text-uch-muted">Cliente</span>
                                <span class="text-label-lg font-bold text-uch-navy">{{ $pedido->cliente->name ?? 'Cliente en mostrador' }}</span>
                                <span class="truncate text-body-sm text-uch-muted">
                                    {{ $pedido->cliente?->email ?? 'Sin correo registrado' }} • {{ $tipoCliente }}
                                </span>
                            </div>
                        </div>

                        <div class="h-px w-full bg-uch-border"></div>

                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-uch-bg text-uch-navy">
                                <span class="material-symbols-outlined text-[20px]">devices</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-label-md text-uch-muted">Atendido por</span>
                                @if ($pedido->atendidoPor)
                                    <span class="text-label-lg font-bold text-uch-navy">{{ $pedido->atendidoPor->name }}</span>
                                    <span class="text-body-sm text-uch-muted">Registro manual en mostrador</span>
                                @else
                                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-uch-blue/10 px-3 py-1 text-label-md text-uch-blue">
                                        <span class="material-symbols-outlined text-[16px]">language</span>
                                        <span class="font-semibold">Canal web (App Autoservicio)</span>
                                    </span>
                                    <span class="text-body-sm text-uch-muted">Terminal virtual #WEB-01</span>
                                @endif
                            </div>
                        </div>

                        <div class="h-px w-full bg-uch-border"></div>

                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-uch-bg text-uch-navy">
                                <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                            </div>
                            <div class="flex w-full flex-col gap-1">
                                <span class="text-label-md text-uch-muted">Estado de pago</span>
                                <div class="flex items-center justify-between gap-2">
                                    @if ($pedido->pagado)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-uch-teal/15 px-3 py-1 text-label-md text-[#008779]">
                                            <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                            <span class="font-semibold">Pagado</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-uch-amber/15 px-3 py-1 text-label-md text-[#B45309]">
                                            <span class="material-symbols-outlined text-[16px]">schedule</span>
                                            <span class="font-semibold">Pendiente de cobro</span>
                                        </span>
                                    @endif
                                    <span class="text-price-tag text-uch-navy">S/ {{ number_format((float) $pedido->total, 2) }}</span>
                                </div>
                                <span class="mt-1 text-body-sm text-uch-muted">{{ $pedido->metodo_pago ?: 'Efectivo en caja' }}</span>
                                @if ($pedido->codigo_transaccion)
                                    <span class="text-body-sm text-uch-muted">Operación: {{ $pedido->codigo_transaccion }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

                <section class="relative overflow-hidden rounded-card bg-uch-surface p-6 shadow-uch-card">
                    <div class="absolute inset-x-0 top-0 h-1 bg-uch-teal"></div>
                    <div class="flex items-center justify-between gap-3 border-b border-uch-border pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-uch-teal/15 text-[#008779]">
                                <span class="material-symbols-outlined text-[20px]">history_toggle_off</span>
                            </div>
                            <div class="flex flex-col">
                                <h2 class="text-title-dish text-uch-navy">Trazabilidad</h2>
                                <span class="text-body-sm text-uch-muted">Historial de cocina y barra</span>
                            </div>
                        </div>
                        <span class="rounded-full bg-uch-blue/10 px-2 py-0.5 text-label-md font-bold text-uch-blue">{{ $minutosDespacho }} min total</span>
                    </div>

                    <div class="relative flex flex-col gap-6 py-5 pl-6">
                        <div class="absolute bottom-3 left-[9px] top-3 w-0.5 {{ $pedido->estado === 'entregado' ? 'bg-uch-teal' : 'bg-uch-border' }}"></div>

                        @foreach ($lineaTiempo as $paso)
                            <div class="relative">
                                <div class="absolute -left-6 top-0.5 flex h-5 w-5 items-center justify-center rounded-full ring-4 ring-uch-surface {{ $paso['completada'] ? 'bg-uch-teal text-white' : 'border border-uch-border bg-uch-surface' }} {{ $paso['actual'] ? 'animate-pulse' : '' }}">
                                    <span class="material-symbols-outlined text-[14px]">{{ $paso['completada'] ? 'check' : 'schedule' }}</span>
                                </div>
                                <p class="text-label-lg font-bold {{ $paso['completada'] ? 'text-uch-navy' : 'text-uch-muted' }}">{{ $paso['titulo'] }}</p>
                                <p class="text-body-sm text-uch-muted">{{ $paso['detalle'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between rounded-xl bg-uch-bg p-3">
                        <span class="text-body-sm text-uch-muted">Tiempo de despacho:</span>
                        @if ($minutosDespacho <= 15)
                            <span class="text-label-lg font-bold text-[#008779]">Excelente ({{ $minutosDespacho }} min)</span>
                        @else
                            <span class="text-label-lg font-bold text-[#B45309]">{{ $minutosDespacho }} min</span>
                        @endif
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-layouts.app>
