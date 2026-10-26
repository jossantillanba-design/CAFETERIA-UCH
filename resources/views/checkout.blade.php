<x-layouts.app>
    <div x-data="checkout()" x-init="init()" class="max-w-lg mx-auto">
        <h1 class="text-3xl font-bold mb-6">Pagar pedido</h1>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="font-bold text-lg mb-3">Resumen</h2>
            <template x-for="item in items" :key="item.id">
                <div class="flex justify-between text-sm mb-2">
                    <span x-text="item.cantidad + 'x ' + item.nombre"></span>
                    <span x-text="'S/' + (item.precio * item.cantidad).toFixed(2)"></span>
                </div>
            </template>
            <div class="border-t mt-3 pt-3 flex justify-between font-bold">
                <span>Total</span>
                <span x-text="'S/' + total().toFixed(2)"></span>
            </div>
        </div>

        <form action="/checkout" method="POST" class="bg-white rounded-lg shadow p-6">
            @csrf
            <template x-for="(item, index) in items" :key="item.id">
                <div>
                    <input type="hidden" :name="'productos[' + index + '][id]'" :value="item.id">
                    <input type="hidden" :name="'productos[' + index + '][cantidad]'" :value="item.cantidad">
                </div>
            </template>

            <h2 class="font-bold text-lg mb-4">Datos de pago (simulado)</h2>

            <label class="block mb-2 text-sm font-semibold">Número de tarjeta</label>
            <input type="text" name="numero_tarjeta" maxlength="19" placeholder="1234 5678 9012 3456" class="w-full border rounded p-2 mb-4" required>

            <label class="block mb-2 text-sm font-semibold">Nombre en la tarjeta</label>
            <input type="text" name="nombre_tarjeta" placeholder="Como aparece en la tarjeta" class="w-full border rounded p-2 mb-4" required>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block mb-2 text-sm font-semibold">Vencimiento</label>
                    <input type="text" name="vencimiento" placeholder="MM/AA" maxlength="5" class="w-full border rounded p-2" required>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold">CVV</label>
                    <input type="text" name="cvv" placeholder="123" maxlength="4" class="w-full border rounded p-2" required>
                </div>
            </div>

            <p class="text-xs text-gray-400 mb-4">⚠️ Este es un pago simulado con fines académicos. No se procesa ninguna transacción real.</p>

            <button type="submit" class="w-full bg-green-700 text-white py-3 rounded font-bold">
                Pagar S/<span x-text="total().toFixed(2)"></span>
            </button>
        </form>
    </div>

    <script>
        function checkout() {
            return {
                items: [],
                init() {
                    const guardado = localStorage.getItem('carrito_checkout');
                    if (guardado) {
                        this.items = JSON.parse(guardado);
                    }
                    if (this.items.length === 0) {
                        window.location.href = '/menu';
                    }
                },
                total() {
                    return this.items.reduce((sum, i) => sum + (i.precio * i.cantidad), 0);
                }
            }
        }
    </script>
</x-layouts.app>