<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\MenuComponente;
use App\Models\MenuDiaOpcion;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MenuComponenteControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_components(): void
    {
        $this->get('/admin/componentes')->assertRedirect('/login');
    }

    public function test_employee_is_forbidden_from_opening_components(): void
    {
        $this->actingAs(User::factory()->empleado()->create())
            ->get('/admin/componentes')
            ->assertForbidden();
    }

    public function test_admin_lists_components_with_separate_create_link(): void
    {
        $entrada = MenuComponente::factory()->entrada()->create(['nombre' => 'Sopa criolla']);
        $fondo = MenuComponente::factory()->fondo()->create(['nombre' => 'Pollo a la brasa']);

        $this->actingAs(User::factory()->administrador()->create())
            ->get('/admin/componentes')
            ->assertOk()
            ->assertSee('Componentes del menú')
            ->assertSee('Nuevo componente')
            ->assertSee($entrada->nombre)
            ->assertSee($fondo->nombre);
    }

    public function test_admin_filters_components_by_time_and_status(): void
    {
        MenuComponente::factory()->entrada()->create(['nombre' => 'Sopa criolla']);
        $inactivo = MenuComponente::factory()->fondo()->create([
            'nombre' => 'Pollo a la brasa',
            'activo_catalogo' => false,
        ]);

        $this->actingAs(User::factory()->administrador()->create())
            ->get('/admin/componentes?tiempo=fondo&estado=inactivo')
            ->assertOk()
            ->assertSee($inactivo->nombre)
            ->assertDontSee('Sopa criolla');
    }

    public function test_admin_searches_components_by_name(): void
    {
        MenuComponente::factory()->entrada()->create(['nombre' => 'Sopa de maní']);
        MenuComponente::factory()->fondo()->create(['nombre' => 'Lomo saltado']);

        $this->actingAs(User::factory()->administrador()->create())
            ->get('/admin/componentes?buscar=Sopa')
            ->assertOk()
            ->assertSee('Sopa de maní')
            ->assertDontSee('Lomo saltado');
    }

    public function test_admin_opens_the_create_form(): void
    {
        $this->actingAs(User::factory()->administrador()->create())
            ->get('/admin/componentes/crear')
            ->assertOk()
            ->assertSee('Nuevo componente')
            ->assertSee('Nombre del componente')
            ->assertSee('Tiempo del menú');
    }

    public function test_admin_creates_a_component(): void
    {
        $response = $this->actingAs(User::factory()->administrador()->create())
            ->post('/admin/componentes', [
                'nombre' => 'Ensalada UCH',
                'descripcion' => 'Lechuga, tomate y quinua',
                'tiempo' => 'entrada',
                'activo_catalogo' => 1,
            ]);

        $response->assertRedirect('/admin/componentes');

        $this->assertDatabaseHas('menu_componentes', [
            'nombre' => 'Ensalada UCH',
            'descripcion' => 'Lechuga, tomate y quinua',
            'tiempo' => 'entrada',
            'activo_catalogo' => true,
        ]);
    }

    public function test_admin_rejects_duplicate_name_in_the_same_time(): void
    {
        MenuComponente::factory()->entrada()->create(['nombre' => 'Sopa criolla']);

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->post('/admin/componentes', [
                'nombre' => 'Sopa criolla',
                'tiempo' => 'entrada',
            ]);

        $response->assertSessionHasErrors(['nombre' => 'Ese componente ya existe en el banco de opciones.']);

        $this->assertSame(1, MenuComponente::where('nombre', 'Sopa criolla')->count());
    }

    public function test_admin_can_reuse_a_name_in_a_different_time(): void
    {
        MenuComponente::factory()->entrada()->create(['nombre' => 'Jugo natural']);

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->post('/admin/componentes', [
                'nombre' => 'Jugo natural',
                'tiempo' => 'refresco',
            ]);

        $response->assertRedirect('/admin/componentes');

        $this->assertSame(2, MenuComponente::where('nombre', 'Jugo natural')->count());
    }

    public function test_admin_opens_the_edit_form_with_current_values(): void
    {
        $componente = MenuComponente::factory()->fondo()->create(['nombre' => 'Lomo saltado']);

        $this->actingAs(User::factory()->administrador()->create())
            ->get('/admin/componentes/'.$componente->id.'/editar')
            ->assertOk()
            ->assertSee('Editar componente')
            ->assertSee('value="Lomo saltado"', false);
    }

    public function test_admin_updates_a_component_and_removes_it_from_menus_when_deactivated(): void
    {
        $componente = MenuComponente::factory()->fondo()->create(['nombre' => 'Lomo saltado']);
        $opcion = MenuDiaOpcion::factory()->for($componente, 'componente')->create(['habilitado' => true]);

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->put('/admin/componentes/'.$componente->id, [
                'nombre' => 'Lomo saltado UCH',
                'descripcion' => 'Con papas fritas',
                'tiempo' => 'fondo',
                'activo_catalogo' => 0,
            ]);

        $response->assertRedirect('/admin/componentes');

        $this->assertDatabaseHas('menu_componentes', [
            'id' => $componente->id,
            'nombre' => 'Lomo saltado UCH',
            'descripcion' => 'Con papas fritas',
            'activo_catalogo' => false,
        ]);
        $this->assertFalse($opcion->fresh()->habilitado);
    }

    public function test_admin_deactivates_a_component_and_its_menu_options(): void
    {
        $componente = MenuComponente::factory()->refresco()->create();
        $opcion = MenuDiaOpcion::factory()->for($componente, 'componente')->create(['habilitado' => true]);

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->patch('/admin/componentes/'.$componente->id.'/toggle');

        $response->assertRedirect('/admin/componentes');

        $this->assertFalse($componente->fresh()->activo_catalogo);
        $this->assertFalse($opcion->fresh()->habilitado);
    }

    public function test_admin_reactivating_a_component_does_not_reenable_old_menu_options(): void
    {
        $componente = MenuComponente::factory()->refresco()->create(['activo_catalogo' => false]);
        $opcion = MenuDiaOpcion::factory()->for($componente, 'componente')->create(['habilitado' => false]);
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->patch('/admin/componentes/'.$componente->id.'/toggle')->assertRedirect();

        $this->assertTrue($componente->fresh()->activo_catalogo);
        $this->assertFalse($opcion->fresh()->habilitado);
    }
}
