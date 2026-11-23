<?php

namespace App\Http\Controllers;

use App\Models\InventarioMovimiento;
use App\Models\MenuDiaOpcion;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function index(): View
    {
        $productos = Producto::with('categoria')
            ->orderBy('stock_actual')
            ->orderBy('nombre')
            ->get();

        $opciones = MenuDiaOpcion::with(['componente', 'menu'])
            ->whereNotNull('stock_raciones')
            ->orderBy('stock_raciones')
            ->get();

        $movimientos = InventarioMovimiento::with('usuario')
            ->latest()
            ->take(20)
            ->get();

        $inventario = $productos->map(fn (Producto $producto): array => [
            'tipo' => InventarioMovimiento::TIPO_PRODUCTO,
            'item_id' => $producto->id,
            'referencia' => $producto->sku ?: 'PRO-'.$producto->id,
            'nombre' => $producto->nombre,
            'descripcion' => $producto->descripcion ?: 'Producto del catálogo UCH',
            'categoria' => $producto->categoria->nombre ?? 'Sin categoría',
            'stock' => (int) $producto->stock_actual,
            'minimo' => (int) $producto->stock_minimo,
            'unidad' => 'unid.',
            'estado' => $producto->stock_actual <= 0
                ? 'agotado'
                : ($producto->stock_actual <= $producto->stock_minimo ? 'bajo' : 'disponible'),
            'imagen' => $producto->imagen_url,
        ])->concat($opciones->map(function (MenuDiaOpcion $opcion): array {
            $imagen = $opcion->menu->imagen;

            return [
                'tipo' => InventarioMovimiento::TIPO_OPCION_MENU,
                'item_id' => $opcion->id,
                'referencia' => 'MEN-'.strtoupper(substr($opcion->menu->tipo_menu, 0, 3)).'-'.str_pad((string) $opcion->id, 3, '0', STR_PAD_LEFT),
                'nombre' => $opcion->componente->nombre,
                'descripcion' => $opcion->menu->titulo,
                'categoria' => 'Menú del Día',
                'stock' => (int) $opcion->stock_raciones,
                'minimo' => 5,
                'unidad' => 'rac.',
                'estado' => $opcion->stock_raciones <= 0
                    ? 'agotado'
                    : ($opcion->stock_raciones <= 5 ? 'bajo' : 'disponible'),
                'imagen' => $imagen
                    ? (preg_match('#^https?://#i', $imagen) ? $imagen : '/'.ltrim($imagen, '/'))
                    : null,
            ];
        }))->values();

        $porClave = $inventario->keyBy(fn (array $item): string => $item['tipo'].'-'.$item['item_id']);
        $movimientos->each(function (InventarioMovimiento $movimiento) use ($porClave): void {
            $item = $porClave->get($movimiento->tipo_item.'-'.$movimiento->item_id);
            $movimiento->setAttribute('nombre_item', $item['nombre'] ?? 'Ítem #'.$movimiento->item_id);
        });

        $alertasProductos = $productos->filter(fn (Producto $producto): bool => $producto->activo && $producto->stock_actual <= $producto->stock_minimo)->count();
        $alertasRaciones = $opciones->filter(fn (MenuDiaOpcion $opcion): bool => $opcion->stock_raciones <= 5)->count();
        $alertasStock = $alertasProductos + $alertasRaciones;
        $racionesDisponibles = (int) $opciones->sum(fn (MenuDiaOpcion $opcion): int => max(0, (int) $opcion->stock_raciones));
        $ultimaAuditoria = $movimientos->first()?->created_at?->format('d/m H:i') ?? 'Sin registros';
        $totalRegistros = $inventario->count();
        $motivos = ['Reposición', 'Merma / Dañado', 'Corrección', 'Venta web'];

        return view('admin.inventario.index', compact(
            'inventario',
            'productos',
            'opciones',
            'movimientos',
            'alertasStock',
            'racionesDisponibles',
            'ultimaAuditoria',
            'totalRegistros',
            'motivos',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'tipo_item' => ['required', Rule::in([InventarioMovimiento::TIPO_PRODUCTO, InventarioMovimiento::TIPO_OPCION_MENU])],
            'item_id' => 'required|integer|min:1',
            'operacion' => ['required', Rule::in([InventarioMovimiento::OPERACION_INGRESO, InventarioMovimiento::OPERACION_SALIDA])],
            'cantidad' => 'required|integer|min:1|max:10000',
            'motivo_categoria' => ['required', Rule::in(['Reposición', 'Merma / Dañado', 'Corrección', 'Venta web'])],
            'comentario' => 'required|string|min:5|max:500',
        ]);

        $esProducto = $datos['tipo_item'] === InventarioMovimiento::TIPO_PRODUCTO;
        $campoStock = $esProducto ? 'stock_actual' : 'stock_raciones';

        DB::transaction(function () use ($datos, $campoStock, $esProducto): void {
            $modelo = $esProducto
                ? Producto::whereKey($datos['item_id'])->lockForUpdate()->firstOrFail()
                : MenuDiaOpcion::whereKey($datos['item_id'])->lockForUpdate()->firstOrFail();

            $stockAnterior = (int) $modelo->{$campoStock};
            $cantidad = (int) $datos['cantidad'];
            $stockNuevo = $datos['operacion'] === InventarioMovimiento::OPERACION_INGRESO
                ? $stockAnterior + $cantidad
                : $stockAnterior - $cantidad;

            if ($stockNuevo < 0) {
                throw ValidationException::withMessages([
                    'cantidad' => 'No puedes restar más unidades de las disponibles en stock.',
                ]);
            }

            $modelo->update([$campoStock => $stockNuevo]);

            InventarioMovimiento::create([
                'tipo_item' => $datos['tipo_item'],
                'item_id' => $modelo->id,
                'operacion' => $datos['operacion'],
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'motivo_categoria' => $datos['motivo_categoria'],
                'comentario' => $datos['comentario'],
                'usuario_id' => auth()->id(),
            ]);
        });

        return redirect('/admin/inventario')->with('mensaje', 'Stock actualizado y movimiento registrado.');
    }
}
