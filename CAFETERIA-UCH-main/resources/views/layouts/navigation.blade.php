@auth
    @php
        $partesNombre = preg_split('/\s+/', trim(Auth::user()->name));
        $iniciales = mb_strtoupper(mb_substr($partesNombre[0] ?? '', 0, 1) . mb_substr($partesNombre[1] ?? '', 0, 1));
        $rolEtiqueta = match (Auth::user()->rol) {
            'cliente' => 'Estudiante',
            'administrador' => 'Administrador',
            'empleado' => 'Empleado',
            default => 'Usuario',
        };
    @endphp
@endauth

<nav x-data="navUCH()" x-init="init()" class="hasp-navbar">
    <a href="/" class="hasp-nav-brand" aria-label="Inicio GRUPO HASP SERVICE">
        <div class="hasp-brand-icon">☕</div>
        <div class="hasp-brand-text">
            <span class="hasp-brand-name">GRUPO HASP SERVICE</span>
            <span class="hasp-brand-sub">Cafetería UCH · Comedor Universitario</span>
        </div>
    </a>

    <ul class="hasp-nav-links">
        <li><a href="/menu#menus-del-dia" class="{{ request()->is('menu') ? 'hasp-active' : '' }}">Menú del día</a></li>
        <li><a href="/menu#productos">Productos</a></li>
        <li><a href="/mis-pedidos" class="{{ request()->is('mis-pedidos') ? 'hasp-active' : '' }}">Mis pedidos</a></li>
    </ul>

    <div class="hasp-nav-actions">
        @if (request()->is('menu'))
            <div class="hasp-search" x-data="{ q: '' }">
                <x-icon name="search" class="hasp-search-icon" />
                <input type="search"
                       x-init="q = window.busquedaMenu || new URLSearchParams(window.location.search).get('buscar') || ''"
                       :value="q"
                       @input="q = $event.target.value; window.busquedaMenu = q; $dispatch('buscar-menu', { value: q })"
                       placeholder="Buscar plato, café o combo..."
                       aria-label="Buscar en el menú">
            </div>
        @endif

        @auth
            @if (in_array(auth()->user()->rol, ['administrador', 'empleado'], true))
                <a href="{{ auth()->user()->rol === 'administrador' ? '/admin/dashboard' : '/empleado/tomar-orden' }}"
                   class="hidden md:inline text-sm font-semibold" style="color: var(--hasp-muted)">
                    {{ auth()->user()->rol === 'administrador' ? 'Panel admin' : 'Panel empleado' }}
                </a>
            @endif
        @endauth

        @if (request()->is('menu'))
            <button type="button" class="hasp-btn-cart" @click="$dispatch('abrir-carrito')" aria-label="Abrir mi carrito">
                🛒 <span class="hidden sm:inline">Mi carrito</span>
                <span class="hasp-cart-badge" x-text="totalItems">0</span>
            </button>
        @else
            <a href="/checkout" class="hasp-btn-cart" aria-label="Ir a mi pedido">
                🛒 <span class="hidden sm:inline">Mi carrito</span>
                <span class="hasp-cart-badge" x-text="totalItems">0</span>
            </a>
        @endif

        @auth
            <div class="hidden sm:block relative" x-data="{ abierto: false }">
                <button @click="abierto = !abierto" @click.outside="abierto = false" class="flex items-center gap-2" aria-label="Menú de usuario">
                    <span class="hasp-avatar">{{ $iniciales }}</span>
                </button>
                <div x-show="abierto" x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-lg py-2 z-50"
                     style="border: 1px solid var(--hasp-border)">
                    <div class="px-4 py-2 mb-1" style="border-bottom: 1px solid var(--hasp-border)">
                        <p class="text-sm font-bold truncate" style="color: var(--hasp-text)">{{ Auth::user()->name }}</p>
                        <p class="text-xs" style="color: var(--hasp-muted)">{{ $rolEtiqueta }}</p>
                    </div>
                    <a href="/mis-pedidos" class="block px-4 py-2 text-sm hover:bg-[#F9F9F7]" style="color: var(--hasp-text)">Mis pedidos</a>
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-[#F9F9F7]" style="color: var(--hasp-text)">Mi perfil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm font-semibold hover:bg-[#F9F9F7]" style="color: var(--hasp-brand)">Cerrar sesión</button>
                    </form>
                </div>
            </div>
        @else
            <a href="/login" class="hasp-btn-login">Iniciar sesión</a>
        @endauth

        <button @click="open = !open" class="sm:hidden w-9 h-9 flex items-center justify-center rounded-lg" style="color: var(--hasp-text)" aria-label="Abrir menú">
            <x-icon name="menu" class="w-5 h-5" x-show="!open" x-cloak />
            <x-icon name="close" class="w-5 h-5" x-show="open" x-cloak />
        </button>
    </div>

    <!-- Panel móvil -->
    <div x-show="open" x-cloak class="sm:hidden absolute top-full inset-x-0 bg-white px-4 pb-4 pt-2 shadow-lg" style="border-bottom: 1px solid var(--hasp-border)">
        <div class="flex flex-col gap-1">
            @if (request()->is('menu'))
                <div class="relative mb-2" x-data="{ q: '' }">
                    <input type="search"
                           x-init="q = window.busquedaMenu || new URLSearchParams(window.location.search).get('buscar') || ''"
                           :value="q"
                           @input="q = $event.target.value; window.busquedaMenu = q; $dispatch('buscar-menu', { value: q })"
                           placeholder="Buscar plato, café o combo..."
                           aria-label="Buscar en el menú"
                           class="w-full px-4 py-2 rounded-full text-sm" style="border: 1px solid var(--hasp-border); background: var(--hasp-bg)">
                </div>
            @endif
            <a href="/menu#menus-del-dia" @click="open = false" class="px-3 py-2 rounded-lg text-sm font-semibold" style="color: var(--hasp-text)">Menú del día</a>
            <a href="/menu#productos" @click="open = false" class="px-3 py-2 rounded-lg text-sm font-semibold" style="color: var(--hasp-text)">Productos</a>
            <a href="/mis-pedidos" @click="open = false" class="px-3 py-2 rounded-lg text-sm font-semibold" style="color: var(--hasp-text)">Mis pedidos</a>
            @auth
                @if (in_array(auth()->user()->rol, ['administrador', 'empleado'], true))
                    <a href="{{ auth()->user()->rol === 'administrador' ? '/admin/dashboard' : '/empleado/tomar-orden' }}" @click="open = false" class="px-3 py-2 rounded-lg text-sm font-semibold" style="color: var(--hasp-text)">{{ auth()->user()->rol === 'administrador' ? 'Panel admin' : 'Panel empleado' }}</a>
                @endif
                <a href="{{ route('profile.edit') }}" @click="open = false" class="px-3 py-2 rounded-lg text-sm font-semibold" style="color: var(--hasp-text)">Mi perfil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm font-semibold" style="color: var(--hasp-brand)">Cerrar sesión</button>
                </form>
            @else
                <a href="/login" @click="open = false" class="px-3 py-2 rounded-lg text-sm font-semibold" style="color: var(--hasp-brand)">Iniciar sesión</a>
            @endauth
        </div>
    </div>
</nav>

<script>
    function navUCH() {
        return {
            open: false,
            items: [],

            get totalItems() {
                return this.items.reduce((suma, item) => suma + (Number(item.cantidad) || 0), 0);
            },

            init() {
                this.items = this.leerCarrito();

                window.addEventListener('carrito-actualizado', (evento) => {
                    this.items = evento.detail || [];
                });
            },

            leerCarrito() {
                try {
                    return JSON.parse(localStorage.getItem('carrito') || '[]');
                } catch (error) {
                    return [];
                }
            },
        };
    }
</script>
