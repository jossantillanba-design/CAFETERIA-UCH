<?php

namespace App\Http\Controllers\Empleado;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\OrdenCajaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PedidoController extends Controller
{
    /**
     * @var array<string, array<int, string>>
     */
    private const TRANSICIONES = [
        'pendiente' => ['en_preparacion', 'cancelado'],
        'en_preparacion' => ['listo'],
        'listo' => ['entregado'],
    ];

    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $estadoFiltro = (string) $request->query('estado', 'todos');

        if (! in_array($estadoFiltro, ['todos', 'pendiente', 'en_preparacion', 'listo'], true)) {
            $estadoFiltro = 'todos';
        }

        $estadosActivos = ['pendiente', 'en_preparacion', 'listo'];

        $relaciones = [
            'cliente',
            'detalles.producto',
            'detalles.menu',
            'detalles.entrada',
            'detalles.fondo',
            'detalles.refresco',
        ];

        $conteos = Pedido::query()
            ->whereIn('estado', $estadosActivos)
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $consulta = Pedido::with($relaciones)->whereIn('estado', $estadosActivos);
        $this->aplicarBusqueda($consulta, $buscar);

        $pedidos = $consulta
            ->when($estadoFiltro !== 'todos', fn (Builder $query): Builder => $query->where('estado', $estadoFiltro))
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return view('empleado.pedidos', [
            'pedidos' => $pedidos,
            'buscar' => $buscar,
            'estadoFiltro' => $estadoFiltro,
            'conteos' => $conteos,
            'fechaTurno' => ucfirst(today()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')),
        ]);
    }

    public function actualizarEstado(Request $request, Pedido $pedido, OrdenCajaService $servicio): RedirectResponse
    {
        $datos = $request->validate([
            'estado' => 'required|in:en_preparacion,listo,entregado,cancelado',
        ]);

        $permitidos = self::TRANSICIONES[$pedido->estado] ?? [];

        if (! in_array($datos['estado'], $permitidos, true)) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede aplicar esa transición desde el estado actual del pedido.',
            ]);
        }

        if ($datos['estado'] === 'cancelado') {
            $servicio->anular($pedido);
        } else {
            $pedido->update(['estado' => $datos['estado']]);
        }

        return redirect()->route('empleado.pedidos.index')->with('mensaje', 'Pedido #'.$pedido->id.' actualizado.');
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
            $query->where(function (Builder $query) use ($buscar): void {
                $query->where('id', (int) $buscar)
                    ->orWhere('codigo_transaccion', 'like', '%'.$buscar.'%');
            });

            return;
        }

        $query->where(function (Builder $query) use ($buscar): void {
            $query->whereHas('cliente', function (Builder $query) use ($buscar): void {
                $query->where('name', 'like', '%'.$buscar.'%')
                    ->orWhere('email', 'like', '%'.$buscar.'%');
            })->orWhere('cliente_nombre_caja', 'like', '%'.$buscar.'%')
                ->orWhere('codigo_transaccion', 'like', '%'.$buscar.'%');
        });
    }
}
