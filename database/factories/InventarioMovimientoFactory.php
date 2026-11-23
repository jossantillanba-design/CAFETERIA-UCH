<?php

namespace Database\Factories;

use App\Models\InventarioMovimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventarioMovimiento>
 */
class InventarioMovimientoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo_item' => InventarioMovimiento::TIPO_PRODUCTO,
            'item_id' => fake()->numberBetween(1, 1000),
            'operacion' => InventarioMovimiento::OPERACION_INGRESO,
            'cantidad' => fake()->numberBetween(1, 20),
            'stock_anterior' => 0,
            'stock_nuevo' => 10,
            'motivo_categoria' => 'Reposición',
            'comentario' => fake()->sentence(),
            'usuario_id' => null,
        ];
    }

    public function venta(): static
    {
        return $this->state(fn (array $attributes): array => [
            'operacion' => InventarioMovimiento::OPERACION_VENTA,
            'motivo_categoria' => 'Venta web',
        ]);
    }

    public function opcionMenu(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tipo_item' => InventarioMovimiento::TIPO_OPCION_MENU,
        ]);
    }
}
