<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        Producto::insert([
            ['nombre' => 'Café americano', 'descripcion' => 'Café negro recién preparado.', 'precio' => 3.50, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pan con huevo revueto', 'descripcion' => 'Pan artesanal, huevo frito', 'precio' => 1.50, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pan con pollo', 'descripcion' => 'Pan artesanal, pollo y verduras frescas.', 'precio' => 1.50, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pan con salchicha huachana', 'descripcion' => 'Horneada, con queso derretido.', 'precio' => 2.00, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pan con queso', 'descripcion' => 'Horneada, con queso derretido.', 'precio' => 2.00, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Quinua', 'descripcion' => 'Bebida caliente.', 'precio' => 3.00, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Empanada de queso', 'descripcion' => 'Horneada, con queso derretido.', 'precio' => 4.00, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Empanada de pollo', 'descripcion' => 'Horneada, con pollo.', 'precio' => 4.50, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Empanada de carne', 'descripcion' => 'Horneada, con carne.', 'precio' => 5.50, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Brownie', 'descripcion' => 'Chocolate intenso.', 'precio' => 3.50, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}