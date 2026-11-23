<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\InventarioMovimiento;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PedidoController extends Controller
{
    // Lista de pedidos para el trabajador
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

        $consultaActivos = Pedido::with($relaciones)->whereIn('estado', $estadosActivos);
        $this->aplicarBusqueda($consultaActivos, $buscar);

        $pedidos = $consultaActivos
            ->when($estadoFiltro !== 'todos', fn (Builder $query): Builder => $query->where('estado', $estadoFiltro))
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $fechaHistorial = (string) $request->query('fecha', '');
        $fechaValida = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHistorial) === 1
            && checkdate((int) substr($fechaHistorial, 5, 2), (int) substr($fechaHistorial, 8, 2), (int) substr($fechaHistorial, 0, 4));

        if (! $fechaValida) {
            $fechaHistorial = today()->toDateString();
        }

        $historialBuscar = trim((string) $request->query('historial', ''));

        $consultaHistorial = Pedido::with($relaciones)
            ->whereIn('estado', ['entregado', 'cancelado'])
            ->whereDate('created_at', $fechaHistorial);

        $this->aplicarBusqueda($consultaHistorial, $historialBuscar !== '' ? $historialBuscar : $buscar);

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

        return view('admin.pedidos.index', [
            'pedidos' => $pedidos,
            'buscar' => $buscar,
            'estadoFiltro' => $estadoFiltro,
            'conteos' => $conteos,
            'historial' => $historial,
            'historialBuscar' => $historialBuscar,
            'fechaHistorial' => $fechaHistorial,
            'fechaTurno' => ucfirst(today()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')),
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

        $hora = (int) $pedido->created_at->format('H');
        $turno = match (true) {
            $hora < 6 => 'Madrugada / Guardia',
            $hora < 12 => 'Mañana',
            $hora < 19 => 'Tarde',
            default => 'Noche',
        };

        $indiceActual = array_search($pedido->estado, ['pendiente', 'en_preparacion', 'listo', 'entregado'], true);
        $indiceActual = $indiceActual === false ? -1 : $indiceActual;

        $etapas = [
            ['titulo' => 'Pedido recibido', 'detalle' => $pedido->atendidoPor
                ? 'Registrado desde ticket manual en mostrador'
                : 'Registrado desde portal web estudiantil UCH'],
            ['titulo' => 'En preparación en cocina', 'detalle' => 'Comanda enviada a estación caliente'],
            ['titulo' => 'Listo para recoger en barra', 'detalle' => 'Notificación enviada a pantalla de barra'],
            ['titulo' => 'Entregado al cliente', 'detalle' => 'Validado con ticket en módulo central'],
        ];

        $lineaTiempo = collect($etapas)->map(function (array $etapa, int $indice) use ($pedido, $indiceActual): array {
            $completada = $indice <= $indiceActual;
            $horaPaso = $indice === 0
                ? $pedido->created_at->format('h:i a')
                : ($completada && $indice === $indiceActual ? $pedido->updated_at->format('h:i a') : null);

            return [
                'titulo' => $horaPaso ? $horaPaso.' • '.$etapa['titulo'] : $etapa['titulo'],
                'detalle' => $etapa['detalle'],
                'completada' => $completada,
                'actual' => $indice === $indiceActual,
            ];
        })->all();

        $subtotal = round((float) $pedido->total / 1.18, 2);

        return view('admin.pedidos.show', [
            'pedido' => $pedido,
            'turno' => $turno,
            'lineaTiempo' => $lineaTiempo,
            'minutosDespacho' => (int) max(1, $pedido->created_at->diffInMinutes($pedido->updated_at)),
            'subtotal' => $subtotal,
            'igv' => round((float) $pedido->total - $subtotal, 2),
            'codigoOperacion' => $pedido->codigo_transaccion
                ?: 'UCH-'.str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT).'-'.$pedido->created_at->format('Ymd'),
        ]);
    }

    // Cambia el estado de un pedido (ej. de "pendiente" a "listo")
    public function actualizarEstado(Request $request, Pedido $pedido): RedirectResponse
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_preparacion,listo,entregado,cancelado',
        ]);

        $pedido->update(['estado' => $request->estado]);

        return redirect('/admin/pedidos')->with('mensaje', 'Estado actualizado.');
    }

    // Formulario para crear un ticket manual
    public function create(): View
    {
        $productos = Producto::where('activo', true)->where('stock_actual', '>', 0)->get();

        return view('admin.pedidos.create', ['productos' => $productos]);
    }

    // Guarda el ticket manual
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.id' => ['required', Rule::exists('productos', 'id')->where('activo', true)],
            'productos.*.cantidad' => 'required|integer|min:1|max:50',
        ]);

        $pedido = DB::transaction(function () use ($datos) {
            $pedido = Pedido::create([
                'atendido_por' => auth()->id(),
                'estado' => 'pendiente',
                'total' => 0,
            ]);

            $total = 0;

            foreach ($datos['productos'] as $item) {
                $producto = Producto::whereKey($item['id'])->lockForUpdate()->firstOrFail();
                $cantidad = (int) $item['cantidad'];

                if ($producto->stock_actual < $cantidad) {
                    throw ValidationException::withMessages([
                        'productos' => 'Stock insuficiente para '.$producto->nombre.'.',
                    ]);
                }

                $stockAnterior = (int) $producto->stock_actual;
                $stockNuevo = $stockAnterior - $cantidad;
                $producto->update(['stock_actual' => $stockNuevo]);

                InventarioMovimiento::create([
                    'tipo_item' => InventarioMovimiento::TIPO_PRODUCTO,
                    'item_id' => $producto->id,
                    'operacion' => InventarioMovimiento::OPERACION_VENTA,
                    'cantidad' => $cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'motivo_categoria' => 'Venta web',
                    'comentario' => 'Ticket manual #'.$pedido->id,
                    'usuario_id' => auth()->id(),
                ]);

                $subtotal = $producto->precio * $cantidad;
                $total += $subtotal;

                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'tipo_item' => 'producto',
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotal,
                ]);
            }

            $pedido->update(['total' => $total]);

            return $pedido;
        });

        return redirect('/admin/pedidos')->with('mensaje', 'Ticket #'.$pedido->id.' creado correctamente.');
    }

    public function marcarPagado(Pedido $pedido): RedirectResponse
    {
        if (! $pedido->pagado) {
            $pedido->update([
                'pagado' => true,
                'metodo_pago' => 'Efectivo (cobrado en caja)',
            ]);
        }

        return redirect('/admin/pedidos')->with('mensaje', 'Pago del pedido #'.$pedido->id.' registrado.');
    }
}
