<x-layouts.app>
    <div x-data="checkout(@js(old('metodo', 'tarjeta')), @js(old('billetera', 'yape')))" x-init="init()"
         class="mx-auto max-w-3xl px-4 py-8 sm:px-6">

        <header class="mb-6">
            <p class="mb-1 flex items-center gap-1.5 text-label-md font-bold uppercase tracking-widest text-uch-blue">
                <span class="material-symbols-outlined text-[18px]">restaurant</span>
                Comedor UCH · Pabellón A
            </p>
            <h1 class="font-rubik text-headline-lg-mobile text-uch-navy lg:text-headline-lg">Pagar pedido</h1>
            <p class="mt-1 text-body-md text-uch-muted">Revisa tu pedido y elige cómo quieres pagar.</p>
        </header>

        @if (session('error'))
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-uch-coral/30 bg-uch-coral/10 px-4 py-3 text-body-sm font-semibold text-uch-coral-dark">
                <span class="material-symbols-outlined shrink-0 text-[20px]">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @error('productos')
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-uch-coral/30 bg-uch-coral/10 px-4 py-3 text-body-sm font-semibold text-uch-coral-dark">
                <span class="material-symbols-outlined shrink-0 text-[20px]">error</span>
                <span>{{ $message }}</span>
            </div>
        @enderror

        <div x-show="items.length === 0" x-cloak
             class="mb-6 rounded-card border border-uch-border bg-uch-surface p-10 text-center shadow-uch-card">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-uch-bg text-uch-muted">
                <x-icon name="shopping_bag" class="h-8 w-8" />
            </div>
            <p class="font-rubik text-headline-sm text-uch-navy">Tu pedido está vacío</p>
            <p class="mt-space-xs text-body-md text-uch-muted">Agrega productos desde el menú para continuar.</p>
            <a href="/menu"
               class="mt-space-md inline-flex items-center gap-2 rounded-[10px] bg-uch-coral px-5 py-2.5 text-label-md font-bold text-white transition-all hover:bg-uch-coral-dark hover:shadow-uch-card">
                Volver al menú
                <x-icon name="arrow_forward" class="h-[18px] w-[18px]" />
            </a>
        </div>

        <section x-show="items.length > 0" x-cloak
                 class="mb-6 overflow-hidden rounded-card border border-uch-border bg-uch-surface shadow-uch-card">
            <div class="flex items-center gap-2 border-b border-uch-border bg-uch-bg px-6 py-3.5">
                <span class="material-symbols-outlined text-[20px] text-uch-blue">receipt_long</span>
                <h2 class="font-rubik text-headline-sm text-uch-navy">Resumen del pedido</h2>
            </div>
            <div class="p-6">
                <template x-for="item in items" :key="item.id">
                    <div class="flex items-start justify-between gap-3 border-b border-uch-border py-2.5 last:border-0">
                        <div class="min-w-0">
                            <p class="text-body-md font-bold text-uch-text" x-text="item.cantidad + 'x ' + item.nombre"></p>
                            <p class="text-body-sm text-uch-muted" x-text="item.detalle || ''"></p>
                        </div>
                        <span class="shrink-0 font-rubik text-price-tag text-uch-navy"
                              x-text="'S/' + (item.precio * item.cantidad).toFixed(2)"></span>
                    </div>
                </template>
                <div class="mt-3 flex items-center justify-between border-t border-uch-border pt-4">
                    <span class="text-label-lg font-bold uppercase tracking-wide text-uch-muted">Total a pagar</span>
                    <span class="font-rubik text-headline-sm text-uch-navy" x-text="'S/' + total().toFixed(2)"></span>
                </div>
            </div>
        </section>

        <details x-show="items.length > 0" x-cloak
                 class="group mb-6 rounded-card border border-uch-blue/20 bg-uch-blue/5 text-body-sm text-uch-text">
            <summary class="flex cursor-pointer items-center gap-2 px-4 py-3 font-rubik text-label-lg font-bold text-uch-blue">
                <span class="material-symbols-outlined text-[20px]">info</span>
                Datos de prueba
                <span class="material-symbols-outlined ml-auto text-[20px] transition-transform group-open:rotate-180">expand_more</span>
            </summary>
            <div class="border-t border-uch-blue/15 px-4 py-4">
                <ul class="grid gap-2 sm:grid-cols-2">
                    <li><b class="text-uch-navy">Tarjeta aprobada:</b> 4111 1111 1111 1111</li>
                    <li><b class="text-uch-navy">Tarjeta sin fondos:</b> 4000 0400 0000 0008</li>
                    <li><b class="text-uch-navy">Tarjeta con CVV incorrecto:</b> 5400 0200 0000 0003</li>
                    <li><b class="text-uch-navy">Billetera aprobada:</b> celular 900 000 001 y cualquier código de 6 dígitos</li>
                    <li><b class="text-uch-navy">Billetera sin saldo:</b> celular 900 000 002</li>
                    <li><b class="text-uch-navy">Código incorrecto:</b> 000000</li>
                </ul>
                <p class="mt-3 text-body-sm text-uch-muted">En tarjeta usa un vencimiento futuro (por ejemplo 12/30) y un CVV de 3 dígitos.</p>
            </div>
        </details>

        <form x-show="items.length > 0" x-cloak action="/checkout" method="POST"
              class="overflow-hidden rounded-card border border-uch-border bg-uch-surface shadow-uch-card">
            @csrf
            <div class="h-2 w-full bg-uch-blue">
                <div class="h-full w-28 bg-uch-cyan"></div>
            </div>

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

            <div class="space-y-6 p-6 sm:p-8">
                <div>
                    <h2 class="font-rubik text-headline-sm text-uch-navy">Método de pago</h2>
                    <p class="mt-0.5 text-body-sm text-uch-muted">Selecciona con cuál medio deseas cancelar.</p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <button type="button" @click="metodo = 'efectivo'"
                            :class="metodo === 'efectivo'
                                ? 'border-uch-blue bg-uch-blue/10 text-uch-navy shadow-uch-card'
                                : 'border-uch-border bg-uch-surface text-uch-muted hover:border-uch-cyan hover:bg-uch-bg'"
                            class="flex flex-col items-center gap-2 rounded-xl border-2 px-3 py-4 transition-all">
                        <span class="material-symbols-outlined text-[28px]">payments</span>
                        <span class="text-label-lg font-bold">Efectivo</span>
                    </button>
                    <button type="button" @click="metodo = 'tarjeta'"
                            :class="metodo === 'tarjeta'
                                ? 'border-uch-blue bg-uch-blue/10 text-uch-navy shadow-uch-card'
                                : 'border-uch-border bg-uch-surface text-uch-muted hover:border-uch-cyan hover:bg-uch-bg'"
                            class="flex flex-col items-center gap-2 rounded-xl border-2 px-3 py-4 transition-all">
                        <span class="material-symbols-outlined text-[28px]">credit_card</span>
                        <span class="text-label-lg font-bold">Tarjeta</span>
                    </button>
                    <button type="button" @click="metodo = 'billetera'"
                            :class="metodo === 'billetera'
                                ? 'border-uch-blue bg-uch-blue/10 text-uch-navy shadow-uch-card'
                                : 'border-uch-border bg-uch-surface text-uch-muted hover:border-uch-cyan hover:bg-uch-bg'"
                            class="flex flex-col items-center gap-2 rounded-xl border-2 px-3 py-4 transition-all">
                        <span class="material-symbols-outlined text-[28px]">smartphone</span>
                        <span class="text-label-lg font-bold">Billetera</span>
                    </button>
                </div>
                @error('metodo')
                    <p class="-mt-3 text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                @enderror

                {{-- EFECTIVO --}}
                <div x-show="metodo === 'efectivo'" x-cloak
                     class="flex items-start gap-3 rounded-xl border border-uch-amber/30 bg-uch-amber/15 px-4 py-3 text-body-sm text-[#B45309]">
                    <span class="material-symbols-outlined shrink-0 text-[20px]">info</span>
                    <p>
                        Tu pedido se registrará ahora y pagarás <b>S/<span x-text="total().toFixed(2)"></span></b>
                        en efectivo en caja al recogerlo. El empleado confirmará tu pago.
                    </p>
                </div>

                {{-- TARJETA --}}
                <div x-show="metodo === 'tarjeta'" x-cloak class="space-y-4">
                    <div class="space-y-1.5">
                        <label for="numero_tarjeta" class="block text-label-md font-bold text-uch-navy">Número de tarjeta</label>
                        <input id="numero_tarjeta" type="text" name="numero_tarjeta" inputmode="numeric" autocomplete="off" maxlength="23"
                               placeholder="4111 1111 1111 1111" @input="formatearTarjeta($event)"
                               :required="metodo === 'tarjeta'"
                               class="w-full rounded-lg border border-uch-border bg-uch-surface py-2.5 pl-3.5 pr-10 text-body-md text-uch-text transition-all placeholder:text-uch-muted focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none">
                        @error('numero_tarjeta')
                            <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="nombre_tarjeta" class="block text-label-md font-bold text-uch-navy">Nombre en la tarjeta</label>
                        <input id="nombre_tarjeta" type="text" name="nombre_tarjeta" value="{{ old('nombre_tarjeta') }}" autocomplete="off"
                               placeholder="Como aparece en la tarjeta" :required="metodo === 'tarjeta'"
                               class="w-full rounded-lg border border-uch-border bg-uch-surface px-3.5 py-2.5 text-body-md text-uch-text transition-all placeholder:text-uch-muted focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none">
                        @error('nombre_tarjeta')
                            <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="vencimiento" class="block text-label-md font-bold text-uch-navy">Vencimiento</label>
                            <input id="vencimiento" type="text" name="vencimiento" inputmode="numeric" autocomplete="off" maxlength="5"
                                   placeholder="MM/AA" @input="formatearFecha($event)"
                                   :required="metodo === 'tarjeta'"
                                   class="w-full rounded-lg border border-uch-border bg-uch-surface px-3.5 py-2.5 text-body-md text-uch-text transition-all placeholder:text-uch-muted focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none">
                            @error('vencimiento')
                                <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label for="cvv" class="block text-label-md font-bold text-uch-navy">CVV</label>
                            <input id="cvv" type="password" name="cvv" inputmode="numeric" autocomplete="off" maxlength="4"
                                   placeholder="123" :required="metodo === 'tarjeta'"
                                   class="w-full rounded-lg border border-uch-border bg-uch-surface px-3.5 py-2.5 text-body-md text-uch-text transition-all placeholder:text-uch-muted focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none">
                            @error('cvv')
                                <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- BILLETERA DIGITAL --}}
                <div x-show="metodo === 'billetera'" x-cloak class="space-y-4">
                    <div class="space-y-1.5">
                        <span class="block text-label-md font-bold text-uch-navy">Billetera</span>
                        <div class="flex flex-wrap gap-6">
                            <label class="flex items-center gap-2 text-body-md text-uch-text">
                                <input type="radio" name="billetera" value="yape" x-model="billetera" class="accent-uch-blue"> Yape
                            </label>
                            <label class="flex items-center gap-2 text-body-md text-uch-text">
                                <input type="radio" name="billetera" value="plin" x-model="billetera" class="accent-uch-blue"> Plin
                            </label>
                        </div>
                        @error('billetera')
                            <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="celular" class="block text-label-md font-bold text-uch-navy">Número de celular</label>
                        <input id="celular" type="text" name="celular" value="{{ old('celular') }}" inputmode="numeric" autocomplete="off" maxlength="11"
                               placeholder="900 000 001" @input="formatearCelular($event)"
                               :required="metodo === 'billetera'"
                               class="w-full rounded-lg border border-uch-border bg-uch-surface px-3.5 py-2.5 text-body-md text-uch-text transition-all placeholder:text-uch-muted focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none">
                        @error('celular')
                            <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="codigo_billetera" class="block text-label-md font-bold text-uch-navy">Código de aprobación (6 dígitos)</label>
                        <input id="codigo_billetera" type="password" name="codigo_billetera" inputmode="numeric" autocomplete="off" maxlength="6"
                               placeholder="123456" :required="metodo === 'billetera'"
                               class="w-full rounded-lg border border-uch-border bg-uch-surface px-3.5 py-2.5 text-body-md text-uch-text transition-all placeholder:text-uch-muted focus:border-uch-blue focus:ring-2 focus:ring-uch-cyan/20 focus:outline-none">
                        @error('codigo_billetera')
                            <p class="text-body-sm font-semibold text-uch-coral-dark">{{ $message }}</p>
                        @enderror
                        <p class="text-body-sm text-uch-muted">En la app real, este código aparece en tu aplicación de Yape o Plin. Aquí sirve cualquiera de 6 dígitos.</p>
                    </div>
                </div>

                <p class="text-body-sm text-uch-muted">
                    Pago simulado con fines académicos. No se procesa ninguna transacción real ni se guardan los datos de la tarjeta.
                </p>

                <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-uch-coral px-5 py-3.5 font-rubik text-label-lg font-bold text-white shadow-uch-card transition-all hover:bg-uch-coral-dark hover:shadow-uch-card-hover active:scale-[0.99]">
                    <span class="material-symbols-outlined text-[20px]">lock</span>
                    <span x-show="metodo === 'efectivo'">Confirmar pedido (pagar en caja)</span>
                    <span x-show="metodo !== 'efectivo'">Pagar S/<span x-text="total().toFixed(2)"></span></span>
                </button>
            </div>
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
