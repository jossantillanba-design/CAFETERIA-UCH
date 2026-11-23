<x-layouts.app>
    <div x-data="checkout(@js(old('metodo', 'tarjeta')), @js(old('billetera', 'yape')))" x-init="init()" class="max-w-lg mx-auto">
        <h1 class="text-3xl font-bold mb-6">Pagar pedido</h1>

        @if (session('error'))
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        @error('productos')
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ $message }}</div>
        @enderror

        <div x-show="items.length === 0" x-cloak class="bg-uch-surface rounded-card shadow-uch-card p-8 mb-6 text-center">
            <span class="material-symbols-outlined text-uch-muted text-[40px]">shopping_bag</span>
            <p class="font-rubik text-lg font-bold text-uch-navy mt-space-sm">Tu pedido está vacío</p>
            <p class="text-body-md text-uch-muted mt-space-xs">Agrega productos desde el menú para continuar.</p>
            <a href="/menu" class="inline-flex items-center gap-2 mt-space-md bg-uch-coral text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-uch-coral-dark transition-colors">
                Volver al menú
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        </div>

        <div x-show="items.length > 0" x-cloak class="bg-uch-surface rounded-card shadow-uch-card p-6 mb-6">
            <h2 class="font-bold text-lg mb-3">Resumen</h2>
            <template x-for="item in items" :key="item.id">
                <div class="flex justify-between gap-3 text-sm mb-2">
                    <div class="min-w-0">
                        <p class="font-semibold" x-text="item.cantidad + 'x ' + item.nombre"></p>
                        <p class="text-xs text-uch-muted" x-text="item.detalle || ''"></p>
                    </div>
                    <span class="shrink-0" x-text="'S/' + (item.precio * item.cantidad).toFixed(2)"></span>
                </div>
            </template>
            <div class="border-t mt-3 pt-3 flex justify-between font-bold">
                <span>Total</span>
                <span x-text="'S/' + total().toFixed(2)"></span>
            </div>
        </div>

        <details x-show="items.length > 0" x-cloak class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm">
            <summary class="font-semibold cursor-pointer">Datos de prueba</summary>
            <ul class="mt-3 space-y-1">
                <li><b>Tarjeta aprobada:</b> 4111 1111 1111 1111</li>
                <li><b>Tarjeta sin fondos:</b> 4000 0400 0000 0008</li>
                <li><b>Tarjeta con CVV incorrecto:</b> 5400 0200 0000 0003</li>
                <li><b>Billetera aprobada:</b> celular 900 000 001 y cualquier código de 6 dígitos</li>
                <li><b>Billetera sin saldo:</b> celular 900 000 002</li>
                <li><b>Código incorrecto:</b> 000000</li>
            </ul>
            <p class="mt-2 text-gray-500">En tarjeta usa un vencimiento futuro (por ejemplo 12/30) y un CVV de 3 dígitos.</p>
        </details>

        <form x-show="items.length > 0" x-cloak action="/checkout" method="POST" class="bg-white rounded-lg shadow p-6">
            @csrf
            <input type="hidden" name="metodo" :value="metodo">
            <template x-for="(item, index) in items" :key="item.id">
                <div>
                    <input type="hidden" :name="'productos[' + index + '][id]'" :value="item.id">
                    <input type="hidden" :name="'productos[' + index + '][tipo]'" :value="item.tipo || 'producto'">
                    <input type="hidden" :name="'productos[' + index + '][cantidad]'" :value="item.cantidad">
                    <input type="hidden" :name="'productos[' + index + '][detalle]'" :value="item.detalle || ''">
                    <template x-if="(item.tipo || 'producto') === 'menu'">
                        <div>
                            <input type="hidden" :name="'productos[' + index + '][menu_id]'" :value="item.menu_id">
                            <input type="hidden" :name="'productos[' + index + '][opciones][entrada]'" :value="item.opciones.entrada">
                            <input type="hidden" :name="'productos[' + index + '][opciones][fondo]'" :value="item.opciones.fondo">
                            <input type="hidden" :name="'productos[' + index + '][opciones][refresco]'" :value="item.opciones.refresco">
                        </div>
                    </template>
                </div>
            </template>

            <h2 class="font-bold text-lg mb-4">Método de pago</h2>

            <div class="grid grid-cols-3 gap-3 mb-6">
                <button type="button" @click="metodo = 'efectivo'"
                        :class="metodo === 'efectivo' ? 'border-blue-700 bg-blue-50' : 'border-gray-200'"
                        class="border-2 rounded-lg p-3 text-center">
                    <div class="text-2xl">💵</div>
                    <div class="text-sm font-semibold">Efectivo</div>
                </button>
                <button type="button" @click="metodo = 'tarjeta'"
                        :class="metodo === 'tarjeta' ? 'border-blue-700 bg-blue-50' : 'border-gray-200'"
                        class="border-2 rounded-lg p-3 text-center">
                    <div class="text-2xl">💳</div>
                    <div class="text-sm font-semibold">Tarjeta</div>
                </button>
                <button type="button" @click="metodo = 'billetera'"
                        :class="metodo === 'billetera' ? 'border-blue-700 bg-blue-50' : 'border-gray-200'"
                        class="border-2 rounded-lg p-3 text-center">
                    <div class="text-2xl">📱</div>
                    <div class="text-sm font-semibold">Billetera</div>
                </button>
            </div>
            @error('metodo') <p class="text-red-600 text-sm mb-3">{{ $message }}</p> @enderror

            {{-- EFECTIVO --}}
            <div x-show="metodo === 'efectivo'" x-cloak class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4 text-sm text-yellow-900">
                Tu pedido se registrará ahora y pagarás <b>S/<span x-text="total().toFixed(2)"></span></b> en efectivo en caja al recogerlo. El empleado confirmará tu pago.
            </div>

            {{-- TARJETA --}}
            <div x-show="metodo === 'tarjeta'" x-cloak>
                <label class="block mb-2 text-sm font-semibold">Número de tarjeta</label>
                <input type="text" name="numero_tarjeta" inputmode="numeric" autocomplete="off" maxlength="23"
                       placeholder="4111 1111 1111 1111" @input="formatearTarjeta($event)"
                       :required="metodo === 'tarjeta'" class="w-full border rounded p-2 mb-1">
                @error('numero_tarjeta') <p class="text-red-600 text-sm mb-2">{{ $message }}</p> @enderror

                <label class="block mt-3 mb-2 text-sm font-semibold">Nombre en la tarjeta</label>
                <input type="text" name="nombre_tarjeta" value="{{ old('nombre_tarjeta') }}" autocomplete="off"
                       placeholder="Como aparece en la tarjeta" :required="metodo === 'tarjeta'"
                       class="w-full border rounded p-2 mb-1">
                @error('nombre_tarjeta') <p class="text-red-600 text-sm mb-2">{{ $message }}</p> @enderror

                <div class="grid grid-cols-2 gap-4 mt-3 mb-4">
                    <div>
                        <label class="block mb-2 text-sm font-semibold">Vencimiento</label>
                        <input type="text" name="vencimiento" inputmode="numeric" autocomplete="off" maxlength="5"
                               placeholder="MM/AA" @input="formatearFecha($event)"
                               :required="metodo === 'tarjeta'" class="w-full border rounded p-2">
                        @error('vencimiento') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold">CVV</label>
                        <input type="password" name="cvv" inputmode="numeric" autocomplete="off" maxlength="4"
                               placeholder="123" :required="metodo === 'tarjeta'" class="w-full border rounded p-2">
                        @error('cvv') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- BILLETERA DIGITAL --}}
            <div x-show="metodo === 'billetera'" x-cloak>
                <label class="block mb-2 text-sm font-semibold">Billetera</label>
                <div class="flex gap-6 mb-4">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="billetera" value="yape" x-model="billetera"> Yape
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" name="billetera" value="plin" x-model="billetera"> Plin
                    </label>
                </div>
                @error('billetera') <p class="text-red-600 text-sm mb-2">{{ $message }}</p> @enderror

                <label class="block mb-2 text-sm font-semibold">Número de celular</label>
                <input type="text" name="celular" value="{{ old('celular') }}" inputmode="numeric" autocomplete="off" maxlength="11"
                       placeholder="900 000 001" @input="formatearCelular($event)"
                       :required="metodo === 'billetera'" class="w-full border rounded p-2 mb-1">
                @error('celular') <p class="text-red-600 text-sm mb-2">{{ $message }}</p> @enderror

                <label class="block mt-3 mb-2 text-sm font-semibold">Código de aprobación (6 dígitos)</label>
                <input type="password" name="codigo_billetera" inputmode="numeric" autocomplete="off" maxlength="6"
                       placeholder="123456" :required="metodo === 'billetera'" class="w-full border rounded p-2 mb-1">
                @error('codigo_billetera') <p class="text-red-600 text-sm mb-2">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-500 mb-4">En la app real, este código aparece en tu aplicación de Yape o Plin. Aquí sirve cualquiera de 6 dígitos.</p>
            </div>

            <p class="text-xs text-gray-400 mb-4">Pago simulado con fines académicos. No se procesa ninguna transacción real ni se guardan los datos de la tarjeta.</p>

            <button type="submit" class="w-full bg-green-700 text-white py-3 rounded font-bold">
                <span x-show="metodo === 'efectivo'">Confirmar pedido (pagar en caja)</span>
                <span x-show="metodo !== 'efectivo'">Pagar S/<span x-text="total().toFixed(2)"></span></span>
            </button>
        </form>
    </div>

    <script>
        function checkout(metodoInicial, billeteraInicial) {
            return {
                items: [],
                metodo: metodoInicial,
                billetera: billeteraInicial,
                init() {
                    this.items = this.leerCarrito();

                    if (this.items.length > 0) {
                        localStorage.setItem('carrito_checkout', JSON.stringify(this.items));
                    }
                },

                leerCarrito() {
                    const parse = (clave) => {
                        try {
                            const valor = JSON.parse(localStorage.getItem(clave) || '[]');
                            return Array.isArray(valor) ? valor : [];
                        } catch (error) {
                            return [];
                        }
                    };

                    const delCheckout = parse('carrito_checkout');

                    return delCheckout.length > 0 ? delCheckout : parse('carrito');
                },
                total() {
                    return this.items.reduce((sum, i) => sum + (i.precio * i.cantidad), 0);
                },
                formatearTarjeta(e) {
                    const v = e.target.value.replace(/\D/g, '').slice(0, 19);
                    e.target.value = v.replace(/(.{4})(?=.)/g, '$1 ');
                },
                formatearFecha(e) {
                    let v = e.target.value.replace(/\D/g, '').slice(0, 4);
                    if (v.length > 2) {
                        v = v.slice(0, 2) + '/' + v.slice(2);
                    }
                    e.target.value = v;
                },
                formatearCelular(e) {
                    const v = e.target.value.replace(/\D/g, '').slice(0, 9);
                    e.target.value = v.replace(/(\d{3})(?=\d)/g, '$1 ');
                }
            }
        }
    </script>
</x-layouts.app>