<x-guest-layout>
    <div class="guest-form-header">
        <h2>Crear cuenta</h2>
        <p>Regístrate con tu correo institucional UCH</p>
    </div>

    <div class="guest-card">
        <!-- Google -->
        <a href="{{ route('google.redirect') }}" class="guest-google-btn">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" style="width:18px;height:18px;">
            Registrarse con Google
        </a>

        <div class="guest-divider">o con correo</div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Nombre -->
            <div>
                <x-input-label for="name" :value="__('Nombre completo')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text"
                    name="name" :value="old('name')"
                    placeholder="Tu nombre"
                    required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="mt-4">
                <x-input-label for="email" :value="__('Correo institucional')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email"
                    name="email" :value="old('email')"
                    placeholder="nombre@uch.pe"
                    required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Contraseña')" />
                <x-text-input id="password" class="block mt-1 w-full"
                    type="password" name="password"
                    required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                    type="password" name="password_confirmation"
                    required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button type="submit" class="guest-submit">
                Crear cuenta
            </button>
        </form>
    </div>

    <p class="guest-footer-link">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
    </p>
</x-guest-layout>
