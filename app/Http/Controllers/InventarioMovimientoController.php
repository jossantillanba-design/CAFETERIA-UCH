<?php

namespace App\Http\Controllers;

use App\Models\InventarioMovimiento;
use App\Models\MenuDiaOpcion;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventarioMovimientoController extends Controller
{
    public function index(Request $request): View
    {
        $motivos = ['Reposición', 'Merma / Dañado', 'Corrección', 'Venta web'];
        $operaciones = [
            InventarioMovimiento::OPERACION_INGRESO,
            InventarioMovimiento::OPERACION_SALIDA,
            InventarioMovimiento::OPERACION_VENTA,
        ];
        $tipos = [InventarioMovimiento::TIPO_PRODUCTO, InventarioMovimiento::TIPO_OPCION_MENU];

        $buscar = trim($request->string('buscar')->toString());
        $operacionFiltro = $request->string('operacion')->toString();
        $motivoFiltro = $request->string('motivo')->toString();
        $tipoFiltro = $request->string('tipo')->toString();

        if (! in_array($operacionFiltro, $operaciones, true)) {
            $operacionFiltro = 'all';
        }

        if (! in_array($motivoFiltro, $motivos, true)) {
            $motivoFiltro = 'all';
        }

        if (! in_array($tipoFiltro, $tipos, true)) {
            $tipoFiltro = 'all';
        }

        $consulta = InventarioMovimiento::with('usuario');

        if ($buscar !== '') {
            $idsProductos = Producto::where('nombre', 'like', '%'.$buscar.'%')->pluck('id');
            $idsOpciones = MenuDiaOpcion::whereHas('componente', function (EloquentBuilder $query) use ($buscar): void {
                $query->where('nombre', 'like', '%'.$buscar.'%');
            })->pluck('id');

            $consulta->where(function (EloquentBuilder $query) use ($buscar, $idsProductos, $idsOpciones): void {
                $query->where('comentario', 'like', '%'.$buscar.'%')
                    ->orWhere('motivo_categoria', 'like', '%'.$buscar.'%')
                    ->orWhere(function (EloquentBuilder $query) use ($idsProductos): void {
                        $query->where('tipo_item', InventarioMovimiento::TIPO_PRODUCTO)
                            ->whereIn('item_id', $idsProductos);
                    })
                    ->orWhere(function (EloquentBuilder $query) use ($idsOpciones): void {
                        $query->where('tipo_item', InventarioMovimiento::TIPO_OPCION_MENU)
                            ->whereIn('item_id', $idsOpciones);
                    });
            });
        }

        if ($operacionFiltro !== 'all') {
            $consulta->where('operacion', $operacionFiltro);
        }

        if ($motivoFiltro !== 'all') {
            $consulta->where('motivo_categoria', $motivoFiltro);
        }

        if ($tipoFiltro !== 'all') {
            $consulta->where('tipo_item', $tipoFiltro);
        }

        $movimientos = $consulta->latest()->paginate(12)->withQueryString();

        $elementos = $movimientos->getCollection();
        $idsProductosVisibles = $elementos->where('tipo_item', InventarioMovimiento::TIPO_PRODUCTO)
            ->pluck('item_id')
            ->unique()
            ->all();
        $idsOpcionesVisibles = $elementos->where('tipo_item', InventarioMovimiento::TIPO_OPCION_MENU)
            ->pluck('item_id')
            ->unique()
            ->all();

        $nombresProductos = Producto::whereIn('id', $idsProductosVisibles)->pluck('nombre', 'id');
        $nombresOpciones = MenuDiaOpcion::with('componente')
            ->whereIn('id', $idsOpcionesVisibles)
            ->get()
            ->mapWithKeys(function (MenuDiaOpcion $opcion): array {
                return [$opcion->id => $opcion->componente->nombre ?? 'Ítem #'.$opcion->id];
            });

        $elementos->each(function (InventarioMovimiento $movimiento) use ($nombresProductos, $nombresOpciones): void {
            $nombre = $movimiento->tipo_item === InventarioMovimiento::TIPO_PRODUCTO
                ? ($nombresProductos[$movimiento->item_id] ?? null)
                : ($nombresOpciones[$movimiento->item_id] ?? null);

            $movimiento->setAttribute('nombre_item', $nombre ?? 'Ítem #'.$movimiento->item_id);
        });

        $metricas = [
            'total' => InventarioMovimiento::count(),
            'ingresos' => InventarioMovimiento::where('operacion', InventarioMovimiento::OPERACION_INGRESO)->count(),
            'salidas' => InventarioMovimiento::whereIn('operacion', [
                InventarioMovimiento::OPERACION_SALIDA,
                InventarioMovimiento::OPERACION_VENTA,
            ])->count(),
            'hoy' => InventarioMovimiento::whereDate('created_at', today())->count(),
        ];

        return view('admin.movimientos.index', [
            'movimientos' => $movimientos,
            'metricas' => $metricas,
            'motivos' => $motivos,
            'buscar' => $buscar,
            'operacionFiltro' => $operacionFiltro === 'all' ? '' : $operacionFiltro,
            'motivoFiltro' => $motivoFiltro === 'all' ? '' : $motivoFiltro,
            'tipoFiltro' => $tipoFiltro === 'all' ? '' : $tipoFiltro,
        ]);
    }
}
