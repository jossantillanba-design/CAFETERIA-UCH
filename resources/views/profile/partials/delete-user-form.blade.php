<section class="flex flex-col gap-0">
    <div class="flex flex-col gap-0.5">
        <h3 class="font-rubik text-headline-sm font-bold text-uch-navy">Eliminar cuenta</h3>
        <p class="mt-1 text-body-sm leading-relaxed text-uch-muted">Esta acción eliminará permanentemente tu historial y datos.</p>
    </div>

    <button type="button" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-xl border border-uch-coral px-4 py-2 text-label-md font-bold text-uch-coral transition-colors hover:bg-[#ffdad6]/40 active:scale-95">
        <span class="material-symbols-outlined text-[16px]">delete_forever</span>
        Eliminar mi cuenta
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-[22px] text-uch-coral">warning</span>
                <div>
                    <h2 class="font-rubik text-headline-sm font-bold text-uch-navy">¿Eliminar tu cuenta?</h2>
                    <p class="mt-1 text-body-sm leading-relaxed text-uch-muted">Esta acción es permanente. Ingresa tu contraseña para confirmar la eliminación de tus datos y tu historial.</p>
                </div>
            </div>

            <div class="mt-6">
                <label for="password" class="sr-only">Contraseña</label>
                <input id="password" name="password" type="password" placeholder="Contraseña" autocomplete="current-password" class="w-full rounded-lg border border-uch-border bg-uch-surface px-3.5 py-2.5 text-body-md text-uch-navy placeholder:text-uch-muted focus:border-uch-coral focus:outline-none focus:ring-2 focus:ring-uch-coral/20">
                @error('password', 'userDeletion')
                    <p class="mt-1 text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="rounded-[10px] border border-uch-border bg-uch-surface px-5 py-2 text-label-md font-bold text-uch-muted transition-colors hover:bg-uch-bg">
                    Cancelar
                </button>
                <button type="submit" class="rounded-[10px] bg-uch-coral px-5 py-2 text-label-md font-bold text-white shadow-uch-card transition-colors hover:bg-uch-coral-dark active:scale-95">
                    Eliminar mi cuenta
                </button>
            </div>
        </form>
    </x-modal>
</section>