<x-layouts.app>
    <div class="hasp-page" style="min-height: calc(100vh - 4rem);">
    <div x-data="checkout(@js(old('metodo', 'tarjeta')), @js(old('billetera', 'yape')))" x-init="init()" x-cloak class="hasp-co">

        <header class="hasp-co-header">
            <span class="hasp-pill hasp-pill-brand">Comedor UCH · Pabellón C 1er Piso</span>
            <h1>Pagar pedido</h1>
            <p>Revisa tu pedido y elige cómo quieres pagar.</p>
        </header>

        @if (session('error'))
            <div class="hasp-co-alert hasp-co-alert-error"><span>⚠️</span><span>{{ session('error') }}</span></div>
        @endif

        @error('productos')
            <div class="hasp-co-alert hasp-co-alert-error"><span>⚠️</span><span>{{ $message }}</span></div>
        @enderror

        <div x-show="items.length === 0" x-cloak class="hasp-co-card hasp-co-empty">
            <div class="hasp-big-icon">🛒</div>
            <h2>Tu pedido está vacío</h2>
            <p>Agrega productos desde el menú para continuar.</p>
            <a href="/menu" class="hasp-btn-primary">Volver al menú</a>
        </div>

        <section x-show="items.length > 0" x-cloak class="hasp-co-card">
            <div class="hasp-co-card-head"><span>🧾</span><h2>Resumen del pedido</h2></div>
            <div class="hasp-co-card-body">
                <template x-for="item in items" :key="item.id">
                    <div class="hasp-co-line">
                        <template x-if="item.imagen">
                            <img class="hasp-cart-item-img" :src="item.imagen" :alt="item.nombre" onerror="this.style.display='none'">
                        </template>
                        <div class="hasp-co-line-info">
                            <p class="hasp-co-line-name" x-text="item.cantidad + '× ' + item.nombre"></p>
                            <p class="hasp-co-line-sub" x-text="item.detalle || ''"></p>
                        </div>
                        <span class="hasp-co-line-price" x-text="'S/ ' + (item.precio * item.cantidad).toFixed(2)"></span>
                    </div>
                </template>
                <div class="hasp-co-total">
                    <span>Total a pagar</span>
                    <b x-text="'S/ ' + total().toFixed(2)"></b>
                </div>
            </div>
        </section>

        <details x-show="items.length > 0" x-cloak class="hasp-co-test">
            <summary>ℹ️ Datos de prueba</summary>
            <div>
                <ul>
                    <li><b>Tarjeta aprobada:</b> 4111 1111 1111 1111</li>
                    <li><b>Tarjeta sin fondos:</b> 4000 0400 0000 0008</li>
                    <li><b>Tarjeta con CVV incorrecto:</b> 5400 0200 0000 0003</li>
                    <li><b>Billetera aprobada:</b> celular 900 000 001 y cualquier código de 6 dígitos</li>
                    <li><b>Billetera sin saldo:</b> celular 900 000 002</li>
                    <li><b>Código incorrecto:</b> 000000</li>
                </ul>
                <p>En tarjeta usa un vencimiento futuro (por ejemplo 12/30) y un CVV de 3 dígitos.</p>
            </div>
        </details>

        <form x-show="items.length > 0" x-cloak action="/checkout" method="POST" class="hasp-co-card">
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

            <div class="hasp-co-card-body hasp-co-form">
                <div>
                    <h2>Método de pago</h2>
                    <p class="hasp-co-hint">Selecciona con cuál medio deseas cancelar.</p>
                </div>

                <div class="hasp-co-methods">
                    <button type="button" @click="metodo = 'efectivo'" :class="{ 'hasp-selected': metodo === 'efectivo' }" class="hasp-co-method">
                        <span class="hasp-co-method-icon">💵</span>
                        <span>Efectivo</span>
                    </button>
                    <button type="button" @click="metodo = 'tarjeta'" :class="{ 'hasp-selected': metodo === 'tarjeta' }" class="hasp-co-method">
                        <span class="hasp-co-method-icon">💳</span>
                        <span>Tarjeta</span>
                    </button>
                    <button type="button" @click="metodo = 'billetera'" :class="{ 'hasp-selected': metodo === 'billetera' }" class="hasp-co-method">
                        <span class="hasp-co-method-icon">📱</span>
                        <span>Yape / Plin</span>
                    </button>
                </div>
                @error('metodo')
                    <p class="hasp-co-error">{{ $message }}</p>
                @enderror

                {{-- EFECTIVO --}}
                <div x-show="metodo === 'efectivo'" x-cloak class="hasp-co-alert hasp-co-alert-info">
                    <span>ℹ️</span>
                    <p>Tu pedido se registrará ahora y pagarás <b>S/ <span x-text="total().toFixed(2)"></span></b> en efectivo en caja al recogerlo. El empleado confirmará tu pago.</p>
                </div>

                {{-- TARJETA --}}
                <div x-show="metodo === 'tarjeta'" x-cloak class="hasp-co-group">
                    <div class="hasp-co-field">
                        <label for="numero_tarjeta">Número de tarjeta</label>
                        <input id="numero_tarjeta" type="text" name="numero_tarjeta" autocomplete="off" inputmode="numeric" maxlength="23" placeholder="4111 1111 1111 1111" @input="formatearTarjeta($event)" :required="metodo === 'tarjeta'">
                        @error('numero_tarjeta')
                            <p class="hasp-co-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="hasp-co-field">
                        <label for="nombre_tarjeta">Nombre en la tarjeta</label>
                        <input id="nombre_tarjeta" type="text" name="nombre_tarjeta" value="{{ old('nombre_tarjeta') }}" autocomplete="off" placeholder="Como aparece en la tarjeta" :required="metodo === 'tarjeta'">
                        @error('nombre_tarjeta')
                            <p class="hasp-co-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="hasp-co-grid2">
                    <div class="hasp-co-field">
                        <label for="vencimiento">Vencimiento</label>
                        <input id="vencimiento" type="text" name="vencimiento" autocomplete="off" inputmode="numeric" maxlength="5" placeholder="MM/AA" @input="formatearFecha($event)" :required="metodo === 'tarjeta'">
                        @error('vencimiento')
                            <p class="hasp-co-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="hasp-co-field">
                        <label for="cvv">CVV</label>
                        <input id="cvv" type="password" name="cvv" autocomplete="off" inputmode="numeric" maxlength="4" placeholder="123" :required="metodo === 'tarjeta'">
                        @error('cvv')
                            <p class="hasp-co-error">{{ $message }}</p>
                        @enderror
                    </div>
                    </div>
                </div>

                {{-- BILLETERA DIGITAL --}}
                <div x-show="metodo === 'billetera'" x-cloak class="hasp-co-group">
                    <div class="hasp-co-field">
                        <span class="hasp-co-label">Billetera</span>
                        <div class="hasp-co-radios">
                            <label><input type="radio" name="billetera" value="yape" x-model="billetera"> Yape</label>
                            <label><input type="radio" name="billetera" value="plin" x-model="billetera"> Plin</label>
                        </div>
                        @error('billetera')
                            <p class="hasp-co-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="hasp-co-field">
                        <label for="celular">Número de celular</label>
                        <input id="celular" type="text" name="celular" value="{{ old('celular') }}" autocomplete="off" inputmode="numeric" maxlength="11" placeholder="900 000 001" @input="formatearCelular($event)" :required="metodo === 'billetera'">
                        @error('celular')
                            <p class="hasp-co-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="hasp-co-field">
                        <label for="codigo_billetera">Código de aprobación (6 dígitos)</label>
                        <input id="codigo_billetera" type="password" name="codigo_billetera" inputmode="numeric" autocomplete="off" maxlength="6" placeholder="123456" :required="metodo === 'billetera'">
                        @error('codigo_billetera')
                            <p class="hasp-co-error">{{ $message }}</p>
                        @enderror
                        <p class="hasp-co-hint">En la app real, este código aparece en tu aplicación de Yape o Plin. Aquí sirve cualquiera de 6 dígitos.</p>
                    </div>
                </div>

                <p class="hasp-co-hint">Pago simulado con fines académicos. No se procesa ninguna transacción real ni se guardan los datos de la tarjeta.</p>

                <button type="submit" class="hasp-btn-checkout">
                    <span x-show="metodo === 'efectivo'">🔒 Confirmar pedido (pagar en caja)</span>
                    <span x-show="metodo !== 'efectivo'">🔒 Pagar S/ <span x-text="total().toFixed(2)"></span></span>
                </button>
                <a href="/menu" class="hasp-co-back">← Seguir comprando</a>
            </div>
        </form>
    </div>
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
