<?php

namespace App\Services;

use App\Models\DetallePedido;
use App\Models\InventarioMovimiento;
use App\Models\MenuDia;
use App\Models\MenuDiaOpcion;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrdenCajaService
{
    /**
     * @param  array<int, array<string, mixed>>  $carrito
     */
    public function crearPedido(array $datos, array $carrito): Pedido
    {
        return DB::transaction(function () use ($datos, $carrito): Pedido {
            $preparado = $this->prepararLineas($carrito, true);
            $total = round((float) $preparado['total'], 2);
            $esEfectivo = $datos['metodo_pago'] === Pedido::METODO_EFECTIVO;
            $montoRecibido = $esEfectivo ? round((float) $datos['monto_recibido'], 2) : null;

            if ($esEfectivo && $montoRecibido < $total) {
                throw ValidationException::withMessages([
                    'monto_recibido' => 'El monto recibido debe ser mayor o igual al total del ticket.',
                ]);
            }

            $pedido = Pedido::create([
                'atendido_por' => auth()->id(),
                'origen' => Pedido::ORIGEN_CAJA,
                'cliente_nombre_caja' => $datos['cliente_nombre_caja'],
                'tipo_consumo' => $datos['tipo_consumo'],
                'estado' => 'pendiente',
                'total' => $total,
                'pagado' => true,
                'metodo_pago' => $datos['metodo_pago'],
                'monto_recibido' => $montoRecibido,
                'vuelto' => $esEfectivo ? round($montoRecibido - $total, 2) : null,
            ]);

            $pedido->update([
                'codigo_transaccion' => sprintf('CAJA-%s-%04d', today()->format('Ymd'), $pedido->id),
            ]);

            foreach ($preparado['lineas'] as $linea) {
                if ($linea['tipo'] === 'producto') {
                    $this->registrarProducto($linea, $pedido->id);
                } else {
                    $this->registrarMenu($linea, $pedido->id);
                }
            }

            return $pedido->refresh();
        });
    }

    public function anular(Pedido $pedido): void
    {
        DB::transaction(function () use ($pedido): void {
            $pedido->load('detalles');

            $productos = [];
            $opciones = [];

            foreach ($pedido->detalles as $detalle) {
                if ($detalle->tipo_item === 'producto' && $detalle->producto_id) {
                    $productos[$detalle->producto_id] = ($productos[$detalle->producto_id] ?? 0) + $detalle->cantidad;
                }

                if ($detalle->tipo_item !== 'menu' || ! $detalle->menu_id) {
                    continue;
                }

                foreach ([$detalle->entrada_id, $detalle->fondo_id, $detalle->refresco_id] as $idComponente) {
                    if (! $idComponente) {
                        continue;
                    }

                    $opcion = MenuDiaOpcion::where('menu_id', $detalle->menu_id)
                        ->where('componente_id', $idComponente)
                        ->first();

                    if ($opcion && $opcion->stock_raciones !== null) {
                        $opciones[$opcion->id] = ($opciones[$opcion->id] ?? 0) + $detalle->cantidad;
                    }
                }
            }

            foreach ($productos as $idProducto => $cantidad) {
                /** @var Producto $producto */
                $producto = Producto::whereKey($idProducto)->lockForUpdate()->firstOrFail();
                $stockAnterior = (int) $producto->stock_actual;
                $stockNuevo = $stockAnterior + $cantidad;

                $producto->update(['stock_actual' => $stockNuevo]);

                $this->registrarMovimiento(
                    InventarioMovimiento::TIPO_PRODUCTO,
                    $producto->id,
                    $cantidad,
                    $stockAnterior,
                    $stockNuevo,
                    InventarioMovimiento::OPERACION_INGRESO,
                    'Anulación de ticket',
                    'Pedido #'.$pedido->id,
                );
            }

            foreach ($opciones as $idOpcion => $cantidad) {
                /** @var MenuDiaOpcion $opcion */
                $opcion = MenuDiaOpcion::whereKey($idOpcion)->lockForUpdate()->firstOrFail();
                $stockAnterior = (int) $opcion->stock_raciones;
                $stockNuevo = $stockAnterior + $cantidad;

                $opcion->update(['stock_raciones' => $stockNuevo]);

                $this->registrarMovimiento(
                    InventarioMovimiento::TIPO_OPCION_MENU,
                    $opcion->id,
                    $cantidad,
                    $stockAnterior,
                    $stockNuevo,
                    InventarioMovimiento::OPERACION_INGRESO,
                    'Anulación de ticket',
                    'Pedido #'.$pedido->id,
                );
            }

            $pedido->update(['estado' => 'cancelado']);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $carrito
     * @return array{lineas: array<int, array<string, mixed>>, total: float}
     */
    private function prepararLineas(array $carrito, bool $bloquear): array
    {
        $lineas = [];
        $total = 0.0;
        $demandasProductos = [];
        $demandasOpciones = [];

        foreach ($carrito as $item) {
            $cantidad = (int) $item['cantidad'];

            if ($item['tipo'] === 'producto') {
                $consulta = Producto::whereKey((int) $item['id']);

                if ($bloquear) {
                    $consulta->lockForUpdate();
                }

                /** @var Producto $producto */
                $producto = $consulta->firstOrFail();

                if (! $producto->activo) {
                    throw ValidationException::withMessages([
                        'productos' => 'El producto '.$producto->nombre.' no está disponible.',
                    ]);
                }

                $precio = (float) $producto->precio;
                $total += $precio * $cantidad;
                $lineas[] = [
                    'tipo' => 'producto',
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                ];
                $demandasProductos[$producto->id] = ($demandasProductos[$producto->id] ?? 0) + $cantidad;

                continue;
            }

            $consultaMenu = MenuDia::whereKey((int) $item['menu_id']);

            if ($bloquear) {
                $consultaMenu->lockForUpdate();
            }

            /** @var MenuDia $menu */
            $menu = $consultaMenu->firstOrFail();

            if (! $menu->activo) {
                throw ValidationException::withMessages([
                    'productos' => 'El '.$menu->titulo.' no está disponible hoy.',
                ]);
            }

            $idsComponentes = [
                'entrada' => (int) $item['opciones']['entrada'],
                'fondo' => (int) $item['opciones']['fondo'],
                'refresco' => (int) $item['opciones']['refresco'],
            ];

            $consultaOpciones = MenuDiaOpcion::with('componente')
                ->where('menu_id', $menu->id)
                ->whereIn('componente_id', $idsComponentes);

            if ($bloquear) {
                $consultaOpciones->lockForUpdate();
            }

            $opciones = $consultaOpciones->get()->keyBy('componente_id');

            foreach ($idsComponentes as $tiempo => $idComponente) {
                /** @var MenuDiaOpcion|null $opcion */
                $opcion = $opciones->get($idComponente);

                if (! $opcion || ! $opcion->habilitado || $opcion->componente?->tiempo !== $tiempo) {
                    throw ValidationException::withMessages([
                        'productos' => 'Una opción del '.$menu->titulo.' ya no está disponible. Vuelve a armar el menú.',
                    ]);
                }
            }

            $fondo = $opciones->get($idsComponentes['fondo']);
            $precio = $menu->es_precio_variable
                ? (float) ($fondo?->precio_opcion ?? $menu->precio_base)
                : (float) $menu->precio_base;

            $detalle = collect($idsComponentes)
                ->map(fn (int $idComponente, string $tiempo): string => ucfirst($tiempo).': '.($opciones->get($idComponente)?->componente->nombre ?? 'Opción'))
                ->join(' · ');

            $total += $precio * $cantidad;
            $lineas[] = [
                'tipo' => 'menu',
                'menu' => $menu,
                'opciones' => $opciones,
                'ids_componentes' => $idsComponentes,
                'detalle' => $detalle,
                'cantidad' => $cantidad,
                'precio' => $precio,
            ];

            foreach ($opciones as $opcion) {
                if ($opcion->stock_raciones !== null) {
                    $demandasOpciones[$opcion->id] = ($demandasOpciones[$opcion->id] ?? 0) + $cantidad;
                }
            }
        }

        foreach ($demandasProductos as $idProducto => $cantidad) {
            $producto = collect($lineas)
                ->first(fn (array $linea): bool => $linea['tipo'] === 'producto' && $linea['producto']->id === $idProducto);

            if ($producto['producto']->stock_actual < $cantidad) {
                throw ValidationException::withMessages([
                    'productos' => 'Stock insuficiente para '.$producto['producto']->nombre.'.',
                ]);
            }
        }

        foreach ($demandasOpciones as $idOpcion => $cantidad) {
            $opcion = collect($lineas)
                ->flatMap(fn (array $linea): array => $linea['tipo'] === 'menu' ? $linea['opciones']->all() : [])
                ->first(fn (MenuDiaOpcion $item): bool => $item->id === $idOpcion);

            if ($opcion->stock_raciones < $cantidad) {
                throw ValidationException::withMessages([
                    'productos' => 'No quedan suficientes raciones de '.$opcion->componente->nombre.'.',
                ]);
            }
        }

        return ['lineas' => $lineas, 'total' => round($total, 2)];
    }

    /**
     * @param  array<string, mixed>  $linea
     */
    private function registrarProducto(array $linea, int $pedidoId): void
    {
        /** @var Producto $producto */
        $producto = $linea['producto'];
        $cantidad = (int) $linea['cantidad'];
        $stockAnterior = (int) $producto->stock_actual;
        $stockNuevo = $stockAnterior - $cantidad;

        $producto->update(['stock_actual' => $stockNuevo]);

        $this->registrarMovimiento(
            InventarioMovimiento::TIPO_PRODUCTO,
            $producto->id,
            $cantidad,
            $stockAnterior,
            $stockNuevo,
            InventarioMovimiento::OPERACION_VENTA,
            'Venta caja',
            'Ticket #'.$pedidoId,
        );

        DetallePedido::create([
            'pedido_id' => $pedidoId,
            'producto_id' => $producto->id,
            'tipo_item' => 'producto',
            'cantidad' => $cantidad,
            'precio_unitario' => $linea['precio'],
            'subtotal' => round($linea['precio'] * $cantidad, 2),
        ]);
    }

    /**
     * @param  array<string, mixed>  $linea
     */
    private function registrarMenu(array $linea, int $pedidoId): void
    {
        /** @var MenuDia $menu */
        $menu = $linea['menu'];
        $cantidad = (int) $linea['cantidad'];

        /** @var Collection<int, MenuDiaOpcion> $opciones */
        $opciones = $linea['opciones'];

        foreach ($opciones as $opcion) {
            if ($opcion->stock_raciones === null) {
                continue;
            }

            $stockAnterior = (int) $opcion->stock_raciones;
            $stockNuevo = $stockAnterior - $cantidad;

            $opcion->update(['stock_raciones' => $stockNuevo]);

            $this->registrarMovimiento(
                InventarioMovimiento::TIPO_OPCION_MENU,
                $opcion->id,
                $cantidad,
                $stockAnterior,
                $stockNuevo,
                InventarioMovimiento::OPERACION_VENTA,
                'Venta caja',
                'Ticket #'.$pedidoId.' · '.$menu->titulo,
            );
        }

        DetallePedido::create([
            'pedido_id' => $pedidoId,
            'tipo_item' => 'menu',
            'menu_id' => $menu->id,
            'entrada_id' => $linea['ids_componentes']['entrada'],
            'fondo_id' => $linea['ids_componentes']['fondo'],
            'refresco_id' => $linea['ids_componentes']['refresco'],
            'detalle_menu' => $linea['detalle'],
            'cantidad' => $cantidad,
            'precio_unitario' => $linea['precio'],
            'subtotal' => round($linea['precio'] * $cantidad, 2),
        ]);
    }

    private function registrarMovimiento(
        string $tipoItem,
        int $itemId,
        int $cantidad,
        int $stockAnterior,
        int $stockNuevo,
        string $operacion,
        string $motivoCategoria,
        string $comentario
    ): void {
        InventarioMovimiento::create([
            'tipo_item' => $tipoItem,
            'item_id' => $itemId,
            'operacion' => $operacion,
            'cantidad' => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $stockNuevo,
            'motivo_categoria' => $motivoCategoria,
            'comentario' => $comentario,
            'usuario_id' => auth()->id(),
        ]);
    }
}
