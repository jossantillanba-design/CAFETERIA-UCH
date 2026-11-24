<x-layouts.app>
    <x-empleado-nav />

    @php
        $estilosEstado = [
            'pendiente' => ['etiqueta' => 'bg-uch-amber/15 text-[#B45309]', 'punto' => 'bg-uch-amber', 'texto' => 'Pendiente'],
            'en_preparacion' => ['etiqueta' => 'bg-uch-cyan/15 text-[#0060A9]', 'punto' => 'bg-uch-cyan', 'texto' => 'En preparación'],
            'listo' => ['etiqueta' => 'bg-uch-teal/15 text-[#008779]', 'punto' => 'bg-uch-teal', 'texto' => 'Listo en barra'],
            'entregado' => ['etiqueta' => 'bg-uch-teal/15 text-[#008779]', 'punto' => 'bg-uch-teal', 'texto' => 'Entregado'],
            'cancelado' => ['etiqueta' => 'bg-uch-coral/10 text-uch-coral-dark', 'punto' => 'bg-uch-coral', 'texto' => 'Anulado'],
        ];

        $estilo = $estilosEstado[$pedido->estado] ?? $estilosEstado['pendiente'];
        $esCaja = $pedido->origen === \App\Models\Pedido::ORIGEN_CAJA;
        $nombreCliente = $pedido->cliente_nombre_caja ?: ($pedido->cliente->name ?? 'Cliente en mostrador');
        $metodosPago = [
            'efectivo' => 'Efectivo',
            'yape_plin' => 'Yape/Plin',
            'tarjeta' => 'Tarjeta POS',
        ];
        $metodoPago = $metodosPago[$pedido->metodo_pago] ?? ($pedido->metodo_pago ?: 'Sin registro');
    @endphp

    <div class="mx-auto w-full max-w-[1100px] px-4 py-6 sm:px-6 lg:px-8">
        <section class="no-print mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('empleado.pedidos.index') }}"
                   class="group inline-flex items-center gap-1.5 text-label-lg text-uch-blue transition-colors hover:text-uch-navy">
                    <span class="material-symbols-outlined text-[18px] transition-transform group-hover:-translate-x-1">arrow_back</span>
                    Volver a pedidos en vivo
                </a>
                <h1 class="mt-2 font-rubik text-headline-lg-mobile tracking-tight text-uch-navy lg:text-headline-lg">
                    Ticket #{{ str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT) }}
                </h1>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('empleado.tomar-orden.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-label-lg font-bold text-white transition-all active:scale-95"
                   style="background: var(--hasp-brand, #C8511B);">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Nueva orden
                </a>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-label-md font-bold {{ $estilo['etiqueta'] }}">
                    <span class="h-2 w-2 rounded-full {{ $estilo['punto'] }}"></span>
                    {{ $estilo['texto'] }}
                </span>
                <button type="button" onclick="window.print()"
                        class="inline-flex items-center gap-2 rounded-lg bg-uch-surface px-4 py-2.5 text-label-lg font-bold text-uch-navy shadow-uch-card transition-all hover:bg-uch-bg active:scale-95">
                    <span class="material-symbols-outlined text-[20px] text-uch-blue">print</span>
                    Imprimir ticket
                </button>
            </div>
        </section>

        <article class="print-card overflow-hidden rounded-card bg-uch-surface shadow-[0_10px_30px_rgba(27,54,104,0.12)]">
            <div class="h-3 w-full bg-gradient-to-r from-uch-navy via-uch-blue to-uch-cyan"></div>
            <div class="h-1.5 w-full bg-uch-cyan"></div>

            <div class="flex flex-col gap-6 p-6 md:p-8">
                <header class="-mx-6 -mt-6 flex flex-col gap-4 border-b border-uch-border bg-uch-bg/60 p-6 sm:flex-row sm:items-center sm:justify-between md:-mx-8 md:-mt-8 md:p-8">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-uch-navy font-rubik text-headline-md text-white shadow-md">UCH</div>
                        <div class="flex flex-col">
                            <p class="font-rubik text-headline-sm leading-tight tracking-tight text-uch-navy">COMEDOR UNIVERSITARIO</p>
                            <p class="text-label-md font-bold uppercase tracking-wider text-uch-blue">Campus Los Olivos • Pabellón A</p>
                            <p class="text-body-sm text-uch-muted">RUC: 20512836471 • Boleta Electrónica</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-start rounded-xl bg-uch-surface px-4 py-2.5 shadow-uch-card sm:items-end">
                        <span class="text-label-md font-semibold uppercase tracking-wider text-uch-muted">Comprobante interno</span>
                        <span class="font-rubik text-headline-sm text-uch-navy">#{{ str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT) }}-{{ $pedido->created_at->year }}</span>
                        <span class="text-body-sm text-uch-muted">{{ $pedido->created_at->format('d/m/Y • h:i:s a') }}</span>
                    </div>
                </header>

                <section class="grid grid-cols-1 gap-3 rounded-xl bg-uch-bg p-3.5 sm:grid-cols-2">
                    <div class="flex flex-col">
                        <span class="text-label-md text-uch-muted">Cliente</span>
                        <span class="text-label-lg font-semibold text-uch-navy">{{ $nombreCliente }}</span>
                        <span class="text-body-sm text-uch-muted">{{ $pedido->cliente?->email ?? 'Cliente en mostrador' }}</span>
                    </div>
                    <div class="flex flex-col sm:items-end">
                        <span class="text-label-md text-uch-muted">Atención</span>
                        <span class="text-label-lg font-semibold text-uch-navy">
                            {{ $esCaja ? 'Mostrador · '.$pedido->atendidoPor?->name : 'Canal web (Autoservicio)' }}
                        </span>
                        <span class="text-body-sm text-uch-muted">
                            {{ $esCaja ? 'Venta en mostrador' : 'Pedido virtual' }}
                        </span>
                    </div>
                </section>

                <section class="flex flex-col gap-3">
                    <div class="flex items-center justify-between border-b border-uch-border pb-1">
                        <span class="text-label-md font-bold uppercase tracking-wider text-uch-blue">Detalle de consumos</span>
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
                </section>

                <div class="-mx-6 flex items-center justify-between overflow-hidden py-4 md:-mx-8">
                    <div class="-ml-3 h-6 w-6 rounded-full bg-uch-bg shadow-inner"></div>
                    <div class="mx-3 flex-1 border-b-2 border-dashed border-uch-border"></div>
                    <div class="-mr-3 h-6 w-6 rounded-full bg-uch-bg shadow-inner"></div>
                </div>

                <section class="flex flex-col gap-2.5 px-1 text-body-md text-uch-muted">
                    <div class="flex items-center justify-between">
                        <span>Subtotal afecto</span>
                        <span class="font-semibold text-uch-text">S/ {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>IGV (18%)</span>
                        <span class="font-semibold text-uch-text">S/ {{ number_format($igv, 2) }}</span>
                    </div>

                    @if ($pedido->monto_recibido !== null)
                        <div class="flex items-center justify-between">
                            <span>Monto recibido ({{ $metodoPago }})</span>
                            <span class="font-semibold text-uch-text">S/ {{ number_format((float) $pedido->monto_recibido, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Vuelto entregado</span>
                            <span class="font-semibold text-[#008779]">S/ {{ number_format((float) ($pedido->vuelto ?? 0), 2) }}</span>
                        </div>
                    @else
                        <div class="flex items-center justify-between">
                            <span>Método de pago</span>
                            <span class="font-semibold text-uch-text">{{ $metodoPago }}</span>
                        </div>
                    @endif

                    <div class="mt-2 flex items-center justify-between rounded-xl bg-uch-bg p-4 shadow-uch-card">
                        <div class="flex flex-col">
                            <span class="text-title-dish uppercase tracking-tight text-uch-navy">Total del pedido</span>
                            <span class="text-body-sm text-uch-muted">
                                {{ $pedido->pagado ? 'Importe cancelado en moneda nacional (PEN)' : 'Pendiente de cobro en moneda nacional (PEN)' }}
                            </span>
                        </div>
                        <span class="font-rubik text-headline-lg text-uch-navy">S/ {{ number_format((float) $pedido->total, 2) }}</span>
                    </div>
                </section>

                <footer class="flex flex-col items-center justify-center gap-3 rounded-xl bg-uch-bg/60 p-5">
                    <div class="flex w-full max-w-sm flex-col items-center gap-1.5">
                        <div class="flex h-12 w-full items-center justify-center gap-0.5 rounded-lg bg-uch-surface px-4 py-2 shadow-uch-card font-mono text-body-md tracking-widest text-uch-navy">
                            {{ $codigoOperacion }}
                        </div>
                        <span class="text-label-md font-bold uppercase tracking-widest text-uch-navy">OP: {{ $codigoOperacion }}</span>
                    </div>
                    <p class="max-w-md text-center text-body-sm text-uch-muted">
                        Válido para recojo en barra • Comedor Central Pabellón A • Conservar ticket digital para reclamos o cambios de menú.
                    </p>
                </footer>
            </div>
        </article>
    </div>

    @if (request()->boolean('imprimir'))
        @push('scripts')
            <script>
                {{-- Venta recién cobrada: abre la impresión y, al cerrarla, vuelve a una orden nueva --}}
                window.addEventListener('load', function () {
                    window.print();
                });
                window.addEventListener('afterprint', function () {
                    window.location.href = @js(route('empleado.tomar-orden.create'));
                });
            </script>
        @endpush
    @endif
</x-layouts.app>
