<?php

namespace Tests\Feature;

use App\Models\DetallePedido;
use App\Models\InventarioMovimiento;
use App\Models\MenuComponente;
use App\Models\MenuDia;
use App\Models\MenuDiaOpcion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_sees_real_dashboard_metrics_and_modules(): void
    {
        $admin = User::factory()->administrador()->create();
        $cliente = User::factory()->cliente()->create(['name' => 'Ana Pérez']);

        $producto = Producto::factory()->create([
            'nombre' => 'Café de prueba',
            'sku' => 'CAF-001',
            'precio' => 7.5,
            'stock_actual' => 2,
            'stock_minimo' => 10,
        ]);

        $menu = MenuDia::factory()->create([
            'tipo_menu' => 'universitario',
            'titulo' => 'Menú Univ. Prueba',
            'precio_base' => 10,
            'es_precio_variable' => false,
            'activo' => true,
        ]);

        $componente = MenuComponente::factory()->fondo()->create(['nombre' => 'Lomo Saltado']);

        MenuDiaOpcion::factory()->conStock(8, 10)->create([
            'menu_id' => $menu->id,
            'componente_id' => $componente->id,
        ]);

        $pedido = Pedido::create([
            'cliente_id' => $cliente->id,
            'estado' => 'pendiente',
            'total' => 7.5,
            'pagado' => true,
            'metodo_pago' => 'Yape',
        ]);

        DetallePedido::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'tipo_item' => 'producto',
            'cantidad' => 1,
            'precio_unitario' => 7.5,
            'subtotal' => 7.5,
        ]);

        InventarioMovimiento::factory()->create([
            'tipo_item' => InventarioMovimiento::TIPO_PRODUCTO,
            'item_id' => $producto->id,
            'operacion' => InventarioMovimiento::OPERACION_INGRESO,
            'cantidad' => 5,
            'motivo_categoria' => 'Reposición',
            'comentario' => 'Reposición turno mañana',
            'usuario_id' => $admin->id,
        ]);

        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Resumen del comedor')
            ->assertSee('Panel administrativo')
            ->assertSee('Buscar pedidos, alumnos, productos o SKU...')
            ->assertSee('S/ 7.50')
            ->assertSee('Café de prueba')
            ->assertSee('Menú Univ. Prueba')
            ->assertSee('Lomo Saltado')
            ->assertSee('Ana Pérez')
            ->assertSee('Preparar')
            ->assertSee('Reposición')
            ->assertSee('Ver historial completo')
            ->assertSee('Gestionar inventario');
    }

    public function test_quick_action_marks_pending_order_as_in_preparation(): void
    {
        $pedido = Pedido::create([
            'estado' => 'pendiente',
            'total' => 10,
            'pagado' => true,
            'metodo_pago' => 'Yape',
        ]);

        $this->actingAs(User::factory()->administrador()->create())
            ->put('/admin/pedidos/'.$pedido->id.'/estado', ['estado' => 'en_preparacion'])
            ->assertRedirect('/admin/pedidos');

        $this->assertSame('en_preparacion', $pedido->fresh()->estado);
    }

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
            ->assertRedirect('/admin/pedidos?buscar='.$pedido->id);

        $this->actingAs($admin)
            ->get('/admin/buscar?q='.urlencode('Buscador'))
            ->assertRedirect('/admin/clientes?buscar=Buscador');

        $this->assertSame('cliente', $cliente->rol);
    }
}
