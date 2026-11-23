<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Bebidas', 'slug' => 'bebidas', 'descripcion' => 'Infusiones calientes, jugos naturales embotellados y agua.', 'color_hex' => '#00AEEF', 'orden' => 1],
            ['nombre' => 'Sánguches', 'slug' => 'sanguches', 'descripcion' => 'Panes artesanales, hamburguesas y empanadas al paso.', 'color_hex' => '#0066B3', 'orden' => 2],
            ['nombre' => 'Postres', 'slug' => 'postres', 'descripcion' => 'Tartaletas, brownies y dulces de la casa.', 'color_hex' => '#E63946', 'orden' => 3],
            ['nombre' => 'Menú del día', 'slug' => 'menu-del-dia', 'descripcion' => 'Almuerzos de tres tiempos listos para pedir.', 'color_hex' => '#00B4A6', 'orden' => 4],
        ];

        foreach ($categorias as $categoria) {
            Categoria::updateOrCreate(
                ['slug' => $categoria['slug']],
                [...$categoria, 'activo' => true],
            );
        }
    }
}
