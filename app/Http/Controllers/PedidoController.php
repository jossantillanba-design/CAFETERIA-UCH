<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\DetallePedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    // Lista de pedidos para el trabajador
    public function index()
    {
        $pedidos = Pedido::with(['cliente', 'detalles.producto'])
            ->whereIn('estado', ['pendiente', 'en_preparacion'])
            ->orderBy('created_at')
            ->get();

        return view('admin.pedidos.index', ['pedidos' => $pedidos]);
    }

    // Cambia el estado de un pedido (ej. de "pendiente" a "listo")
    public function actualizarEstado(Request $request, Pedido $pedido)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_preparacion,listo,entregado,cancelado',
        ]);

        $pedido->update(['estado' => $request->estado]);

        return redirect('/admin/pedidos')->with('mensaje', 'Estado actualizado.');
    }

    // Formulario para crear un ticket manual
    public function create()
    {
        $productos = Producto::all();
        return view('admin.pedidos.create', ['productos' => $productos]);
    }

    // Guarda el ticket manual
    public function store(Request $request)
    {
        $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        $pedido = Pedido::create([
            'atendido_por' => auth()->id(),
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

        return redirect('/admin/pedidos')->with('mensaje', 'Ticket creado correctamente.');
    }
}