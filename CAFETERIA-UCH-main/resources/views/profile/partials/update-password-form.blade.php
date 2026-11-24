@php
    $inputClase = 'w-full rounded-lg border border-uch-border bg-uch-surface px-3.5 py-2.5 pr-10 text-body-md font-medium text-uch-navy placeholder:text-uch-muted focus:border-uch-blue focus:outline-none focus:ring-2 focus:ring-uch-cyan/20 transition-colors';
@endphp

<section>
    <div class="flex flex-col gap-0.5 border-b border-uch-border pb-4">
        <h2 class="font-rubik text-headline-sm font-bold text-uch-navy">Seguridad y contraseña</h2>
        <p class="text-body-sm text-uch-muted">Usa una contraseña segura de al menos 8 caracteres.</p>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 flex flex-col gap-4">
        @csrf
        @method('put')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="flex flex-col gap-1.5" x-data="{ mostrar: false }">
                <label for="update_password_current_password" class="text-label-md font-bold text-uch-text">Contraseña actual</label>
                <div class="relative flex items-center">
                    <input id="update_password_current_password" name="current_password" :type="mostrar ? 'text' : 'password'" autocomplete="current-password" class="{{ $inputClase }}">
                    <button type="button" @click="mostrar = !mostrar" aria-label="Mostrar contraseña" class="absolute right-2 flex items-center justify-center p-0.5 text-uch-muted transition-colors hover:text-uch-navy">
                        <span class="material-symbols-outlined text-[18px]" x-text="mostrar ? 'visibility_off' : 'visibility'"></span>
                    </button>
                </div>
                @error('current_password', 'updatePassword')
                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5" x-data="{ mostrar: false }">
                <label for="update_password_password" class="text-label-md font-bold text-uch-text">Nueva contraseña</label>
                <div class="relative flex items-center">
                    <input id="update_password_password" name="password" :type="mostrar ? 'text' : 'password'" placeholder="Mín. 8 caracteres" autocomplete="new-password" class="{{ $inputClase }}">
                    <button type="button" @click="mostrar = !mostrar" aria-label="Mostrar contraseña" class="absolute right-2 flex items-center justify-center p-0.5 text-uch-muted transition-colors hover:text-uch-navy">
                        <span class="material-symbols-outlined text-[18px]" x-text="mostrar ? 'visibility_off' : 'visibility'"></span>
                    </button>
                </div>
                @error('password', 'updatePassword')
                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5" x-data="{ mostrar: false }">
                <label for="update_password_password_confirmation" class="text-label-md font-bold text-uch-text">Confirmar contraseña</label>
                <div class="relative flex items-center">
                    <input id="update_password_password_confirmation" name="password_confirmation" :type="mostrar ? 'text' : 'password'" placeholder="Repite la contraseña" autocomplete="new-password" class="{{ $inputClase }}">
                    <button type="button" @click="mostrar = !mostrar" aria-label="Mostrar contraseña" class="absolute right-2 flex items-center justify-center p-0.5 text-uch-muted transition-colors hover:text-uch-navy">
                        <span class="material-symbols-outlined text-[18px]" x-text="mostrar ? 'visibility_off' : 'visibility'"></span>
                    </button>
                </div>
                @error('password_confirmation', 'updatePassword')
                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-body-sm font-semibold text-uch-teal">Contraseña actualizada.</p>
            @endif
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-[10px] bg-uch-blue px-6 py-2 text-label-md font-bold text-white shadow-uch-card transition-colors hover:bg-uch-navy active:scale-95">
                <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                Actualizar contraseña
            </button>
        </div>
    </form>
</section>