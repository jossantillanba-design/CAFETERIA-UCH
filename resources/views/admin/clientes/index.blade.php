<x-layouts.app>
    <x-admin-nav />

    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <x-flash-message />

        <div class="flex items-center gap-2 text-label-md uppercase tracking-wider text-uch-muted">
            <span>Control y operación</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="font-bold text-uch-blue">Clientes</span>
        </div>

        <header class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <span class="text-label-md uppercase tracking-widest text-uch-blue">Comunidad UCH</span>
                <h1 class="mt-1 text-headline-lg-mobile lg:text-headline-lg font-rubik text-uch-navy tracking-tight">Clientes</h1>
                <p class="mt-1 text-body-md text-uch-muted">Estudiantes y personal registrados en el comedor universitario.</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-xl border border-uch-border bg-white px-4 py-2.5 text-body-sm text-uch-navy shadow-sm">
                <span class="material-symbols-outlined text-uch-blue text-[19px]">group</span>
                <strong>{{ $clientes->total() }}</strong> registros
            </div>
        </header>

        <section class="overflow-hidden rounded-card border border-uch-border bg-white shadow-uch-card">
            <div class="overflow-x-auto">
                <table class="w-full text-body-sm">
                    <thead>
                        <tr class="bg-uch-bg text-left text-uch-muted">
                            <th class="px-6 py-3 font-semibold">Cliente</th>
                            <th class="px-6 py-3 font-semibold">Correo</th>
                            <th class="px-6 py-3 font-semibold">Rol</th>
                            <th class="px-6 py-3 font-semibold">Registro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-uch-border">
                        @forelse ($clientes as $cliente)
                            <tr class="hover:bg-uch-bg transition-colors">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-uch-cyan/15 text-body-sm font-bold text-uch-blue">
                                            {{ mb_strtoupper(mb_substr($cliente->name, 0, 1)) }}
                                        </span>
                                        <span class="font-semibold text-uch-navy">{{ $cliente->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-uch-muted">{{ $cliente->email }}</td>
                                <td class="px-6 py-3">
                                    <span class="rounded-full bg-uch-blue/10 px-2.5 py-1 text-label-md font-semibold capitalize text-uch-blue">{{ $cliente->rol }}</span>
                                </td>
                                <td class="px-6 py-3 text-uch-muted">{{ $cliente->created_at?->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-body-sm text-uch-muted">Aún no hay clientes registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($clientes->hasPages())
                <div class="border-t border-uch-border bg-uch-surface px-6 py-4">
                    {{ $clientes->links() }}
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
