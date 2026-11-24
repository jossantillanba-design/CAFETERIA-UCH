<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\InventarioMovimiento;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PedidoClienteController extends Controller
{
    public function store(Request $request)
    {
        $datos = $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.id' => ['required', Rule::exists('productos', 'id')->where('activo', true)],
            'productos.*.cantidad' => 'required|integer|min:1|max:50',
        ]);

        $pedido = DB::transaction(function () use ($datos) {
            $pedido = Pedido::create([
                'cliente_id' => auth()->id(),
                'estado' => 'pendiente',
                'total' => 0,
            ]);

            $total = 0;

            foreach ($datos['productos'] as $item) {
                $producto = Producto::whereKey($item['id'])->lockForUpdate()->firstOrFail();
                $cantidad = (int) $item['cantidad'];

                if ($producto->stock_actual < $cantidad) {
                    throw ValidationException::withMessages([
                        'productos' => 'Stock insuficiente para '.$producto->nombre.'.',
                    ]);
                }

                $stockAnterior = (int) $producto->stock_actual;
                $stockNuevo = $stockAnterior - $cantidad;
                $producto->update(['stock_actual' => $stockNuevo]);

                InventarioMovimiento::create([
                    'tipo_item' => InventarioMovimiento::TIPO_PRODUCTO,
                    'item_id' => $producto->id,
                    'operacion' => InventarioMovimiento::OPERACION_VENTA,
                    'cantidad' => $cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'motivo_categoria' => 'Venta web',
                    'comentario' => 'Pedido #'.$pedido->id,
                    'usuario_id' => auth()->id(),
                ]);

                $subtotal = $producto->precio * $cantidad;
                $total += $subtotal;

                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'tipo_item' => 'producto',
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotal,
                ]);
            }

            $pedido->update(['total' => $total]);

            return $pedido;
        });

        return redirect('/mis-pedidos')->with('mensaje', '¡Tu pedido fue enviado! Pronto lo prepararemos.');
    }

    public function index()
    {
        $pedidos = Pedido::with('detalles.producto', 'detalles.menu')
            ->where('cliente_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mis-pedidos', ['pedidos' => $pedidos]);
    }
}
