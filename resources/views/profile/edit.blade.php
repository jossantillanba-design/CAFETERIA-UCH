<x-layouts.app>
    <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-12">
        <header class="mb-6">
            <a href="/menu" class="inline-flex items-center gap-1.5 text-label-md font-bold text-uch-blue transition-colors hover:text-uch-navy hover:underline">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                Volver al menú
            </a>
            <div class="mt-2 flex flex-col">
                <p class="text-label-md font-bold uppercase tracking-widest text-uch-blue">Cuenta estudiantil</p>
                <h1 class="mt-1 font-rubik text-headline-lg-mobile font-bold tracking-tight text-uch-navy lg:text-headline-lg">Mi perfil</h1>
            </div>
        </header>

        <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-12">
            <div class="flex flex-col gap-4 md:col-span-4">
                <section class="overflow-hidden rounded-card border border-uch-border bg-uch-surface text-center shadow-uch-card">
                    <div class="h-[70px] w-full bg-gradient-to-r from-uch-navy to-uch-cyan"></div>
                    <div class="-mt-10 flex justify-center">
                        <div class="flex h-20 w-20 select-none items-center justify-center rounded-full border-4 border-white bg-uch-cyan font-rubik text-2xl font-bold text-white shadow-md">
                            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="flex flex-col items-center px-6 pb-6 pt-3">
                        <h2 class="font-rubik text-title-dish font-bold leading-snug text-uch-navy">{{ $user->name }}</h2>
                        <p class="mt-0.5 text-body-sm text-uch-muted">{{ $user->email }}</p>
                        <span class="mt-3 inline-flex items-center gap-1.5 rounded-full border border-uch-teal/20 bg-[#E6FFFA] px-3 py-1 text-body-sm font-semibold text-uch-teal shadow-sm">
                            <span class="h-1.5 w-1.5 rounded-full bg-uch-teal"></span>
                            Estudiante activo · Campus Los Olivos
                        </span>
                        <div class="my-4 w-full border-t border-uch-border"></div>
                        <div class="grid w-full grid-cols-2 gap-2 rounded-xl bg-uch-bg p-3 text-left">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-medium leading-tight text-uch-muted">Pedidos realizados</span>
                                <span class="mt-0.5 font-rubik text-base font-bold text-uch-navy">{{ $pedidosRealizados }} {{ $pedidosRealizados === 1 ? 'pedido' : 'pedidos' }}</span>
                            </div>
                            <div class="flex flex-col border-l border-uch-border pl-2">
                                <span class="text-[11px] font-medium leading-tight text-uch-muted">Recojo frecuente</span>
                                <span class="mt-0.5 truncate font-rubik text-xs font-bold text-uch-navy">Pabellón A</span>
                            </div>
                        </div>
                    </div>
                </section>

                @include('profile.partials.delete-user-form')
            </div>

            <div class="flex flex-col gap-4 md:col-span-8">
                <section class="rounded-card border border-uch-border bg-uch-surface p-6 shadow-uch-card">
                    @include('profile.partials.update-profile-information-form')
                </section>

                <section class="rounded-card border border-uch-border bg-uch-surface p-6 shadow-uch-card">
                    @include('profile.partials.update-password-form')
                </section>
            </div>
        </div>
    </div>
</x-layouts.app>