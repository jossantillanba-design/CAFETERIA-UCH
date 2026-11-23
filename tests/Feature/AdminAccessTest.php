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

    public function test_employee_can_open_orders_but_not_admin_dashboard(): void
    {
        $empleado = User::factory()->empleado()->create();

        $this->actingAs($empleado)->get('/admin/pedidos')->assertOk();
        $this->actingAs($empleado)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_can_open_dashboard_and_orders(): void
    {
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin/pedidos')->assertOk();
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
            ->assertSee('Componentes de menú')
            ->assertSee('Historial de movimientos')
            ->assertSee('Clientes')
            ->assertDontSee('href="/menu"', false)
            ->assertDontSee('href="/checkout"', false)
            ->assertDontSee('Mis pedidos');
    }

    public function test_employee_orders_page_uses_shared_sidebar_without_customer_navigation(): void
    {
        $this->actingAs(User::factory()->empleado()->create())->get('/admin/pedidos')
            ->assertOk()
            ->assertSee('Comedor Admin')
            ->assertSee('Pedidos en vivo')
            ->assertDontSee('href="/menu"', false)
            ->assertDontSee('href="/checkout"', false);
    }

    public function test_customer_menu_keeps_public_navigation(): void
    {
        $this->actingAs(User::factory()->cliente()->create())->get('/menu')
            ->assertOk()
            ->assertSee('href="/menu"', false)
            ->assertSee('Mis pedidos');
    }

    public function test_customer_can_open_menu_checkout_and_order_history(): void
    {
        $cliente = User::factory()->cliente()->create();

        $this->actingAs($cliente)->get('/menu')->assertOk();
        $this->actingAs($cliente)->get('/checkout')->assertOk();
        $this->actingAs($cliente)->get('/mis-pedidos')->assertOk();
    }
}
