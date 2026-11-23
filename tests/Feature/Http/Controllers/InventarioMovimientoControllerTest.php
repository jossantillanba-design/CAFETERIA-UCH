<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\InventarioMovimiento;
use App\Models\MenuComponente;
use App\Models\MenuDia;
use App\Models\MenuDiaOpcion;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class InventarioMovimientoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_and_employee_is_forbidden_when_opening_movements(): void
    {
        $this->get('/admin/movimientos')->assertRedirect('/login');

        $this->actingAs(User::factory()->empleado()->create())
            ->get('/admin/movimientos')
            ->assertForbidden();
    }

    public function test_admin_sees_resolved_item_names_and_kardex_metrics(): void
    {
        $admin = User::factory()->administrador()->create(['name' => 'Ana Administradora']);
        $producto = Producto::factory()->create(['nombre' => 'Café de prueba', 'sku' => 'CAF-001']);
        $menu = MenuDia::factory()->create(['titulo' => 'Menú Universitario']);
        $componente = MenuComponente::factory()->fondo()->create(['nombre' => 'Lomo Saltado']);
        $opcion = MenuDiaOpcion::factory()
            ->for($menu, 'menu')
            ->for($componente, 'componente')
            ->create();

        InventarioMovimiento::factory()->create([
            'tipo_item' => InventarioMovimiento::TIPO_PRODUCTO,
            'item_id' => $producto->id,
            'operacion' => InventarioMovimiento::OPERACION_INGRESO,
            'motivo_categoria' => 'Reposición',
            'comentario' => 'Reposición semanal de café',
            'usuario_id' => $admin->id,
        ]);
        InventarioMovimiento::factory()->venta()->create([
            'tipo_item' => InventarioMovimiento::TIPO_OPCION_MENU,
            'item_id' => $opcion->id,
            'cantidad' => 2,
            'stock_anterior' => 8,
            'stock_nuevo' => 6,
            'usuario_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/admin/movimientos')
            ->assertOk()
            ->assertSee('Historial de movimientos')
            ->assertSee('Café de prueba')
            ->assertSee('Lomo Saltado')
            ->assertSee('Reposición semanal de café')
            ->assertSee('Ana Administradora')
            ->assertViewHas('movimientos', fn ($movimientos) => $movimientos->total() === 2
                && $movimientos->contains(fn ($movimiento) => $movimiento->nombre_item === 'Café de prueba')
                && $movimientos->contains(fn ($movimiento) => $movimiento->nombre_item === 'Lomo Saltado'))
            ->assertViewHas('metricas', fn ($metricas) => $metricas['total'] === 2
                && $metricas['ingresos'] === 1
                && $metricas['salidas'] === 1);
    }

    public function test_admin_filters_movements_by_search_operation_motive_and_type(): void
    {
        $producto = Producto::factory()->create(['nombre' => 'Café de prueba']);
        $menu = MenuDia::factory()->create();
        $componente = MenuComponente::factory()->fondo()->create(['nombre' => 'Lomo Saltado']);
        $opcion = MenuDiaOpcion::factory()
            ->for($menu, 'menu')
            ->for($componente, 'componente')
            ->create();

        InventarioMovimiento::factory()->create([
            'tipo_item' => InventarioMovimiento::TIPO_PRODUCTO,
            'item_id' => $producto->id,
            'operacion' => InventarioMovimiento::OPERACION_INGRESO,
            'motivo_categoria' => 'Reposición',
            'comentario' => 'Ingreso de café',
        ]);
        InventarioMovimiento::factory()->venta()->create([
            'tipo_item' => InventarioMovimiento::TIPO_OPCION_MENU,
            'item_id' => $opcion->id,
        ]);
        InventarioMovimiento::factory()->create([
            'tipo_item' => InventarioMovimiento::TIPO_PRODUCTO,
            'item_id' => $producto->id + 100,
            'operacion' => InventarioMovimiento::OPERACION_SALIDA,
            'motivo_categoria' => 'Merma / Dañado',
            'comentario' => 'Producto dañado en el traslado',
        ]);

        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)
            ->get('/admin/movimientos?'.http_build_query(['buscar' => 'Lomo']))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movimientos) => $movimientos->total() === 1
                && $movimientos->first()->nombre_item === 'Lomo Saltado')
            ->assertSee('Lomo Saltado')
            ->assertDontSee('Ingreso de café');

        $this->actingAs($admin)
            ->get('/admin/movimientos?'.http_build_query(['operacion' => InventarioMovimiento::OPERACION_VENTA]))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movimientos) => $movimientos->total() === 1
                && $movimientos->first()->tipo_item === InventarioMovimiento::TIPO_OPCION_MENU);

        $this->actingAs($admin)
            ->get('/admin/movimientos?'.http_build_query(['motivo' => 'Merma / Dañado']))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movimientos) => $movimientos->total() === 1
                && $movimientos->first()->motivo_categoria === 'Merma / Dañado');

        $this->actingAs($admin)
            ->get('/admin/movimientos?'.http_build_query(['tipo' => InventarioMovimiento::TIPO_OPCION_MENU]))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movimientos) => $movimientos->total() === 1
                && $movimientos->first()->nombre_item === 'Lomo Saltado');
    }

    public function test_inventory_index_no_longer_embeds_the_movement_history(): void
    {
        $producto = Producto::factory()->create(['stock_actual' => 10]);
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->post('/admin/inventario', [
            'tipo_item' => InventarioMovimiento::TIPO_PRODUCTO,
            'item_id' => $producto->id,
            'operacion' => InventarioMovimiento::OPERACION_INGRESO,
            'cantidad' => 2,
            'motivo_categoria' => 'Reposición',
            'comentario' => 'Reposición de prueba',
        ])->assertRedirect('/admin/inventario');

        $this->actingAs($admin)
            ->get('/admin/inventario')
            ->assertOk()
            ->assertViewMissing('movimientos')
            ->assertViewHas('totalMovimientos', 1)
            ->assertDontSee('id="historial"', false)
            ->assertSee('Ver historial')
            ->assertSee('href="/admin/movimientos"', false);

        $this->actingAs($admin)
            ->get('/admin/movimientos')
            ->assertOk()
            ->assertSee('Reposición de prueba');
    }
}
