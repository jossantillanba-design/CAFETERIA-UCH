<x-guest-layout>
    <div class="guest-form-header">
        <h2>Iniciar sesión</h2>
        <p>Accede con tu correo institucional UCH</p>
    </div>

    <div class="guest-card">
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Google -->
        <a href="{{ route('google.redirect') }}" class="guest-google-btn">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" style="width:18px;height:18px;">
            Continuar con Google
        </a>

        <div class="guest-divider">o con correo</div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div>
                <x-input-label for="email" :value="__('Correo institucional')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email"
                    name="email" :value="old('email')"
                    placeholder="nombre@uch.pe"
                    required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Contraseña')" />
                <x-text-input id="password" class="block mt-1 w-full"
                    type="password" name="password"
                    required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember + Forgot -->
            <div class="flex items-center justify-between mt-4">
                <label class="guest-remember">
                    <input id="remember_me" type="checkbox" name="remember">
                    Recordarme
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="guest-forgot">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <button type="submit" class="guest-submit">
                Iniciar sesión
            </button>
        </form>
    </div>

    <p class="guest-footer-link">
        ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a>
    </p>
</x-guest-layout>
