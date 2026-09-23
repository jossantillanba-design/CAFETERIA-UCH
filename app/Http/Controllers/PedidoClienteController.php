<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\DetallePedido;
use Illuminate\Http\Request;

class PedidoClienteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        $pedido = Pedido::create([
            'cliente_id' => auth()->id(),
            'estado' => 'pendiente',
            'total' => 0,
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

        return redirect('/mis-pedidos')->with('mensaje', '¡Tu pedido fue enviado! Pronto lo prepararemos.');
    }

    public function index()
    {
        $pedidos = Pedido::with('detalles.producto')
            ->where('cliente_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mis-pedidos', ['pedidos' => $pedidos]);
    }
}
