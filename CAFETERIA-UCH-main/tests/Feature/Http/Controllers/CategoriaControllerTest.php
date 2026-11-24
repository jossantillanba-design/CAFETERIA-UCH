<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Categoria;
use App\Models\MenuDia;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CategoriaControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login_when_deleting_a_category(): void
    {
        $categoria = Categoria::factory()->create();

        $response = $this->delete('/admin/categorias/'.$categoria->id);

        $response->assertRedirect('/login');
        $this->assertModelExists($categoria);
    }

    public function test_employee_is_forbidden_from_deleting_a_category(): void
    {
        $categoria = Categoria::factory()->create();

        $response = $this->actingAs(User::factory()->empleado()->create())
            ->delete('/admin/categorias/'.$categoria->id);

        $response->assertForbidden();
        $this->assertModelExists($categoria);
    }

    public function test_admin_cannot_delete_category_with_active_products(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->for($categoria, 'categoria')->create();

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->delete('/admin/categorias/'.$categoria->id);

        $response
            ->assertRedirect()
            ->assertSessionHas('error', 'No puedes eliminar una categoría con productos activos. Desactívala o reasigna sus productos.');

        $this->assertModelExists($categoria);
    }

    public function test_admin_deletes_category_and_detaches_inactive_products(): void
    {
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->inactivo()->for($categoria, 'categoria')->create();

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->delete('/admin/categorias/'.$categoria->id);

        $response->assertRedirect('/admin/categorias');

        $this->assertModelMissing($categoria);
        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'categoria_id' => null,
        ]);
    }

    public function test_admin_sees_unified_structure_and_real_catalog_metrics(): void
    {
        MenuDia::factory()->create([
            'tipo_menu' => 'universitario',
            'titulo' => 'Menú Universitario',
            'descripcion' => 'Entrada, fondo y refresco subvencionado',
        ]);
        $categoria = Categoria::factory()->create([
            'nombre' => 'Cafés y Bebidas',
            'descripcion' => 'Infusiones calientes y jugos naturales',
            'orden' => 1,
            'activo' => true,
        ]);
        Categoria::factory()->inactiva()->create(['orden' => 0]);
        Producto::factory()->for($categoria, 'categoria')->create(['activo' => true]);

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->get('/admin/categorias');

        $response
            ->assertOk()
            ->assertViewIs('admin.categorias.index')
            ->assertSee('Estructura del Menú')
            ->assertSee('Menú Universitario')
            ->assertSee('Infusiones calientes y jugos naturales')
            ->assertSee('@click="abrirEditar(', false)
            ->assertDontSee("@click='abrirEditar", false)
            ->assertViewHas('estructura', function ($estructura) use ($categoria): bool {
                return $estructura->first()['es_menu'] === true
                    && $estructura->first()['color'] === '#0066B3'
                    && $estructura->last()['id'] === $categoria->id
                    && $estructura->last()['tipo'] === 'Productos';
            })
            ->assertViewHas('metricas', function (array $metricas): bool {
                return $metricas['catalogosMenu'] === 1
                    && $metricas['productosIndividuales'] === 1
                    && $metricas['visibilidad'] === 50
                    && $metricas['totalRegistros'] === 3;
            });
    }

    public function test_admin_creates_category_with_short_description(): void
    {
        $response = $this->actingAs(User::factory()->administrador()->create())
            ->post('/admin/categorias', [
                '_method' => 'POST',
                'nombre' => 'Platos a la Carta',
                'descripcion' => 'Proteínas premium y guarniciones al momento.',
                'color_hex' => '#F59E0B',
                'orden' => 7,
                'activo' => 1,
            ]);

        $response->assertRedirect('/admin/categorias');

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Platos a la Carta',
            'slug' => 'platos-a-la-carta',
            'descripcion' => 'Proteínas premium y guarniciones al momento.',
            'color_hex' => '#F59E0B',
            'orden' => 7,
            'activo' => true,
        ]);
    }

    public function test_admin_updates_category_description_and_color(): void
    {
        $categoria = Categoria::factory()->create();

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->put('/admin/categorias/'.$categoria->id, [
                'nombre' => $categoria->nombre,
                'slug' => $categoria->slug,
                'descripcion' => 'Nueva descripción para los tótems.',
                'color_hex' => '#00B4A6',
                'orden' => 3,
                'activo' => 0,
            ]);

        $response->assertRedirect('/admin/categorias');

        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'descripcion' => 'Nueva descripción para los tótems.',
            'color_hex' => '#00B4A6',
            'activo' => false,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_reordering_categories(): void
    {
        $response = $this->post('/admin/categorias/reordenar', ['ids' => [1, 2]]);

        $response->assertRedirect('/login');
    }

    public function test_employee_is_forbidden_from_reordering_categories(): void
    {
        $response = $this->actingAs(User::factory()->empleado()->create())
            ->post('/admin/categorias/reordenar', ['ids' => [1, 2]]);

        $response->assertForbidden();
    }

    public function test_admin_reorders_categories_and_persists_new_positions(): void
    {
        $primera = Categoria::factory()->create(['orden' => 1]);
        $segunda = Categoria::factory()->create(['orden' => 2]);

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->post('/admin/categorias/reordenar', ['ids' => [$segunda->id, $primera->id]]);

        $response
            ->assertRedirect('/admin/categorias')
            ->assertSessionHas('mensaje', 'Orden del catálogo actualizado correctamente.');

        $this->assertDatabaseHas('categorias', ['id' => $segunda->id, 'orden' => 1]);
        $this->assertDatabaseHas('categorias', ['id' => $primera->id, 'orden' => 2]);
    }

    public function test_reorder_rejects_unknown_category_ids_without_changing_order(): void
    {
        $categoria = Categoria::factory()->create(['orden' => 5]);

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->post('/admin/categorias/reordenar', ['ids' => [999999]]);

        $response
            ->assertRedirect()
            ->assertSessionHasErrors('ids.0');

        $this->assertDatabaseHas('categorias', ['id' => $categoria->id, 'orden' => 5]);
    }
}
