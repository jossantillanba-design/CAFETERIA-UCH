<?php

namespace Database\Factories;

use App\Models\MenuComponente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuComponente>
 */
class MenuComponenteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => ucfirst(fake()->unique()->words(2, true)),
            'descripcion' => fake()->sentence(),
            'tiempo' => 'entrada',
            'activo_catalogo' => true,
        ];
    }

    public function entrada(): static
    {
        return $this->state(fn (array $attributes): array => ['tiempo' => 'entrada']);
    }

    public function fondo(): static
    {
        return $this->state(fn (array $attributes): array => ['tiempo' => 'fondo']);
    }

    public function refresco(): static
    {
        return $this->state(fn (array $attributes): array => ['tiempo' => 'refresco']);
    }
}
