<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\InventarioMovimiento;
use App\Models\MenuDia;
use App\Models\MenuDiaOpcion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\PasarelaSimulada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout');
    }

    public function procesar(Request $request, PasarelaSimulada $pasarela)
    {
        $validator = Validator::make($request->all(), [
            'metodo' => 'required|in:efectivo,tarjeta,billetera',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|max:120',
            'productos.*.tipo' => 'required|in:producto,menu',
            'productos.*.cantidad' => 'required|integer|min:1|max:50',
            'productos.*.detalle' => 'nullable|string|max:300',

            'numero_tarjeta' => ['required_if:metodo,tarjeta', 'nullable', 'regex:/^[\d\s]{13,23}$/'],
            'nombre_tarjeta' => ['required_if:metodo,tarjeta', 'nullable', 'string', 'max:100'],
            'vencimiento' => ['required_if:metodo,tarjeta', 'nullable', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'cvv' => ['required_if:metodo,tarjeta', 'nullable', 'digits_between:3,4'],

            'billetera' => ['required_if:metodo,billetera', 'nullable', 'in:yape,plin'],
            'celular' => ['required_if:metodo,billetera', 'nullable', 'regex:/^9\d{2}[\s-]?\d{3}[\s-]?\d{3}$/'],
            'codigo_billetera' => ['required_if:metodo,billetera', 'nullable', 'digits:6'],
        ], [
            'metodo.required' => 'Elige un método de pago.',
            'metodo.in' => 'Método de pago no válido.',
            'numero_tarjeta.required_if' => 'Ingresa el número de tarjeta.',
            'numero_tarjeta.regex' => 'Ingresa un número de tarjeta válido.',
            'nombre_tarjeta.required_if' => 'Ingresa el nombre que aparece en la tarjeta.',
            'vencimiento.required_if' => 'Ingresa la fecha de vencimiento.',
            'vencimiento.regex' => 'El vencimiento debe tener el formato MM/AA.',
            'cvv.required_if' => 'Ingresa el CVV.',
            'cvv.digits_between' => 'El CVV debe tener 3 o 4 dígitos.',
            'billetera.required_if' => 'Elige Yape o Plin.',
            'celular.required_if' => 'Ingresa tu número de celular.',
            'celular.regex' => 'El celular debe tener 9 dígitos y empezar con 9.',
            'codigo_billetera.required_if' => 'Ingresa el código de aprobación.',
            'codigo_billetera.digits' => 'El código debe tener 6 dígitos.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('numero_tarjeta', 'cvv', 'codigo_billetera'));
        }

        $carrito = [];

        foreach ($request->input('productos', []) as $item) {
            $reglas = [
                'id' => ['required', 'string', 'max:120'],
                'tipo' => ['required', 'in:producto,menu'],
                'cantidad' => ['required', 'integer', 'min:1', 'max:50'],
            ];

            if (($item['tipo'] ?? null) === 'producto') {
                $reglas['id'] = ['required', Rule::exists('productos', 'id')->where('activo', true)];
            } else {
                $reglas['tipo'] = 'required|in:menu';
                $reglas['menu_id'] = ['required', 'integer', Rule::exists('menus_dia', 'id')->where('activo', true)];
                $reglas['opciones.entrada'] = ['required', 'integer', Rule::exists('menu_componentes', 'id')];
                $reglas['opciones.fondo'] = ['required', 'integer', Rule::exists('menu_componentes', 'id')];
                $reglas['opciones.refresco'] = ['required', 'integer', Rule::exists('menu_componentes', 'id')];
            }

            $validadorItem = Validator::make($item, $reglas);

            if ($validadorItem->fails()) {
                return back()
                    ->withErrors($validadorItem)
                    ->withInput($request->except('numero_tarjeta', 'cvv', 'codigo_billetera'));
            }

            $carrito[] = $validadorItem->validated();
        }

        // Validamos stock antes de cobrar para no procesar un pago que no se puede entregar.
        $this->prepararLineas($carrito, false);

        $metodo = $request->metodo;
        $pagado = false;
        $codigo = null;
        $detallePago = 'Efectivo (pago en caja)';

        if ($metodo !== 'efectivo') {
            $resultado = $metodo === 'tarjeta'
                ? $pasarela->cobrarTarjeta($request->numero_tarjeta, $request->vencimiento)
                : $pasarela->cobrarBilletera($request->billetera, $request->celular, $request->codigo_billetera);

            if (! $resultado['aprobado']) {
                return back()
                    ->withInput($request->only('metodo', 'nombre_tarjeta', 'billetera', 'celular'))
                    ->with('error', 'Pago rechazado: '.$resultado['mensaje']);
            }

            $pagado = true;
            $codigo = $resultado['codigo'];
            $detallePago = $resultado['referencia'].' (simulado)';
        }

        $pedido = DB::transaction(function () use ($request, $carrito, $pagado, $codigo, $detallePago) {
            $preparado = $this->prepararLineas($carrito, true);

            $pedido = Pedido::create([
                'cliente_id' => $request->user()->id,
                'estado' => 'pendiente',
                'total' => $preparado['total'],
                'pagado' => $pagado,
                'metodo_pago' => $detallePago,
                'codigo_transaccion' => $codigo,
            ]);

            foreach ($preparado['lineas'] as $linea) {
                if ($linea['tipo'] === 'producto') {
                    $this->registrarProducto($linea, $pedido->id);
                } else {
                    $this->registrarMenu($linea, $pedido->id);
                }
            }

            return $pedido;
        });

        $mensaje = $pagado
            ? 'Pago aprobado ('.$codigo.'). Tu pedido #'.$pedido->id.' fue recibido.'
            : 'Pedido #'.$pedido->id.' registrado. Paga S/'.number_format($pedido->total, 2).' en efectivo en caja al recogerlo.';

        return redirect('/mis-pedidos')->with('mensaje', $mensaje);
    }

    /**
     * @param  array<int, array<string, mixed>>  $carrito
     * @return array{lineas: array<int, array<string, mixed>>, total: float}
     */
    private function prepararLineas(array $carrito, bool $bloquear): array
    {
        $lineas = [];
        $total = 0;

        foreach ($carrito as $item) {
            $cantidad = (int) $item['cantidad'];

            if ($item['tipo'] === 'producto') {
                $consulta = Producto::whereKey((int) $item['id']);

                if ($bloquear) {
                    $consulta->lockForUpdate();
                }

                $producto = $consulta->firstOrFail();

                if (! $producto->activo || $producto->stock_actual < $cantidad) {
                    throw ValidationException::withMessages([
                        'productos' => 'Stock insuficiente para '.$producto->nombre.'.',
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

                continue;
            }

            $consultaMenu = MenuDia::whereKey((int) $item['menu_id']);

            if ($bloquear) {
                $consultaMenu->lockForUpdate();
            }

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
                $opcion = $opciones->get($idComponente);

                if (! $opcion || ! $opcion->habilitado || $opcion->componente?->tiempo !== $tiempo) {
                    throw ValidationException::withMessages([
                        'productos' => 'Una opción del '.$menu->titulo.' ya no está disponible. Vuelve a armar tu menú.',
                    ]);
                }

                if ($opcion->stock_raciones !== null && $opcion->stock_raciones < $cantidad) {
                    throw ValidationException::withMessages([
                        'productos' => 'No quedan suficientes raciones de '.$opcion->componente->nombre.'.',
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
        }

        return ['lineas' => $lineas, 'total' => $total];
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
            'Pedido #'.$pedidoId,
        );

        DetallePedido::create([
            'pedido_id' => $pedidoId,
            'producto_id' => $producto->id,
            'tipo_item' => 'producto',
            'cantidad' => $cantidad,
            'precio_unitario' => $linea['precio'],
            'subtotal' => $linea['precio'] * $cantidad,
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
        $idsComponentes = $linea['ids_componentes'];

        foreach ($linea['opciones'] as $opcion) {
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
                'Pedido #'.$pedidoId.' · '.$menu->titulo,
            );
        }

        DetallePedido::create([
            'pedido_id' => $pedidoId,
            'tipo_item' => 'menu',
            'menu_id' => $menu->id,
            'entrada_id' => $idsComponentes['entrada'],
            'fondo_id' => $idsComponentes['fondo'],
            'refresco_id' => $idsComponentes['refresco'],
            'detalle_menu' => $linea['detalle'],
            'cantidad' => $cantidad,
            'precio_unitario' => $linea['precio'],
            'subtotal' => $linea['precio'] * $cantidad,
        ]);
    }

    private function registrarMovimiento(
        string $tipoItem,
        int $itemId,
        int $cantidad,
        int $stockAnterior,
        int $stockNuevo,
        string $comentario
    ): void {
        InventarioMovimiento::create([
            'tipo_item' => $tipoItem,
            'item_id' => $itemId,
            'operacion' => InventarioMovimiento::OPERACION_VENTA,
            'cantidad' => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $stockNuevo,
            'motivo_categoria' => 'Venta web',
            'comentario' => $comentario,
            'usuario_id' => auth()->id(),
        ]);
    }
}
