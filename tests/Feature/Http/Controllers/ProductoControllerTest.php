<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Categoria;
use App\Models\InventarioMovimiento;
use App\Models\MenuDia;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_menu_exposes_database_categories_stock_and_active_menu(): void
    {
        $categoria = Categoria::factory()->create([
            'nombre' => 'Bebidas',
            'slug' => 'bebidas',
            'color_hex' => '#0066B3',
        ]);
        $disponible = Producto::factory()->for($categoria, 'categoria')->create([
            'nombre' => 'Café con leche',
            'stock_actual' => 4,
        ]);
        $agotado = Producto::factory()->for($categoria, 'categoria')->sinStock()->create([
            'nombre' => 'Capuchino',
        ]);
        $menu = MenuDia::factory()->destacado()->create(['titulo' => 'Menú Universitario']);

        $response = $this->get('/menu');

        $response
            ->assertOk()
            ->assertViewHas('categoriasMenu', fn ($categorias) => collect($categorias)
                ->contains(fn ($categoria) => $categoria['nombre'] === 'Bebidas' && $categoria['slug'] === 'bebidas'))
            ->assertViewHas('productosMenu', fn ($productos) => collect($productos)
                ->contains(fn ($producto) => $producto['id'] === $disponible->id && $producto['stock'] === 4 && $producto['disponible'] === true)
                && collect($productos)->contains(fn ($producto) => $producto['id'] === $agotado->id && $producto['disponible'] === false))
            ->assertViewHas('menuHero', fn ($hero) => $hero['id'] === $menu->id && $hero['titulo'] === 'Menú Universitario')
            ->assertSee('Tu almuerzo UCH, listo sin hacer cola')
            ->assertSee('OFERTA DE HOY')
            ->assertSee('Menús armables de 3 tiempos')
            ->assertSee('Al paso y Combos')
            ->assertSee('FILTRAR POR')
            ->assertSee('ORDENAR POR')
            ->assertSee('Mostrando')
            ->assertDontSee('Menú semanal');
    }

    public function test_admin_can_create_product_with_initial_stock_and_movement(): void
    {
        $categoria = Categoria::factory()->create(['slug' => 'bebidas']);
        $admin = User::factory()->administrador()->create();

        $response = $this->actingAs($admin)->post('/admin/productos', [
            'categoria_id' => $categoria->id,
            'nombre' => 'Limonada',
            'sku' => 'BEB-LIM-001',
            'descripcion' => 'Limonada fresca',
            'precio' => 6.50,
            'stock_actual' => 12,
            'stock_minimo' => 4,
            'activo' => 1,
        ]);

        $response->assertRedirect('/admin/productos');

        $this->assertDatabaseHas('productos', [
            'sku' => 'BEB-LIM-001',
            'categoria_id' => $categoria->id,
            'stock_actual' => 12,
            'stock_minimo' => 4,
            'activo' => true,
        ]);
        $this->assertDatabaseHas('inventario_movimientos', [
            'operacion' => InventarioMovimiento::OPERACION_INGRESO,
            'cantidad' => 12,
            'stock_anterior' => 0,
            'stock_nuevo' => 12,
            'motivo_categoria' => 'Reposición',
            'usuario_id' => $admin->id,
        ]);
    }

    public function test_product_update_does_not_change_stock(): void
    {
        $producto = Producto::factory()->create([
            'nombre' => 'Nombre anterior',
            'stock_actual' => 10,
        ]);

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->put('/admin/productos/'.$producto->id, [
                'nombre' => 'Nombre actualizado',
                'precio' => 9.50,
                'stock_actual' => 999,
                'stock_minimo' => 2,
                'activo' => 1,
            ]);

        $response->assertRedirect('/admin/productos');

        $producto->refresh();
        $this->assertSame('Nombre actualizado', $producto->nombre);
        $this->assertSame(10, $producto->stock_actual);
    }

    public function test_admin_product_list_shows_catalog_metrics_and_paginated_results(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Bebidas', 'slug' => 'bebidas']);

        Producto::factory()->count(9)->for($categoria, 'categoria')->create([
            'stock_actual' => 10,
            'stock_minimo' => 3,
        ]);
        Producto::factory()->for($categoria, 'categoria')->create([
            'nombre' => 'Tartaleta de fresa',
            'sku' => 'POS-TAR-001',
            'stock_actual' => 2,
            'stock_minimo' => 3,
        ]);
        Producto::factory()->for($categoria, 'categoria')->sinStock()->create([
            'nombre' => 'Pan con salchicha huachana',
            'sku' => 'SAN-HUA-003',
        ]);
        Producto::factory()->for($categoria, 'categoria')->inactivo()->create([
            'nombre' => 'Producto pausado',
        ]);

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->get('/admin/productos');

        $response
            ->assertOk()
            ->assertViewHas('metricas', fn (array $metricas): bool => $metricas['total'] === 12
                && $metricas['categoriasActivas'] === 1
                && $metricas['disponibles'] === 9
                && $metricas['stockCritico'] === 1
                && $metricas['agotadosPausados'] === 2)
            ->assertViewHas('productos', fn ($productos): bool => $productos->total() === 12
                && $productos->count() === 8
                && $productos->lastPage() === 2)
            ->assertSee('Menús y Catálogo / Productos')
            ->assertSee('Total productos')
            ->assertSee('Disponibles')
            ->assertSee('Stock crítico')
            ->assertSee('Agotados / Pausados')
            ->assertSee('Mostrando')
            ->assertSee('12</span>', false);
    }

    public function test_admin_product_list_filters_by_search_category_and_stock_status(): void
    {
        $bebidas = Categoria::factory()->create(['nombre' => 'Bebidas', 'slug' => 'bebidas']);
        $postres = Categoria::factory()->create(['nombre' => 'Postres', 'slug' => 'postres']);
        $admin = User::factory()->administrador()->create();

        $cafe = Producto::factory()->for($bebidas, 'categoria')->create([
            'nombre' => 'Café americano',
            'sku' => 'BEB-CAF-001',
            'stock_actual' => 25,
            'stock_minimo' => 3,
            'destacado' => true,
            'es_nuevo' => true,
        ]);
        $tarta = Producto::factory()->for($postres, 'categoria')->create([
            'nombre' => 'Tartaleta de fresa',
            'sku' => 'POS-TAR-001',
            'stock_actual' => 2,
            'stock_minimo' => 3,
        ]);
        $huachana = Producto::factory()->for($bebidas, 'categoria')->sinStock()->create([
            'nombre' => 'Pan con salchicha huachana',
            'sku' => 'SAN-HUA-003',
        ]);
        $pausado = Producto::factory()->for($bebidas, 'categoria')->inactivo()->create([
            'nombre' => 'Limonada pausada',
        ]);

        $this->actingAs($admin)
            ->get('/admin/productos?buscar=BEB-CAF&categoria=bebidas&estado=disponible')
            ->assertOk()
            ->assertViewHas('productos', fn ($productos): bool => $productos->total() === 1
                && $productos->first()->is($cafe))
            ->assertSee('Café americano')
            ->assertSee('Nuevo')
            ->assertSee('Destacado')
            ->assertSee('value="BEB-CAF"', false);

        $this->actingAs($admin)
            ->get('/admin/productos?categoria=postres&estado=stock-bajo')
            ->assertOk()
            ->assertViewHas('productos', fn ($productos): bool => $productos->total() === 1
                && $productos->first()->is($tarta));

        $this->actingAs($admin)
            ->get('/admin/productos?estado=agotado')
            ->assertOk()
            ->assertViewHas('productos', fn ($productos): bool => $productos->total() === 1
                && $productos->first()->is($huachana));

        $this->actingAs($admin)
            ->get('/admin/productos?estado=inactivo')
            ->assertOk()
            ->assertViewHas('productos', fn ($productos): bool => $productos->total() === 1
                && $productos->first()->is($pausado));

        $this->actingAs($admin)
            ->get('/admin/productos?estado=invalido')
            ->assertOk()
            ->assertViewHas('productos', fn ($productos): bool => $productos->total() === 4);
    }

    public function test_create_form_renders_catalog_editor_with_real_controls(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Sánguches']);

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->get('/admin/productos/crear');

        $response
            ->assertOk()
            ->assertSee('Modo creación')
            ->assertSee('Imagen del producto')
            ->assertSee('Visibilidad y Etiquetas')
            ->assertSee('Información del producto')
            ->assertSee('Vista previa en vivo para el estudiante')
            ->assertSee('Guardar y publicar')
            ->assertSee('Sánguches')
            ->assertSee('name="imagen_archivo"', false)
            ->assertSee('name="alergenos"', false)
            ->assertSee('name="stock_actual"', false);
    }

    public function test_edit_form_renders_product_values_and_preserves_image_on_update(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Bebidas']);
        $producto = Producto::factory()->for($categoria, 'categoria')->create([
            'nombre' => 'Café especial',
            'imagen' => 'images/productos/cafe-americano.jpg',
            'alergenos' => 'Gluten, Huevo',
        ]);
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)
            ->get('/admin/productos/'.$producto->id.'/editar')
            ->assertOk()
            ->assertSee('Modo edición')
            ->assertSee('Café especial')
            ->assertSee('Gluten');

        $this->actingAs($admin)
            ->put('/admin/productos/'.$producto->id, [
                'nombre' => 'Café especial actualizado',
                'sku' => $producto->sku,
                'categoria_id' => $categoria->id,
                'precio' => 7.50,
                'stock_minimo' => 4,
                'activo' => 1,
            ])
            ->assertRedirect('/admin/productos');

        $producto->refresh();
        $this->assertSame('Café especial actualizado', $producto->nombre);
        $this->assertSame('images/productos/cafe-americano.jpg', $producto->imagen);
    }

    public function test_admin_can_upload_product_image_from_form(): void
    {
        $categoria = Categoria::factory()->create(['slug' => 'bebidas']);

        $this->actingAs(User::factory()->administrador()->create())
            ->post('/admin/productos', [
                'categoria_id' => $categoria->id,
                'nombre' => 'Limonada con imagen',
                'sku' => 'BEB-IMG-001',
                'precio' => 5.50,
                'stock_actual' => 8,
                'stock_minimo' => 3,
                'activo' => 1,
                'imagen_archivo' => UploadedFile::fake()->createWithContent(
                    'hamburguesa royal.jpg',
                    File::get(public_path('images/productos/cafe-americano.jpg'))
                ),
            ])
            ->assertRedirect('/admin/productos');

        $producto = Producto::where('sku', 'BEB-IMG-001')->firstOrFail();

        try {
            $this->assertStringStartsWith('images/productos/hamburguesa-royal-', $producto->imagen);
            $this->assertFileExists(public_path($producto->imagen));
        } finally {
            File::delete(public_path($producto->imagen));
        }
    }

    public function test_client_is_forbidden_from_admin_product_list(): void
    {
        $response = $this->actingAs(User::factory()->cliente()->create())
            ->get('/admin/productos');

        $response->assertForbidden();
    }
}
