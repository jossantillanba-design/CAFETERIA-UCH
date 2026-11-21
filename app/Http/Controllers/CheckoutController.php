<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\PasarelaSimulada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            'productos.*.id' => ['required', Rule::exists('productos', 'id')->where('activo', true)],
            'productos.*.cantidad' => 'required|integer|min:1|max:50',

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

        // Los datos sensibles no se devuelven al formulario ni se guardan en la sesión
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('numero_tarjeta', 'cvv', 'codigo_billetera'));
        }

        // El total se calcula aquí con los precios de la base de datos
        $total = 0;
        $lineas = [];
        foreach ($request->productos as $item) {
            $producto = Producto::find($item['id']);
            $cantidad = (int) $item['cantidad'];
            $total += $producto->precio * $cantidad;
            $lineas[] = [$producto, $cantidad];
        }

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
                    ->with('error', 'Pago rechazado: ' . $resultado['mensaje']);
            }

            $pagado = true;
            $codigo = $resultado['codigo'];
            $detallePago = $resultado['referencia'] . ' (simulado)';
        }

        $pedido = DB::transaction(function () use ($request, $lineas, $total, $pagado, $codigo, $detallePago) {
            $pedido = Pedido::create([
                'cliente_id' => $request->user()->id,
                'estado' => 'pendiente',
                'total' => $total,
                'pagado' => $pagado,
                'metodo_pago' => $detallePago,
                'codigo_transaccion' => $codigo,
            ]);

            foreach ($lineas as [$producto, $cantidad]) {
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio,
                ]);
            }

            return $pedido;
        });

        $mensaje = $pagado
            ? 'Pago aprobado (' . $codigo . '). Tu pedido #' . $pedido->id . ' fue recibido.'
            : 'Pedido #' . $pedido->id . ' registrado. Paga S/' . number_format($total, 2) . ' en efectivo en caja al recogerlo.';

        return redirect('/mis-pedidos')->with('mensaje', $mensaje);
    }
}