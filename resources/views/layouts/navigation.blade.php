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

<nav x-data="navUCH()" x-init="init()" class="fixed top-0 inset-x-0 z-50 bg-uch-navy shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
    <div class="h-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 flex items-center justify-between gap-4">
        <!-- Marca -->
        <div class="flex items-center gap-3 shrink-0">
            <a href="/" aria-label="Inicio Comedor UCH" class="shrink-0">
                <img src="{{ asset('images/uch-logo-blanco.svg') }}" alt="Universidad de Ciencias y Humanidades" class="h-9 w-auto object-contain">
            </a>
            <div class="hidden sm:flex flex-col">
                <span class="text-white font-bold text-sm tracking-wide leading-tight">Comedor Universitario</span>
                <span class="text-uch-cyan text-[11px] font-semibold leading-none">Campus Los Olivos</span>
            </div>
        </div>

        <!-- Buscador del catálogo -->
        @if (request()->is('menu'))
            <div class="hidden md:block" x-data="{ q: '' }">
                <div class="relative">
                    <x-icon name="search" class="w-[18px] h-[18px] absolute left-3 top-1/2 -translate-y-1/2 text-white/70 pointer-events-none" />
                    <input type="search"
                           x-init="q = window.busquedaMenu || new URLSearchParams(window.location.search).get('buscar') || ''"
                           :value="q"
                           @input="q = $event.target.value; window.busquedaMenu = q; $dispatch('buscar-menu', { value: q })"
                           placeholder="Buscar plato, café o combo..."
                           aria-label="Buscar en el menú"
                           class="w-52 lg:w-64 pl-9 pr-4 py-2 rounded-full bg-white/10 border border-white/20 text-sm text-white placeholder:text-white/60 focus:outline-none focus:ring-2 focus:ring-uch-cyan focus:bg-white focus:text-uch-text transition-all">
                </div>
            </div>
        @endif

        <!-- Acciones -->
        <div class="flex items-center gap-3 sm:gap-4 ml-auto">
            @auth
                @if (in_array(auth()->user()->rol, ['administrador', 'empleado'], true))
                    <a href="{{ auth()->user()->rol === 'administrador' ? '/admin/dashboard' : '/empleado/tomar-orden' }}"
                       class="hidden md:inline text-sm font-semibold text-white/85 hover:text-white transition-colors">
                        {{ auth()->user()->rol === 'administrador' ? 'Panel admin' : 'Panel empleado' }}
                    </a>
                @endif
            @endauth
            @auth
                <a href="/checkout"
                   aria-label="Ir a mi pedido"
                   class="relative flex items-center gap-2 bg-uch-coral text-white px-3.5 py-1.5 rounded-xl font-medium text-sm hover:brightness-110 active:scale-95 transition-all shadow-sm">
                    <x-icon name="shopping_bag" class="w-[19px] h-[19px]" />
                    <span class="hidden sm:inline font-semibold">Mi pedido</span>
                    <span x-text="items.length" class="bg-white text-uch-coral font-bold rounded-full w-5 h-5 flex items-center justify-center text-xs shadow-inner">0</span>
                </a>

                <div class="hidden sm:flex items-center gap-2 pl-2 border-l border-white/20 relative" x-data="{ abierto: false }">
                    <button @click="abierto = !abierto"
                            @click.outside="abierto = false"
                            class="flex items-center gap-2 rounded-full hover:bg-white/5 transition-colors">
                        <span class="w-9 h-9 rounded-full bg-uch-cyan text-white font-bold flex items-center justify-center text-xs shadow-md border-2 border-white/30 tracking-tight">
                            {{ $iniciales }}
                        </span>
                        <span class="hidden lg:flex flex-col text-left">
                            <span class="text-white text-xs font-semibold leading-tight">{{ Auth::user()->name }}</span>
                            <span class="text-white/70 text-[10px] leading-tight">{{ $rolEtiqueta }}</span>
                        </span>
                        <x-icon name="expand_more" class="w-[18px] h-[18px] text-white/70 hidden lg:block" />
                    </button>

                    <div x-show="abierto"
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 top-full mt-2 w-52 bg-uch-surface rounded-card shadow-uch-card-hover py-2 z-50">
                        <div class="px-4 py-2 border-b border-uch-border mb-1">
                            <p class="text-sm font-rubik font-bold text-uch-navy truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-uch-muted font-open">{{ $rolEtiqueta }}</p>
                        </div>
                        <a href="/mis-pedidos" class="block px-4 py-2 text-sm text-uch-text hover:bg-uch-bg">Mis pedidos</a>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-uch-text hover:bg-uch-bg">Mi perfil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-uch-coral hover:bg-uch-bg font-semibold">
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="/login" class="text-sm font-semibold text-white/85 hover:text-white transition-colors">
                    Iniciar sesión
                </a>
            @endauth

            <!-- Menú móvil -->
            <button @click="open = !open"
                    class="md:hidden w-9 h-9 flex items-center justify-center rounded-lg text-white hover:bg-white/10 transition-colors"
                    aria-label="Abrir menú">
                <x-icon name="menu" class="w-5 h-5" x-show="!open" x-cloak />
                <x-icon name="close" class="w-5 h-5" x-show="open" x-cloak />
            </button>
        </div>
    </div>

    <!-- Panel móvil -->
    <div x-show="open" x-cloak class="md:hidden border-t border-white/10 bg-uch-navy px-4 pb-4 pt-2">
        <div class="flex flex-col gap-1">
            @if (request()->is('menu'))
                <div class="relative mb-2" x-data="{ q: '' }">
                    <x-icon name="search" class="w-[18px] h-[18px] absolute left-3 top-1/2 -translate-y-1/2 text-white/70 pointer-events-none" />
                    <input type="search"
                           x-init="q = window.busquedaMenu || new URLSearchParams(window.location.search).get('buscar') || ''"
                           :value="q"
                           @input="q = $event.target.value; window.busquedaMenu = q; $dispatch('buscar-menu', { value: q })"
                           placeholder="Buscar plato, café o combo..."
                           aria-label="Buscar en el menú"
                           class="w-full pl-9 pr-4 py-2 rounded-full bg-white/10 border border-white/20 text-sm text-white placeholder:text-white/60 focus:outline-none focus:ring-2 focus:ring-uch-cyan focus:bg-white focus:text-uch-text transition-all">
                </div>
            @endif

            @auth
                <div class="flex items-center gap-3 py-2 mb-1 border-b border-white/10">
                    <span class="w-9 h-9 rounded-full bg-uch-cyan text-white font-bold flex items-center justify-center text-xs border-2 border-white/30">{{ $iniciales }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[11px] text-white/70">{{ $rolEtiqueta }}</p>
                    </div>
                </div>
            @endauth

            @auth
                <a href="/mis-pedidos" @click="open = false" class="px-3 py-2 rounded-lg text-sm font-semibold text-white/85 hover:bg-white/10">Mis pedidos</a>

                @if (in_array(auth()->user()->rol, ['administrador', 'empleado'], true))
                    <a href="{{ auth()->user()->rol === 'administrador' ? '/admin/dashboard' : '/empleado/tomar-orden' }}" @click="open = false" class="px-3 py-2 rounded-lg text-sm font-semibold text-white/85 hover:bg-white/10">{{ auth()->user()->rol === 'administrador' ? 'Panel admin' : 'Panel empleado' }}</a>
                @endif

                <a href="{{ route('profile.edit') }}" @click="open = false" class="px-3 py-2 rounded-lg text-sm font-semibold text-white/85 hover:bg-white/10">Mi perfil</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm font-semibold text-uch-coral hover:bg-white/10">
                        Cerrar sesión
                    </button>
                </form>
            @else
                <a href="/login" @click="open = false" class="px-3 py-2 rounded-lg text-sm font-semibold text-white/85 hover:bg-white/10">Iniciar sesión</a>
            @endauth
        </div>
    </div>
</nav>

<script>
    function navUCH() {
        return {
            open: false,
            items: [],

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
