<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Categoria;
use App\Models\InventarioMovimiento;
use App\Models\MenuComponente;
use App\Models\MenuDia;
use App\Models\MenuDiaOpcion;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class InventarioControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_inventory_index_exposes_unified_rows_and_metrics(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Bebidas', 'slug' => 'bebidas']);
        $producto = Producto::factory()->for($categoria, 'categoria')->create([
            'stock_actual' => 2,
            'stock_minimo' => 5,
        ]);
        $menu = MenuDia::factory()->create(['titulo' => 'Menú Universitario']);
        $componente = MenuComponente::factory()->fondo()->create(['nombre' => 'Lomo Saltado']);
        $opcion = MenuDiaOpcion::factory()
            ->for($menu, 'menu')
            ->for($componente, 'componente')
            ->conStock(3)
            ->create();

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->get('/admin/inventario');

        $response
            ->assertOk()
            ->assertSee('Inventario y Control de Stock')
            ->assertViewHas('inventario', fn ($filas) => collect($filas)
                ->contains(fn ($fila) => $fila['tipo'] === 'producto'
                    && $fila['item_id'] === $producto->id
                    && $fila['referencia'] === $producto->sku
                    && $fila['estado'] === 'bajo')
                && collect($filas)->contains(fn ($fila) => $fila['tipo'] === 'opcion_menu'
                    && $fila['item_id'] === $opcion->id
                    && $fila['nombre'] === 'Lomo Saltado'
                    && $fila['stock'] === 3))
            ->assertViewHas('alertasStock', 2)
            ->assertViewHas('racionesDisponibles', 3)
            ->assertViewMissing('movimientos')
            ->assertViewHas('totalMovimientos', 0)
            ->assertViewHas('motivos', fn ($motivos) => in_array('Reposición', $motivos, true));
    }

    public function test_guest_is_redirected_to_login_when_adjusting_inventory(): void
    {
        $response = $this->post('/admin/inventario', []);

        $response->assertRedirect('/login');
    }

    public function test_employee_is_forbidden_from_adjusting_inventory(): void
    {
        $response = $this->actingAs(User::factory()->empleado()->create())
            ->post('/admin/inventario', []);

        $response->assertForbidden();
    }

    public function test_admin_registers_inbound_adjustment_with_audit_movement(): void
    {
        $admin = User::factory()->administrador()->create();
        $producto = Producto::factory()->create(['stock_actual' => 10]);

        $response = $this->actingAs($admin)->post('/admin/inventario', [
            'tipo_item' => InventarioMovimiento::TIPO_PRODUCTO,
            'item_id' => $producto->id,
            'operacion' => InventarioMovimiento::OPERACION_INGRESO,
            'cantidad' => 5,
            'motivo_categoria' => 'Reposición',
            'comentario' => 'Reposición semanal de bebidas',
        ]);

        $response->assertRedirect('/admin/inventario');

        $this->assertSame(15, $producto->fresh()->stock_actual);
        $this->assertDatabaseHas('inventario_movimientos', [
            'tipo_item' => InventarioMovimiento::TIPO_PRODUCTO,
            'item_id' => $producto->id,
            'operacion' => InventarioMovimiento::OPERACION_INGRESO,
            'cantidad' => 5,
            'stock_anterior' => 10,
            'stock_nuevo' => 15,
            'motivo_categoria' => 'Reposición',
            'usuario_id' => $admin->id,
        ]);
    }

    public function test_admin_rejects_outbound_adjustment_above_available_stock(): void
    {
        $producto = Producto::factory()->create(['stock_actual' => 3]);

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->post('/admin/inventario', [
                'tipo_item' => InventarioMovimiento::TIPO_PRODUCTO,
                'item_id' => $producto->id,
                'operacion' => InventarioMovimiento::OPERACION_SALIDA,
                'cantidad' => 5,
                'motivo_categoria' => 'Merma / Dañado',
                'comentario' => 'Producto dañado en el traslado',
            ]);

        $response
            ->assertRedirect()
            ->assertSessionHasErrors([
                'cantidad' => 'No puedes restar más unidades de las disponibles en stock.',
            ]);

        $this->assertSame(3, $producto->fresh()->stock_actual);
        $this->assertDatabaseCount('inventario_movimientos', 0);
    }

    public function test_admin_adjusts_menu_option_rations(): void
    {
        $menu = MenuDia::factory()->create();
        $opcion = MenuDiaOpcion::factory()
            ->for($menu, 'menu')
            ->conStock(8)
            ->create();

        $response = $this->actingAs(User::factory()->administrador()->create())
            ->post('/admin/inventario', [
                'tipo_item' => InventarioMovimiento::TIPO_OPCION_MENU,
                'item_id' => $opcion->id,
                'operacion' => InventarioMovimiento::OPERACION_INGRESO,
                'cantidad' => 2,
                'motivo_categoria' => 'Reposición',
                'comentario' => 'Más raciones para el almuerzo',
            ]);

        $response->assertRedirect('/admin/inventario');

        $this->assertSame(10, $opcion->fresh()->stock_raciones);
        $this->assertDatabaseHas('inventario_movimientos', [
            'tipo_item' => InventarioMovimiento::TIPO_OPCION_MENU,
            'item_id' => $opcion->id,
            'operacion' => InventarioMovimiento::OPERACION_INGRESO,
            'stock_anterior' => 8,
            'stock_nuevo' => 10,
        ]);
    }
}
