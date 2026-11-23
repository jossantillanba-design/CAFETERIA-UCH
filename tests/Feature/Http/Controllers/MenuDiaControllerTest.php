<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\MenuComponente;
use App\Models\MenuDia;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MenuDiaControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login_when_updating_a_menu(): void
    {
        $menu = MenuDia::factory()->create();

        $response = $this->put('/admin/menus/'.$menu->id, []);

        $response->assertRedirect('/login');
    }

    public function test_employee_is_forbidden_from_updating_a_menu(): void
    {
        $menu = MenuDia::factory()->create();

        $response = $this->actingAs(User::factory()->empleado()->create())
            ->put('/admin/menus/'.$menu->id, []);

        $response->assertForbidden();
    }

    public function test_admin_saves_menu_configuration_and_component_options(): void
    {
        $menu = MenuDia::factory()->variable()->create();
        $entrada = MenuComponente::factory()->entrada()->create();
        $fondo = MenuComponente::factory()->fondo()->create();
        $refresco = MenuComponente::factory()->refresco()->create();

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->put('/admin/menus/'.$menu->id, [
                'titulo' => 'Almuerzo Ejecutivo UCH',
                'descripcion' => 'Tres tiempos con plato a elección',
                'precio_base' => 12,
                'etiqueta' => 'Ejecutivo',
                'activo' => 1,
                'destacado' => 1,
                'habilitados' => [$entrada->id, $fondo->id, $refresco->id],
                'stock_raciones' => [$fondo->id => 7],
                'precio_opcion' => [$fondo->id => 15.50],
            ]);

        $response->assertRedirect('/admin/menus');

        $this->assertDatabaseHas('menus_dia', [
            'id' => $menu->id,
            'titulo' => 'Almuerzo Ejecutivo UCH',
            'etiqueta' => 'Ejecutivo',
            'precio_base' => '12.00',
            'activo' => true,
            'destacado' => true,
        ]);
        $this->assertDatabaseHas('menu_dia_opciones', [
            'menu_id' => $menu->id,
            'componente_id' => $entrada->id,
            'habilitado' => true,
        ]);
        $this->assertDatabaseHas('menu_dia_opciones', [
            'menu_id' => $menu->id,
            'componente_id' => $fondo->id,
            'habilitado' => true,
            'stock_raciones' => 7,
            'precio_opcion' => '15.50',
        ]);
        $this->assertDatabaseHas('menu_dia_opciones', [
            'menu_id' => $menu->id,
            'componente_id' => $refresco->id,
            'habilitado' => true,
        ]);
    }

    public function test_admin_rejects_variable_menu_without_price_for_main_course(): void
    {
        $menu = MenuDia::factory()->variable()->create();
        $fondo = MenuComponente::factory()->fondo()->create();

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->put('/admin/menus/'.$menu->id, [
                'titulo' => 'Menú sin precio',
                'precio_base' => 10,
                'activo' => 1,
                'habilitados' => [$fondo->id],
                'stock_raciones' => [$fondo->id => 5],
            ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('error', 'El Menú Ejecutivo requiere un precio por cada plato principal habilitado.');

        $this->assertDatabaseMissing('menu_dia_opciones', [
            'menu_id' => $menu->id,
            'componente_id' => $fondo->id,
        ]);
    }

    public function test_admin_toggles_menu_availability(): void
    {
        $menu = MenuDia::factory()->create(['activo' => true]);

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->patch('/admin/menus/'.$menu->id.'/toggle');

        $response->assertRedirect('/admin/menus');

        $this->assertFalse($menu->fresh()->activo);
    }
}
