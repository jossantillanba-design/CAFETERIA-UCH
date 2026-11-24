<?php

namespace App\Http\Controllers\Empleado;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function show(Pedido $pedido): View
    {
        $pedido->load([
            'cliente',
            'atendidoPor',
            'detalles.producto',
            'detalles.menu',
            'detalles.entrada',
            'detalles.fondo',
            'detalles.refresco',
        ]);

        $subtotal = round((float) $pedido->total / 1.18, 2);

        return view('empleado.ticket', [
            'pedido' => $pedido,
            'subtotal' => $subtotal,
            'igv' => round((float) $pedido->total - $subtotal, 2),
            'codigoOperacion' => $pedido->codigo_transaccion
                ?: 'UCH-'.str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT).'-'.$pedido->created_at->format('Ymd'),
        ]);
    }
}
