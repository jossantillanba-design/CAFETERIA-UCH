<?php

namespace App\Http\Controllers\Empleado;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrdenCajaRequest;
use App\Models\MenuDia;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\OrdenCajaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TomarOrdenController extends Controller
{
    public function create(): View
    {
        $menus = MenuDia::query()
            ->activos()
            ->where(function (Builder $query): void {
                $query->whereNull('fecha_vigencia')
                    ->orWhere('fecha_vigencia', today()->toDateString());
            })
            ->with('opciones.componente')
            ->orderBy('tipo_menu')
            ->get();

        $productos = Producto::query()
            ->activos()
            ->where('stock_actual', '>', 0)
            ->orderBy('nombre')
            ->get();

        return view('empleado.tomar-orden', [
            'menus' => $menus,
            'productos' => $productos,
        ]);
    }

    public function store(StoreOrdenCajaRequest $request, OrdenCajaService $servicio): RedirectResponse
    {
        $datos = $request->validated();

        $datos['monto_recibido'] = $datos['metodo_pago'] === Pedido::METODO_EFECTIVO
            ? $datos['monto_recibido']
            : null;

        $pedido = $servicio->crearPedido($datos, $datos['productos']);

        return redirect()->route('empleado.pedidos.index')
            ->with('mensaje', 'Ticket #'.$pedido->id.' registrado por S/ '.number_format((float) $pedido->total, 2).'.');
    }
}
