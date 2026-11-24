<?php

namespace App\Http\Controllers\Empleado;

use App\Http\Controllers\Controller;
use App\Models\DetallePedido;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class HistorialController extends Controller
{
    private const ESTADOS_EN_PROCESO = ['pendiente', 'en_preparacion', 'listo'];

    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $origenFiltro = (string) $request->query('origen', 'todos');
        $metodoFiltro = (string) $request->query('metodo_pago', 'todos');
        $estadoFiltro = (string) $request->query('estado', 'todos');

        if (! in_array($origenFiltro, ['todos', Pedido::ORIGEN_WEB, Pedido::ORIGEN_CAJA], true)) {
            $origenFiltro = 'todos';
        }

        if (! in_array($metodoFiltro, ['todos', Pedido::METODO_EFECTIVO, Pedido::METODO_YAPE_PLIN, Pedido::METODO_TARJETA], true)) {
            $metodoFiltro = 'todos';
        }

        if (! in_array($estadoFiltro, ['todos', 'proceso', 'entregado', 'cancelado'], true)) {
            $estadoFiltro = 'todos';
        }

        [$desde, $hasta] = $this->rangoDeFechas($request);

        $consultaRango = Pedido::query()
            ->whereBetween('created_at', [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()]);

        // Tabla: respeta todos los filtros.
        $consultaHistorial = (clone $consultaRango)
            ->with([
                'cliente',
                'detalles.producto',
                'detalles.menu',
                'detalles.entrada',
                'detalles.fondo',
                'detalles.refresco',
            ]);
        $this->aplicarFiltros($consultaHistorial, $origenFiltro, $metodoFiltro, $estadoFiltro);
        $this->aplicarBusqueda($consultaHistorial, $buscar);

        $historial = $consultaHistorial
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
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

        // Cuadre de caja: depende solo del rango de fechas, no de la búsqueda ni de los filtros de la tabla.
        $pedidosRango = (clone $consultaRango)->get(['id', 'estado', 'total', 'metodo_pago', 'created_at']);
        $ventas = $pedidosRango->where('estado', '!=', 'cancelado');
        $anulados = $pedidosRango->where('estado', 'cancelado');

        $sumarMetodo = fn (string $metodo): float => (float) $ventas->where('metodo_pago', $metodo)->sum('total');
        $contarMetodo = fn (string $metodo): int => $ventas->where('metodo_pago', $metodo)->count();

        $canonicos = [Pedido::METODO_EFECTIVO, Pedido::METODO_YAPE_PLIN, Pedido::METODO_TARJETA];

        $arqueo = [
            'efectivo' => $sumarMetodo(Pedido::METODO_EFECTIVO),
            'billeteras' => $sumarMetodo(Pedido::METODO_YAPE_PLIN),
            'tarjeta' => $sumarMetodo(Pedido::METODO_TARJETA),
            'otros' => (float) $ventas->whereNotIn('metodo_pago', $canonicos)->sum('total'),
            'efectivoTickets' => $contarMetodo(Pedido::METODO_EFECTIVO),
            'billeterasTickets' => $contarMetodo(Pedido::METODO_YAPE_PLIN),
            'tarjetaTickets' => $contarMetodo(Pedido::METODO_TARJETA),
            'total' => (float) $ventas->sum('total'),
            'tickets' => $ventas->count(),
            'anulados' => $anulados->count(),
            'anuladosMonto' => (float) $anulados->sum('total'),
            'sinEntregar' => $ventas->whereIn('estado', self::ESTADOS_EN_PROCESO)->count(),
        ];

        $porDia = $ventas
            ->groupBy(fn (Pedido $pedido): string => $pedido->created_at->toDateString())
            ->map(fn ($grupo, string $dia): array => [
                'fecha' => $dia,
                'tickets' => $grupo->count(),
                'total' => (float) $grupo->sum('total'),
            ])
            ->sortKeysDesc()
            ->values();

        return view('empleado.historial', [
            'historial' => $historial,
            'buscar' => $buscar,
            'desdeHistorial' => $desde->toDateString(),
            'hastaHistorial' => $hasta->toDateString(),
            'atajos' => $this->atajos($desde, $hasta),
            'origenFiltro' => $origenFiltro,
            'metodoFiltro' => $metodoFiltro,
            'estadoFiltro' => $estadoFiltro,
            'arqueo' => $arqueo,
            'porDia' => $porDia,
        ]);
    }

    /**
     * Acepta desde/hasta (y el antiguo parámetro "fecha"); por defecto, solo hoy.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function rangoDeFechas(Request $request): array
    {
        $fechaUnica = $this->fechaValida((string) $request->query('fecha', ''));

        $desde = $this->fechaValida((string) $request->query('desde', '')) ?? $fechaUnica ?? today();
        $hasta = $this->fechaValida((string) $request->query('hasta', '')) ?? $fechaUnica ?? $desde;

        if ($hasta->lt($desde)) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        return [$desde, $hasta];
    }

    private function fechaValida(string $valor): ?Carbon
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $partes) !== 1) {
            return null;
        }

        if (! checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1])) {
            return null;
        }

        return Carbon::create((int) $partes[1], (int) $partes[2], (int) $partes[3])->startOfDay();
    }

    /**
     * @return array<int, array{etiqueta: string, desde: string, hasta: string, activo: bool}>
     */
    private function atajos(Carbon $desde, Carbon $hasta): array
    {
        $hoy = today();

        $opciones = [
            ['etiqueta' => 'Hoy', 'desde' => $hoy->copy(), 'hasta' => $hoy->copy()],
            ['etiqueta' => 'Ayer', 'desde' => $hoy->copy()->subDay(), 'hasta' => $hoy->copy()->subDay()],
            ['etiqueta' => 'Últimos 7 días', 'desde' => $hoy->copy()->subDays(6), 'hasta' => $hoy->copy()],
            ['etiqueta' => 'Este mes', 'desde' => $hoy->copy()->startOfMonth(), 'hasta' => $hoy->copy()],
        ];

        return array_map(fn (array $opcion): array => [
            'etiqueta' => $opcion['etiqueta'],
            'desde' => $opcion['desde']->toDateString(),
            'hasta' => $opcion['hasta']->toDateString(),
            'activo' => $opcion['desde']->isSameDay($desde) && $opcion['hasta']->isSameDay($hasta),
        ], $opciones);
    }

    /**
     * @param  Builder<Pedido>  $query
     */
    private function aplicarFiltros(Builder $query, string $origenFiltro, string $metodoFiltro, string $estadoFiltro): void
    {
        $query->when($origenFiltro !== 'todos', fn (Builder $query): Builder => $query->where('origen', $origenFiltro))
            ->when($metodoFiltro !== 'todos', fn (Builder $query): Builder => $query->where('metodo_pago', $metodoFiltro))
            ->when($estadoFiltro === 'proceso', fn (Builder $query): Builder => $query->whereIn('estado', self::ESTADOS_EN_PROCESO))
            ->when(in_array($estadoFiltro, ['entregado', 'cancelado'], true), fn (Builder $query): Builder => $query->where('estado', $estadoFiltro));
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
