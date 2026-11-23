<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $categoriaPorSlug = Categoria::query()->pluck('id', 'slug');

        $productos = [
            ['nombre' => 'Café americano', 'descripcion' => 'Café negro recién preparado.', 'precio' => 3.50, 'imagen' => 'images/productos/cafe-americano.jpg', 'categoria' => 'bebidas', 'sku' => 'BEB-CAF-001', 'stock' => 40],
            ['nombre' => 'Pan con huevo revueto', 'descripcion' => 'Pan artesanal, huevo frito', 'precio' => 1.50, 'imagen' => 'images/productos/pan-con-huevo.jpg', 'categoria' => 'sanguches', 'sku' => 'SAN-HUE-001', 'stock' => 18],
            ['nombre' => 'Pan con pollo', 'descripcion' => 'Pan artesanal, pollo y verduras frescas.', 'precio' => 1.50, 'imagen' => 'images/productos/pan-con-pollo.jpg', 'categoria' => 'sanguches', 'sku' => 'SAN-POL-001', 'stock' => 24],
            ['nombre' => 'Pan con salchicha huachana', 'descripcion' => 'Horneada, con queso derretido.', 'precio' => 2.00, 'imagen' => 'images/productos/pan-con-salchicha-huachana.jpg', 'categoria' => 'sanguches', 'sku' => 'SAN-SAL-001', 'stock' => 3],
            ['nombre' => 'Pan con queso', 'descripcion' => 'Horneada, con queso derretido.', 'precio' => 2.00, 'imagen' => 'images/productos/pan-con-queso.jpg', 'categoria' => 'sanguches', 'sku' => 'SAN-QUE-001', 'stock' => 20],
            ['nombre' => 'Quinua', 'descripcion' => 'Bebida caliente.', 'precio' => 3.00, 'imagen' => 'images/productos/quinua.svg', 'categoria' => 'bebidas', 'sku' => 'BEB-QUI-001', 'stock' => 30],
            ['nombre' => 'Empanada de queso', 'descripcion' => 'Horneada, con queso derretido.', 'precio' => 4.00, 'imagen' => 'images/productos/empanada-queso.svg', 'categoria' => 'menu-del-dia', 'sku' => 'MEN-EMP-001', 'stock' => 12],
            ['nombre' => 'Empanada de pollo', 'descripcion' => 'Horneada, con pollo.', 'precio' => 4.50, 'imagen' => 'images/productos/empanada-pollo.svg', 'categoria' => 'menu-del-dia', 'sku' => 'MEN-EMP-002', 'stock' => 15],
            ['nombre' => 'Empanada de carne', 'descripcion' => 'Horneada, con carne.', 'precio' => 5.50, 'imagen' => 'images/productos/empanada-carne.svg', 'categoria' => 'menu-del-dia', 'sku' => 'MEN-EMP-003', 'stock' => 8],
            ['nombre' => 'Brownie', 'descripcion' => 'Chocolate intenso.', 'precio' => 3.50, 'imagen' => 'images/productos/brownie.svg', 'categoria' => 'postres', 'sku' => 'POS-BRO-001', 'stock' => 16],
        ];

        foreach ($productos as $producto) {
            Producto::updateOrCreate(
                ['nombre' => $producto['nombre']],
                [
                    'nombre' => $producto['nombre'],
                    'descripcion' => $producto['descripcion'],
                    'precio' => $producto['precio'],
                    'imagen' => $producto['imagen'],
                    'categoria_id' => $categoriaPorSlug[$producto['categoria']] ?? null,
                    'stock_actual' => $producto['stock'],
                    'stock_minimo' => 5,
                    'activo' => true,
                    'destacado' => in_array(Str::slug($producto['nombre']), ['pan-con-pollo', 'empanada-de-carne'], true),
                    'es_popular' => in_array(Str::slug($producto['nombre']), ['pan-con-pollo', 'empanada-de-carne', 'brownie'], true),
                ],
            );
        }
    }
}
