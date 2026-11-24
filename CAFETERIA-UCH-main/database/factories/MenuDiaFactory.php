<?php

namespace Database\Factories;

use App\Models\MenuDia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuDia>
 */
class MenuDiaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo_menu' => fake()->unique()->randomElement(['universitario', 'ejecutivo', 'dieta']),
            'titulo' => ucfirst(fake()->unique()->words(2, true)),
            'descripcion' => fake()->sentence(),
            'precio_base' => 12.50,
            'es_precio_variable' => false,
            'fecha_vigencia' => null,
            'imagen' => null,
            'activo' => true,
            'destacado' => false,
            'etiqueta' => 'Menú del día',
        ];
    }

    public function variable(): static
    {
        return $this->state(fn (array $attributes): array => ['es_precio_variable' => true]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes): array => ['activo' => false]);
    }

    public function destacado(): static
    {
        return $this->state(fn (array $attributes): array => ['destacado' => true]);
    }
}
