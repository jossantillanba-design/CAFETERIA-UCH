<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminSearchController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $termino = trim((string) $request->query('q', ''));

        if ($termino === '') {
            return redirect('/admin/dashboard');
        }

        if (ctype_digit($termino) && Pedido::whereKey((int) $termino)->exists()) {
            $pedido = Pedido::findOrFail((int) $termino);

            return redirect(in_array($pedido->estado, ['entregado', 'cancelado'], true)
                ? '/admin/historial-pedidos?buscar='.urlencode($termino)
                : '/admin/pedidos?buscar='.urlencode($termino));
        }

        $coincideProducto = Producto::where('sku', $termino)
            ->orWhere('nombre', 'like', '%'.$termino.'%')
            ->exists();

        if ($coincideProducto) {
            return redirect('/admin/productos?buscar='.urlencode($termino));
        }

        $coincideCliente = User::where('rol', 'cliente')
            ->where(function ($query) use ($termino): void {
                $query->where('name', 'like', '%'.$termino.'%')
                    ->orWhere('email', 'like', '%'.$termino.'%');
            })
            ->exists();

        if ($coincideCliente) {
            return redirect('/admin/clientes?buscar='.urlencode($termino));
        }

        return redirect('/admin/pedidos?buscar='.urlencode($termino))
            ->with('error', 'No se encontraron resultados para "'.$termino.'".');
    }
}
