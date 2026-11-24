<?php

namespace Database\Factories;

use App\Models\MenuComponente;
use App\Models\MenuDia;
use App\Models\MenuDiaOpcion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuDiaOpcion>
 */
class MenuDiaOpcionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_id' => MenuDia::factory(),
            'componente_id' => MenuComponente::factory(),
            'precio_opcion' => null,
            'stock_raciones' => null,
            'habilitado' => true,
        ];
    }

    public function conStock(int $raciones, ?float $precio = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'stock_raciones' => $raciones,
            'precio_opcion' => $precio,
        ]);
    }

    public function sinStock(): static
    {
        return $this->state(fn (array $attributes): array => ['stock_raciones' => 0]);
    }

    public function deshabilitada(): static
    {
        return $this->state(fn (array $attributes): array => ['habilitado' => false]);
    }
}
