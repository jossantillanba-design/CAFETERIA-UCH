<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->unique()->words(2, true);

        return [
            'nombre' => ucfirst($nombre),
            'slug' => Str::slug($nombre),
            'descripcion' => fake()->optional(0.7)->sentence(8),
            'color_hex' => fake()->randomElement(['#0066B3', '#00AEEF', '#E63946', '#00B4A6']),
            'orden' => fake()->numberBetween(0, 50),
            'activo' => true,
        ];
    }

    public function inactiva(): static
    {
        return $this->state(fn (array $attributes): array => ['activo' => false]);
    }
}
