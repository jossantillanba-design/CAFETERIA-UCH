<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\InventarioMovimiento;
use App\Models\MenuDia;
use App\Models\MenuDiaOpcion;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $hoy = today();

        $ventasHoy = Pedido::whereDate('created_at', $hoy)
            ->where('estado', '!=', 'cancelado')
            ->where('pagado', true)
            ->sum('total');

        $pedidosHoy = Pedido::whereDate('created_at', $hoy)
            ->where('estado', '!=', 'cancelado')
            ->count();

        $enPreparacion = Pedido::where('estado', 'en_preparacion')->count();

        $totalProductos = Producto::where('activo', true)->count();

        $productosStockBajo = Producto::stockBajo()
            ->with('categoria')
            ->orderBy('stock_actual')
            ->get();

        $racionesStockBajo = MenuDiaOpcion::stockBajo()
            ->with(['componente', 'menu'])
            ->orderBy('stock_raciones')
            ->get();

        $alertasStock = $productosStockBajo->count() + $racionesStockBajo->count();

        $alertasResumen = $productosStockBajo->map(fn (Producto $producto): array => [
            'referencia' => $producto->sku ?: 'PRO-'.$producto->id,
            'nombre' => $producto->nombre,
            'categoria' => $producto->categoria->nombre ?? 'Sin categoría',
            'stock' => (int) $producto->stock_actual,
            'minimo' => (int) $producto->stock_minimo,
            'unidad' => 'u.',
            'critica' => (int) $producto->stock_actual <= 0,
        ])->concat($racionesStockBajo->map(function (MenuDiaOpcion $opcion): array {
            $stock = (int) $opcion->stock_raciones;

            return [
                'referencia' => 'MEN-'.strtoupper(substr($opcion->menu->tipo_menu ?? 'men', 0, 3)).'-'.str_pad((string) $opcion->id, 3, '0', STR_PAD_LEFT),
                'nombre' => $opcion->componente->nombre ?? 'Ración de menú',
                'categoria' => $opcion->menu->titulo ?? 'Menú del día',
                'stock' => $stock,
                'minimo' => 5,
                'unidad' => 'rac.',
                'critica' => $stock <= 0,
            ];
        }))->values()->take(3);

        $ultimosMovimientos = InventarioMovimiento::with('usuario')
            ->latest()
            ->take(3)
            ->get();

        $productosPorId = Producto::whereIn('id', $ultimosMovimientos
            ->where('tipo_item', InventarioMovimiento::TIPO_PRODUCTO)
            ->pluck('item_id'))
            ->pluck('nombre', 'id');

        $racionesPorId = MenuDiaOpcion::whereIn('id', $ultimosMovimientos
            ->where('tipo_item', InventarioMovimiento::TIPO_OPCION_MENU)
            ->pluck('item_id'))
            ->with('componente')
            ->get()
            ->mapWithKeys(fn (MenuDiaOpcion $opcion): array => [
                $opcion->id => $opcion->componente->nombre ?? 'Ración de menú',
            ]);

        $ultimosMovimientos->each(function (InventarioMovimiento $movimiento) use ($productosPorId, $racionesPorId): void {
            $nombre = $movimiento->tipo_item === InventarioMovimiento::TIPO_PRODUCTO
                ? $productosPorId->get($movimiento->item_id)
                : $racionesPorId->get($movimiento->item_id);

            $movimiento->setAttribute('nombre_item', $nombre ?? 'Ítem #'.$movimiento->item_id);
        });

        $ventasPorMenu = DetallePedido::query()
            ->where('tipo_item', 'menu')
            ->whereHas('pedido', function ($query) use ($hoy): void {
                $query->whereDate('created_at', $hoy)
                    ->where('estado', '!=', 'cancelado');
            })
            ->selectRaw('menu_id, SUM(cantidad) as vendidas')
            ->groupBy('menu_id')
            ->get()
            ->mapWithKeys(fn (DetallePedido $detalle): array => [
                $detalle->menu_id => (int) $detalle->vendidas,
            ]);

        $ordenMenus = ['universitario' => 0, 'ejecutivo' => 1, 'dieta' => 2];

        $menusDelDia = MenuDia::with(['opciones.componente'])
            ->get()
            ->sortBy(fn (MenuDia $menu): int => $ordenMenus[$menu->tipo_menu] ?? 99)
            ->values()
            ->map(function (MenuDia $menu) use ($ventasPorMenu): array {
                $fondos = $menu->opciones->filter(
                    fn (MenuDiaOpcion $opcion): bool => $opcion->habilitado && $opcion->componente?->tiempo === 'fondo'
                );

                $raciones = (int) $fondos->sum(fn (MenuDiaOpcion $opcion): int => max(0, (int) $opcion->stock_raciones));
                $vendidas = (int) ($ventasPorMenu->get($menu->id) ?? 0);
                $total = $raciones + $vendidas;

                $precio = $menu->precioVisible();

                return [
                    'id' => $menu->id,
                    'tipo' => $menu->tipo_menu,
                    'titulo' => $menu->titulo,
                    'etiqueta' => $menu->etiqueta ?: match ($menu->tipo_menu) {
                        'ejecutivo' => 'Especial',
                        'dieta' => 'Saludable',
                        default => 'Clásico',
                    },
                    'color' => match ($menu->tipo_menu) {
                        'ejecutivo' => 'coral',
                        'dieta' => 'cyan',
                        default => 'blue',
                    },
                    'activo' => $menu->activo,
                    'plato' => $fondos->first()?->componente->nombre ?? 'Plato principal',
                    'precioTexto' => ($menu->es_precio_variable ? 'Desde S/ ' : 'S/ ').number_format($precio, 2),
                    'raciones' => $raciones,
                    'vendidas' => $vendidas,
                    'total' => $total,
                    'porcentaje' => $total > 0 ? (int) round($raciones / $total * 100) : 0,
                ];
            })
            ->values();

        $menusActivos = $menusDelDia->where('activo', true)->count();

        $ultimosPedidos = Pedido::with(['cliente', 'detalles.producto', 'detalles.menu', 'detalles.fondo'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(4)
            ->get();

        $ultimosPedidos->each(function (Pedido $pedido): void {
            $detalle = $pedido->detalles
                ->map(function (DetallePedido $detalle): string {
                    if ($detalle->tipo_item === 'menu') {
                        $prefijo = $detalle->menu->titulo ?? 'Menú del día';
                        $fondo = $detalle->fondo?->nombre;

                        return $detalle->cantidad.'x '.$prefijo.($fondo ? ': '.$fondo : '');
                    }

                    return $detalle->cantidad.'x '.($detalle->producto->nombre ?? 'Producto');
                })
                ->filter()
                ->implode(', ');

            $pedido->setAttribute('detalle_rapido', $detalle !== '' ? $detalle : 'Consulta en caja');

            $rolCliente = $pedido->cliente?->rol;
            $etiquetaRol = match ($rolCliente) {
                'administrador' => 'Administrador',
                'empleado' => 'Empleado',
                'cliente' => 'Estudiante',
                default => 'Mostrador',
            };
            $nombreCliente = $pedido->cliente?->name;

            $pedido->setAttribute(
                'tipo_cliente',
                $nombreCliente ? $nombreCliente.' · '.$etiquetaRol : $etiquetaRol
            );
        });

        $fechaTurno = ucfirst(today()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));

        return view('admin.dashboard', [
            'ventasHoy' => $ventasHoy,
            'pedidosHoy' => $pedidosHoy,
            'enPreparacion' => $enPreparacion,
            'totalProductos' => $totalProductos,
            'productosStockBajo' => $productosStockBajo,
            'racionesStockBajo' => $racionesStockBajo,
            'alertasResumen' => $alertasResumen,
            'alertasStock' => $alertasStock,
            'ultimosMovimientos' => $ultimosMovimientos,
            'menusDelDia' => $menusDelDia,
            'menusActivos' => $menusActivos,
            'ultimosPedidos' => $ultimosPedidos,
            'fechaTurno' => $fechaTurno,
        ]);
    }
}
