@auth
    @if (in_array(auth()->user()->rol, ['administrador'], true))
        <header class="no-print fixed left-[76px] right-0 top-0 z-40 flex h-16 items-center justify-between gap-4 border-b border-uch-border bg-uch-surface px-4 shadow-[0_1px_8px_rgba(0,0,0,0.04)] sm:px-6 lg:left-[248px]">
            <form method="GET" action="/admin/buscar" class="w-full max-w-md">
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 text-uch-muted text-[20px]">search</span>
                    <input type="search" name="q" autocomplete="off" value="{{ request('buscar') }}"
                           placeholder="Buscar pedidos, alumnos, productos o SKU..."
                           aria-label="Buscar en el panel administrativo"
                           class="w-full rounded-full border-0 bg-uch-bg py-2 pl-10 pr-4 text-body-sm text-uch-text placeholder:text-uch-muted focus:ring-2 focus:ring-uch-blue">
                </div>
            </form>

            <div class="flex items-center gap-space-md">
                <div x-data="{ abiertas: [], panel: false }" class="relative">
                    <button type="button" @click="panel = !panel" aria-label="Ver alertas de stock"
                            class="relative flex h-9 w-9 items-center justify-center rounded-full text-uch-muted transition-colors hover:bg-uch-bg hover:text-uch-navy">
                        <span class="material-symbols-outlined text-[22px]">notifications</span>
                        @if ($totalAlertasTopbar > 0)
                            <span class="absolute right-1.5 top-1.5 h-2.5 w-2.5 rounded-full bg-uch-coral ring-2 ring-uch-surface"></span>
                        @endif
                    </button>

                    <div x-show="panel" @click.outside="panel = false" x-cloak
                         class="absolute right-0 z-50 mt-3 w-[340px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-card border border-uch-border bg-uch-surface shadow-uch-card-hover">
                        <div class="h-1.5 w-full bg-uch-cyan"></div>
                        <div class="flex items-center justify-between border-b border-uch-border bg-uch-bg px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-uch-blue text-[18px]">warning</span>
                                <span class="text-label-lg font-bold text-uch-navy">Alertas de stock</span>
                            </div>
                            <span class="rounded-full bg-uch-coral/10 px-2 py-0.5 text-label-md font-bold text-uch-coral-dark">
                                {{ $criticasTopbar }} críticas
                            </span>
                        </div>

                        <div class="divide-y divide-uch-border">
                            @forelse ($alertasTopbar as $alerta)
                                <div x-show="! abiertas.includes('{{ $alerta['clave'] }}')" class="px-4 py-3 transition-colors hover:bg-uch-bg">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex min-w-0 items-start gap-2">
                                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $alerta['critica'] ? 'bg-uch-coral' : 'bg-uch-cyan' }}"></span>
                                            <div class="min-w-0">
                                                <p class="truncate text-label-lg font-semibold text-uch-navy">{{ $alerta['nombre'] }}</p>
                                                <p class="truncate text-body-sm text-uch-muted">{{ $alerta['referencia'] }} · {{ $alerta['categoria'] }}</p>
                                            </div>
                                        </div>
                                        <button type="button" @click="abiertas.push('{{ $alerta['clave'] }}')"
                                                class="shrink-0 text-label-md font-semibold text-uch-blue hover:underline">
                                            Marcar leída
                                        </button>
                                    </div>
                                    <p class="mt-1 pl-4 text-body-sm {{ $alerta['critica'] ? 'font-bold text-uch-coral' : 'text-uch-muted' }}">
                                        Stock {{ $alerta['stock'] }} de {{ $alerta['minimo'] }} {{ $alerta['unidad'] }}
                                    </p>
                                </div>
                            @empty
                                <p class="px-4 py-6 text-center text-body-sm text-uch-muted">Sin alertas activas.</p>
                            @endforelse
                        </div>

                        <div class="bg-uch-bg px-4 py-3 text-center">
                            <a href="/admin/inventario" class="text-label-md font-bold text-uch-blue hover:underline">
                                Ver control de inventario completo
                            </a>
                        </div>
                    </div>
                </div>

                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-uch-navy">
                    <span class="material-symbols-outlined text-[18px] text-white">person</span>
                </div>
            </div>
        </header>
    @endif
@endauth
