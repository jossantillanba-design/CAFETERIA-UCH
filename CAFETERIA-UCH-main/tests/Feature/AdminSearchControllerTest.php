<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminSearchControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_search_redirects_to_the_matching_module(): void
    {
        $admin = User::factory()->administrador()->create();

        $producto = Producto::factory()->create(['sku' => 'SKU-BUSCAR-1']);
        $pedido = Pedido::create(['total' => 5, 'pagado' => true, 'estado' => 'entregado']);
        $cliente = User::factory()->cliente()->create(['name' => 'Buscador UCH']);

        $this->actingAs($admin)
            ->get('/admin/buscar?q='.$producto->sku)
            ->assertRedirect('/admin/productos?buscar='.$producto->sku);

        $this->actingAs($admin)
            ->get('/admin/buscar?q='.$pedido->id)
            ->assertRedirect('/admin/historial-pedidos?buscar='.$pedido->id);

        $this->actingAs($admin)
            ->get('/admin/buscar?q='.urlencode('Buscador'))
            ->assertRedirect('/admin/clientes?buscar=Buscador');

        $this->assertSame('cliente', $cliente->rol);
    }

    public function test_admin_search_redirects_an_active_order_to_live_orders(): void
    {
        $pedido = Pedido::create(['total' => 5, 'pagado' => true, 'estado' => 'pendiente']);

        $this->actingAs(User::factory()->administrador()->create())
            ->get('/admin/buscar?q='.$pedido->id)
            ->assertRedirect('/admin/pedidos?buscar='.$pedido->id);
    }
}
