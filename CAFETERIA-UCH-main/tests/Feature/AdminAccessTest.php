<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin_dashboard(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_client_cannot_open_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->cliente()->create())
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_employee_is_forbidden_from_every_admin_order_module(): void
    {
        $empleado = User::factory()->empleado()->create();

        $this->actingAs($empleado)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($empleado)->get('/admin/pedidos')->assertForbidden();
        $this->actingAs($empleado)->get('/admin/historial-pedidos')->assertForbidden();
    }

    public function test_client_cannot_open_order_history(): void
    {
        $this->actingAs(User::factory()->cliente()->create())
            ->get('/admin/historial-pedidos')
            ->assertForbidden();
    }

    public function test_admin_can_open_dashboard_and_orders(): void
    {
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin/pedidos')->assertOk();
        $this->actingAs($admin)->get('/admin/historial-pedidos')->assertOk();
        $this->actingAs($admin)->get('/admin/clientes')->assertOk();
    }

    public function test_admin_panel_uses_shared_sidebar_and_hides_customer_navigation(): void
    {
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Comedor Admin')
            ->assertSee('Inicio')
            ->assertSee('Pedidos en vivo')
            ->assertSee('Historial de pedidos')
            ->assertSee('href="/admin/historial-pedidos"', false)
            ->assertSee('Componentes del menú')
            ->assertSee('Historial de movimientos')
            ->assertSee('href="/admin/movimientos"', false)
            ->assertSee('Clientes')
            ->assertDontSee('href="/menu"', false)
            ->assertDontSee('href="/checkout"', false)
            ->assertDontSee('Mis pedidos');
    }

    public function test_employee_uses_the_operation_sidebar_without_admin_or_customer_navigation(): void
    {
        $this->actingAs(User::factory()->empleado()->create())->get('/empleado/pedidos')
            ->assertOk()
            ->assertSee('Comedor Operación')
            ->assertSee('Tomar orden')
            ->assertSee('Pedidos en vivo')
            ->assertSee('Historial de pedidos')
            ->assertSee('href="'.route('empleado.historial.index').'"', false)
            ->assertDontSee('Comedor Admin')
            ->assertDontSee('href="/admin/pedidos"', false)
            ->assertDontSee('href="/menu"', false)
            ->assertDontSee('href="/checkout"', false);
    }

    public function test_customer_menu_keeps_public_navigation(): void
    {
        $this->actingAs(User::factory()->cliente()->create())->get('/menu')
            ->assertOk()
            ->assertSee('href="/mis-pedidos"', false)
            ->assertSee('Mis pedidos')
            ->assertSee('Buscar en el menú')
            ->assertDontSee('href="/menu"', false);
    }

    public function test_customer_can_open_menu_checkout_and_order_history(): void
    {
        $cliente = User::factory()->cliente()->create();

        $this->actingAs($cliente)->get('/menu')->assertOk();
        $this->actingAs($cliente)->get('/checkout')->assertOk();
        $this->actingAs($cliente)->get('/mis-pedidos')->assertOk();
    }
}
