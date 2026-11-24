<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class EmpleadoAccesoTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[TestWith(['/empleado/tomar-orden'])]
    #[TestWith(['/empleado/pedidos'])]
    #[TestWith(['/empleado/historial'])]
    public function test_guest_is_redirected_to_login_from_employee_modules(string $ruta): void
    {
        $this->get($ruta)->assertRedirect('/login');
    }

    public function test_employee_can_open_the_three_employee_modules(): void
    {
        $empleado = User::factory()->empleado()->create();

        $this->actingAs($empleado)->get('/empleado/tomar-orden')->assertOk();
        $this->actingAs($empleado)->get('/empleado/pedidos')->assertOk();
        $this->actingAs($empleado)->get('/empleado/historial')->assertOk();
    }

    #[TestWith(['/admin/dashboard'])]
    #[TestWith(['/admin/pedidos'])]
    #[TestWith(['/admin/historial-pedidos'])]
    #[TestWith(['/admin/productos'])]
    #[TestWith(['/admin/buscar'])]
    public function test_employee_is_forbidden_from_every_admin_module(string $ruta): void
    {
        $this->actingAs(User::factory()->empleado()->create())
            ->get($ruta)
            ->assertForbidden();
    }

    #[TestWith(['/empleado/tomar-orden'])]
    #[TestWith(['/empleado/pedidos'])]
    #[TestWith(['/empleado/historial'])]
    public function test_admin_is_forbidden_from_employee_modules(string $ruta): void
    {
        $this->actingAs(User::factory()->administrador()->create())
            ->get($ruta)
            ->assertForbidden();
    }

    #[TestWith(['/empleado/tomar-orden'])]
    #[TestWith(['/empleado/pedidos'])]
    #[TestWith(['/empleado/historial'])]
    public function test_client_is_forbidden_from_employee_modules(string $ruta): void
    {
        $this->actingAs(User::factory()->cliente()->create())
            ->get($ruta)
            ->assertForbidden();
    }

    #[TestWith(['administrador', '/admin/dashboard'])]
    #[TestWith(['empleado', '/empleado/tomar-orden'])]
    #[TestWith(['cliente', '/menu'])]
    public function test_root_redirects_to_the_landing_page_of_each_role(string $rol, string $destino): void
    {
        $usuario = User::factory()->{$rol}()->create();

        $this->actingAs($usuario)->get('/')->assertRedirect($destino);
    }

    public function test_employee_navigation_shows_only_the_three_operation_modules(): void
    {
        $this->actingAs(User::factory()->empleado()->create())->get('/empleado/pedidos')
            ->assertOk()
            ->assertSee('Comedor Operación')
            ->assertSee('Tomar orden')
            ->assertSee('Pedidos en vivo')
            ->assertSee('Historial de pedidos')
            ->assertSee('href="'.route('empleado.tomar-orden.create').'"', false)
            ->assertSee('href="'.route('empleado.pedidos.index').'"', false)
            ->assertSee('href="'.route('empleado.historial.index').'"', false)
            ->assertDontSee('Comedor Admin')
            ->assertDontSee('href="/admin/pedidos"', false)
            ->assertDontSee('Mis pedidos');
    }
}
