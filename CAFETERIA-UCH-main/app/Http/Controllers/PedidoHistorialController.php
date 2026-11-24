<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedidoHistorialController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));

        $fechaHistorial = (string) $request->query('fecha', '');
        $fechaValida = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHistorial) === 1
            && checkdate((int) substr($fechaHistorial, 5, 2), (int) substr($fechaHistorial, 8, 2), (int) substr($fechaHistorial, 0, 4));

        if (! $fechaValida) {
            $fechaHistorial = today()->toDateString();
        }

        $relaciones = [
            'cliente',
            'detalles.producto',
            'detalles.menu',
            'detalles.entrada',
            'detalles.fondo',
            'detalles.refresco',
        ];

        $consultaHistorial = Pedido::with($relaciones)
            ->whereIn('estado', ['entregado', 'cancelado'])
            ->whereDate('created_at', $fechaHistorial);

        $this->aplicarBusqueda($consultaHistorial, $buscar);

        $historial = $consultaHistorial
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        $historial->each(function (Pedido $pedido): void {
            $resumen = $pedido->detalles
                ->map(fn (DetallePedido $detalle): string => $detalle->cantidad.'x '.(
                    $detalle->tipo_item === 'menu'
                        ? ($detalle->menu->titulo ?? 'Menú del día')
                        : ($detalle->producto->nombre ?? 'Producto')
                ))
                ->implode(', ');

            $pedido->setAttribute('resumen', $resumen !== '' ? $resumen : 'Consulta en caja');
        });

        return view('admin.pedidos.historial', [
            'historial' => $historial,
            'buscar' => $buscar,
            'fechaHistorial' => $fechaHistorial,
        ]);
    }

    /**
     * @param  Builder<Pedido>  $query
     */
    private function aplicarBusqueda(Builder $query, string $buscar): void
    {
        if ($buscar === '') {
            return;
        }

        if (ctype_digit($buscar)) {
            $query->where('id', (int) $buscar);

            return;
        }

        $query->whereHas('cliente', function (Builder $query) use ($buscar): void {
            $query->where('name', 'like', '%'.$buscar.'%')
                ->orWhere('email', 'like', '%'.$buscar.'%');
        });
    }
}
