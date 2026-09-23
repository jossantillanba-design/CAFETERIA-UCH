<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;

class DashboardController extends Controller
{
    public function index()
    {
        $ventasHoy = Pedido::whereDate('created_at', today())
            ->where('estado', '!=', 'cancelado')
            ->sum('total');

        $pedidosHoy = Pedido::whereDate('created_at', today())
            ->where('estado', '!=', 'cancelado')
            ->count();

        $pedidosPendientes = Pedido::whereIn('estado', ['pendiente', 'en_preparacion'])->count();

        $totalProductos = Producto::count();

        $ultimosPedidos = Pedido::with('cliente')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'ventasHoy' => $ventasHoy,
            'pedidosHoy' => $pedidosHoy,
            'pedidosPendientes' => $pedidosPendientes,
            'totalProductos' => $totalProductos,
            'ultimosPedidos' => $ultimosPedidos,
        ]);
    }
}