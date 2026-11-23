@php
    $inputClase = 'w-full rounded-lg border border-uch-border bg-uch-surface px-3.5 py-2.5 pr-10 text-body-md font-medium text-uch-navy placeholder:text-uch-muted focus:border-uch-blue focus:outline-none focus:ring-2 focus:ring-uch-cyan/20 transition-colors';
    $inputBloqueadoClase = 'w-full cursor-not-allowed select-none rounded-lg border border-uch-border bg-uch-bg px-3.5 py-2.5 pr-10 text-body-md font-semibold text-uch-navy';
@endphp

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<section>
    <div class="flex flex-col gap-0.5 border-b border-uch-border pb-4">
        <h2 class="font-rubik text-headline-sm font-bold text-uch-navy">Información personal</h2>
        <p class="text-body-sm text-uch-muted">Actualiza tus datos de contacto para identificar tus pedidos en barra.</p>
    </div>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 flex flex-col gap-4">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="flex flex-col gap-1.5">
                <label for="name" class="text-label-md font-bold text-uch-text">Nombre completo</label>
                <div class="relative flex items-center">
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required autofocus class="{{ $inputClase }}">
                    <span class="material-symbols-outlined pointer-events-none absolute right-3 text-[18px] text-uch-muted">badge</span>
                </div>
                @error('name')
                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="email" class="text-label-md font-bold text-uch-text">Correo institucional</label>
                <div class="relative flex items-center">
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="username" required class="{{ $inputClase }}">
                    <span class="material-symbols-outlined pointer-events-none absolute right-3 text-[18px] text-uch-muted">mail</span>
                </div>
                @error('email')
                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="telefono" class="text-label-md font-bold text-uch-text">Celular / WhatsApp (opcional)</label>
                <div class="relative flex items-center">
                    <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $user->telefono) }}" placeholder="987 654 321" autocomplete="tel" class="{{ $inputClase }}">
                    <span class="material-symbols-outlined pointer-events-none absolute right-3 text-[18px] text-uch-muted">phone_iphone</span>
                </div>
                @error('telefono')
                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="codigo_estudiante" class="text-label-md font-bold text-uch-text">Código de estudiante</label>
                <div class="relative flex items-center">
                    <input id="codigo_estudiante" name="codigo_estudiante" type="text" value="{{ old('codigo_estudiante', $user->codigo_estudiante) }}" placeholder="Sin registrar" readonly class="{{ $inputBloqueadoClase }}">
                    <span class="material-symbols-outlined pointer-events-none absolute right-3 text-[18px] text-uch-blue">verified</span>
                </div>
                @error('codigo_estudiante')
                    <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="rounded-lg border border-uch-amber/30 bg-uch-amber/10 p-3">
                <p class="text-body-sm text-uch-navy">
                    {{ __('Your email address is unverified.') }}
                    <button type="submit" form="send-verification" class="font-bold text-uch-blue underline">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-1 text-body-sm font-semibold text-uch-teal">{{ __('A new verification link has been sent to your email address.') }}</p>
                @endif
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-body-sm font-semibold text-uch-teal">Datos actualizados.</p>
            @endif
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-[10px] bg-uch-navy px-6 py-2 text-label-md font-bold text-white shadow-uch-card transition-colors hover:bg-uch-navy-dark active:scale-95">
                <span class="material-symbols-outlined text-[18px]">check</span>
                Guardar cambios
            </button>
        </div>
    </form>
</section>