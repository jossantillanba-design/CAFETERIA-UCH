@auth
    @if (auth()->user()->rol === 'empleado')
        <header class="no-print fixed left-[76px] right-0 top-0 z-40 flex h-16 items-center justify-between gap-4 bg-uch-surface px-4 sm:px-6 lg:left-[248px]" style="border-bottom: 1px solid var(--hasp-border, #EBEBEB);">
            <form method="GET" action="{{ route('empleado.pedidos.index') }}" class="w-full max-w-md">
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 text-[20px] text-uch-muted">search</span>
                    <input type="search" name="buscar" autocomplete="off" value="{{ request('buscar') }}"
                           placeholder="Buscar ticket, nombre o código de operación..."
                           aria-label="Buscar pedidos del turno"
                           class="w-full rounded-full border-0 bg-uch-bg py-2 pl-10 pr-4 text-body-sm text-uch-text placeholder:text-uch-muted focus:ring-2" style="background: var(--hasp-bg, #F9F9F7); --tw-ring-color: var(--hasp-brand, #C8511B);">
                </div>
            </form>

            <div class="flex shrink-0 items-center gap-space-md">
                <span class="hidden text-label-md text-uch-muted sm:inline">
                    {{ ucfirst(today()->locale('es')->isoFormat('dddd D [de] MMMM')) }}
                </span>
                <div class="flex h-8 w-8 items-center justify-center rounded-full" style="background: var(--hasp-brand-lt, #FDF0E8);">
                    <span class="material-symbols-outlined text-[18px]" style="color: var(--hasp-brand, #C8511B);">person</span>
                </div>
            </div>
        </header>
    @endif
@endauth
