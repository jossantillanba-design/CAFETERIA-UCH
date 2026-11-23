<?php

namespace App\Http\Requests;

use App\Models\Pedido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrdenCajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->rol === 'empleado';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $reglas = [
            'cliente_nombre_caja' => ['required', 'string', 'max:120'],
            'tipo_consumo' => ['required', Rule::in([Pedido::TIPO_CONSUMO_SALON, Pedido::TIPO_CONSUMO_LLEVAR])],
            'metodo_pago' => ['required', Rule::in([Pedido::METODO_EFECTIVO, Pedido::METODO_YAPE_PLIN, Pedido::METODO_TARJETA])],
            'monto_recibido' => Rule::when(
                $this->input('metodo_pago') === Pedido::METODO_EFECTIVO,
                ['required', 'numeric', 'min:0'],
                ['nullable', 'numeric', 'min:0'],
            ),
            'productos' => ['required', 'array', 'min:1'],
        ];

        foreach ($this->input('productos', []) as $indice => $item) {
            if (! is_array($item)) {
                $reglas['productos.'.$indice] = ['array'];

                continue;
            }

            $reglas['productos.'.$indice.'.tipo'] = ['required', Rule::in(['producto', 'menu'])];
            $reglas['productos.'.$indice.'.cantidad'] = ['required', 'integer', 'min:1', 'max:50'];

            if (($item['tipo'] ?? null) === 'producto') {
                $reglas['productos.'.$indice.'.id'] = [
                    'required',
                    Rule::exists('productos', 'id')->where('activo', true),
                ];
            } elseif (($item['tipo'] ?? null) === 'menu') {
                $reglas['productos.'.$indice.'.menu_id'] = [
                    'required',
                    Rule::exists('menus_dia', 'id')->where('activo', true),
                ];
                $reglas['productos.'.$indice.'.opciones.entrada'] = ['required', 'integer', Rule::exists('menu_componentes', 'id')];
                $reglas['productos.'.$indice.'.opciones.fondo'] = ['required', 'integer', Rule::exists('menu_componentes', 'id')];
                $reglas['productos.'.$indice.'.opciones.refresco'] = ['required', 'integer', Rule::exists('menu_componentes', 'id')];
            }
        }

        return $reglas;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cliente_nombre_caja.required' => 'Ingresa el nombre del cliente para llamarlo en barra.',
            'cliente_nombre_caja.max' => 'El nombre del cliente no puede superar los 120 caracteres.',
            'tipo_consumo.required' => 'Elige la modalidad de consumo.',
            'metodo_pago.required' => 'Elige el método de pago.',
            'monto_recibido.required' => 'Ingresa el monto recibido en efectivo.',
            'monto_recibido.numeric' => 'El monto recibido debe ser un número válido.',
            'productos.required' => 'Agrega al menos un consumo al ticket.',
            'productos.min' => 'Agrega al menos un consumo al ticket.',
            'productos.*.cantidad.required' => 'Ingresa la cantidad de cada consumo.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.min' => 'La cantidad mínima es 1.',
            'productos.*.cantidad.max' => 'La cantidad máxima por consumo es 50.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('cliente_nombre_caja')) {
            $this->merge([
                'cliente_nombre_caja' => trim((string) $this->input('cliente_nombre_caja')),
            ]);
        }
    }
}
