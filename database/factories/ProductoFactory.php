<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(),
            'nombre' => ucfirst(fake()->unique()->words(2, true)),
            'sku' => 'SKU-'.fake()->unique()->numberBetween(1000, 999999),
            'descripcion' => fake()->sentence(),
            'alergenos' => null,
            'precio' => fake()->randomFloat(2, 3, 25),
            'stock_actual' => fake()->numberBetween(5, 40),
            'stock_minimo' => 3,
            'imagen' => null,
            'activo' => true,
            'destacado' => false,
            'es_nuevo' => false,
            'es_popular' => false,
        ];
    }

    public function sinStock(): static
    {
        return $this->state(fn (array $attributes): array => ['stock_actual' => 0]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes): array => ['activo' => false]);
    }
}
