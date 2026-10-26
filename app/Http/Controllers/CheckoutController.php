<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\DetallePedido;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout');
    }

    public function procesar(Request $request)
    {
        $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'numero_tarjeta' => 'required|string|min:12',
            'nombre_tarjeta' => 'required|string',
            'vencimiento' => 'required|string',
            'cvv' => 'required|string|min:3|max:4',
        ]);

        $pedido = Pedido::create([
            'cliente_id' => auth()->id(),
            'estado' => 'pendiente',
            'total' => 0,
            'pagado' => true,
            'metodo_pago' => 'Tarjeta (simulado)',
        ]);

        $total = 0;

        foreach ($request->productos as $item) {
            $producto = Producto::find($item['id']);
            $subtotal = $producto->precio * $item['cantidad'];
            $total += $subtotal;

            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $producto->id,
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $producto->precio,
            ]);
        }

        $pedido->update(['total' => $total]);

        return redirect('/mis-pedidos')->with('mensaje', '¡Pago simulado exitoso! Tu pedido #' . $pedido->id . ' fue recibido.');
    }
}