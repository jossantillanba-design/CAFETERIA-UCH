<?php

namespace Database\Seeders;

use App\Models\MenuComponente;
use App\Models\MenuDia;
use Illuminate\Database\Seeder;

class MenuCatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $componentes = [
            ['nombre' => 'Sopa criolla', 'tiempo' => 'entrada'],
            ['nombre' => 'Causa rellena de pollo', 'tiempo' => 'entrada'],
            ['nombre' => 'Ensalada fresca', 'tiempo' => 'entrada'],
            ['nombre' => 'Lomo saltado', 'tiempo' => 'fondo'],
            ['nombre' => 'Seco de pollo', 'tiempo' => 'fondo'],
            ['nombre' => 'Filete de pollo', 'tiempo' => 'fondo'],
            ['nombre' => 'Lomo fino saltado', 'tiempo' => 'fondo'],
            ['nombre' => 'Chicha morada', 'tiempo' => 'refresco'],
            ['nombre' => 'Maracuyá', 'tiempo' => 'refresco'],
            ['nombre' => 'Infusión', 'tiempo' => 'refresco'],
        ];

        foreach ($componentes as $componente) {
            MenuComponente::updateOrCreate(
                ['nombre' => $componente['nombre'], 'tiempo' => $componente['tiempo']],
                ['activo_catalogo' => true],
            );
        }

        $menus = [
            [
                'tipo_menu' => 'universitario',
                'titulo' => 'Menú Universitario',
                'descripcion' => 'Entrada, plato principal y refresco a precio fijo.',
                'precio_base' => 10,
                'es_precio_variable' => false,
                'etiqueta' => 'Más pedido',
                'destacado' => true,
            ],
            [
                'tipo_menu' => 'ejecutivo',
                'titulo' => 'Menú Ejecutivo',
                'descripcion' => 'Elige tu plato principal: el precio varía según tu selección.',
                'precio_base' => 13,
                'es_precio_variable' => true,
                'etiqueta' => 'Especial',
                'destacado' => false,
            ],
            [
                'tipo_menu' => 'dieta',
                'titulo' => 'Menú Dieta',
                'descripcion' => 'Opciones ligeras para cuidar tu alimentación.',
                'precio_base' => 11,
                'es_precio_variable' => false,
                'etiqueta' => 'Saludable',
                'destacado' => false,
            ],
        ];

        foreach ($menus as $menu) {
            MenuDia::updateOrCreate(
                ['tipo_menu' => $menu['tipo_menu']],
                [...$menu, 'activo' => true, 'fecha_vigencia' => today()],
            );
        }

        $preciosFondo = [
            'Filete de pollo' => 13,
            'Seco de pollo' => 14.5,
            'Lomo saltado' => 15.5,
            'Lomo fino saltado' => 16,
        ];

        $racionesFondo = [
            'Lomo saltado' => 30,
            'Seco de pollo' => 20,
            'Filete de pollo' => 25,
            'Lomo fino saltado' => 15,
        ];

        foreach (MenuDia::all() as $menu) {
            foreach (MenuComponente::where('activo_catalogo', true)->get() as $componente) {
                $esFondo = $componente->tiempo === 'fondo';

                $menu->opciones()->updateOrCreate(
                    ['componente_id' => $componente->id],
                    [
                        'precio_opcion' => $menu->es_precio_variable && $esFondo
                            ? ($preciosFondo[$componente->nombre] ?? $menu->precio_base)
                            : null,
                        'stock_raciones' => $esFondo ? ($racionesFondo[$componente->nombre] ?? 20) : null,
                        'habilitado' => true,
                    ],
                );
            }
        }
    }
}
