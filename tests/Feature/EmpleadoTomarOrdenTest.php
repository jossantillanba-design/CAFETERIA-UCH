<?php

namespace Tests\Feature;

use App\Models\MenuComponente;
use App\Models\MenuDia;
use App\Models\MenuDiaOpcion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class EmpleadoTomarOrdenTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_cashier_screen_shows_the_catalog_and_the_ticket_form(): void
    {
        $producto = $this->crearProducto(['nombre' => 'Café con leche', 'precio' => 5]);
        $menu = MenuDia::factory()->create(['titulo' => 'Menú universitario', 'precio_base' => 12.5]);
        $this->crearMenuConOpciones($menu);

        $this->actingAs(User::factory()->empleado()->create())->get('/empleado/tomar-orden')
            ->assertOk()
            ->assertSee('Módulo 1 · Mostrador')
            ->assertSee('Menús del día')
            ->assertSee('Menú universitario')
            ->assertSee('Adicionales al paso')
            ->assertSee($producto->nombre)
            ->assertSee('Ticket actual')
            ->assertSee('Confirmar cobro y enviar a cocina');
    }

    public function test_cash_order_is_recorded_with_stock_movement_and_change(): void
    {
        $ahora = $this->freezeTime();

        $empleado = User::factory()->empleado()->create();
        $producto = $this->crearProducto([
            'nombre' => 'Café de prueba para ticket',
            'precio' => 4.5,
            'stock_actual' => 10,
        ]);

        $this->actingAs($empleado)->post('/empleado/tomar-orden', [
            'cliente_nombre_caja' => '  Juan Pérez  ',
            'tipo_consumo' => Pedido::TIPO_CONSUMO_LLEVAR,
            'metodo_pago' => Pedido::METODO_EFECTIVO,
            'monto_recibido' => 20,
            'productos' => [
                ['tipo' => 'producto', 'id' => $producto->id, 'cantidad' => 2],
            ],
        ])->assertRedirect('/empleado/pedidos')
            ->assertSessionHas('mensaje');

        $pedido = Pedido::query()->firstOrFail();

        $this->assertSame(Pedido::ORIGEN_CAJA, $pedido->origen);
        $this->assertSame('Juan Pérez', $pedido->cliente_nombre_caja);
        $this->assertSame(Pedido::TIPO_CONSUMO_LLEVAR, $pedido->tipo_consumo);
        $this->assertSame($empleado->id, $pedido->atendido_por);
        $this->assertTrue($pedido->pagado);
        $this->assertSame('20.00', (string) $pedido->monto_recibido);
        $this->assertSame('11.00', (string) $pedido->vuelto);
        $this->assertSame('CAJA-'.$ahora->format('Ymd').'-0001', $pedido->codigo_transaccion);
        $this->assertDatabaseHas('detalle_pedidos', [
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'subtotal' => 9,
        ]);
        $this->assertSame(8, $producto->fresh()->stock_actual);
        $this->assertDatabaseHas('inventario_movimientos', [
            'tipo_item' => 'producto',
            'item_id' => $producto->id,
            'operacion' => 'venta',
            'cantidad' => 2,
            'stock_anterior' => 10,
            'stock_nuevo' => 8,
            'motivo_categoria' => 'Venta caja',
            'usuario_id' => $empleado->id,
        ]);
    }

    public function test_non_cash_order_does_not_store_received_amount_or_change(): void
    {
        $this->freezeTime();

        $producto = $this->crearProducto(['nombre' => 'Empanada de pollo', 'precio' => 6]);

        $this->actingAs(User::factory()->empleado()->create())->post('/empleado/tomar-orden', [
            'cliente_nombre_caja' => 'María Torres',
            'tipo_consumo' => Pedido::TIPO_CONSUMO_SALON,
            'metodo_pago' => Pedido::METODO_YAPE_PLIN,
            'productos' => [
                ['tipo' => 'producto', 'id' => $producto->id, 'cantidad' => 1],
            ],
        ])->assertRedirect('/empleado/pedidos');

        $pedido = Pedido::query()->firstOrFail();

        $this->assertSame(Pedido::METODO_YAPE_PLIN, $pedido->metodo_pago);
        $this->assertNull($pedido->monto_recibido);
        $this->assertNull($pedido->vuelto);
        $this->assertSame('6.00', (string) $pedido->total);
    }

    public function test_menu_order_discounts_every_option_with_tracked_stock(): void
    {
        $this->freezeTime();

        $empleado = User::factory()->empleado()->create();
        $menu = MenuDia::factory()->create(['titulo' => 'Menú del día', 'precio_base' => 12.5]);
        $opciones = $this->crearMenuConOpciones($menu, 6);

        $this->actingAs($empleado)->post('/empleado/tomar-orden', [
            'cliente_nombre_caja' => 'Pedro Salas',
            'tipo_consumo' => Pedido::TIPO_CONSUMO_SALON,
            'metodo_pago' => Pedido::METODO_TARJETA,
            'productos' => [
                [
                    'tipo' => 'menu',
                    'menu_id' => $menu->id,
                    'cantidad' => 2,
                    'opciones' => [
                        'entrada' => $opciones['entrada']->componente_id,
                        'fondo' => $opciones['fondo']->componente_id,
                        'refresco' => $opciones['refresco']->componente_id,
                    ],
                ],
            ],
        ])->assertRedirect('/empleado/pedidos');

        $pedido = Pedido::query()->firstOrFail();

        $this->assertSame('25.00', (string) $pedido->total);
        $this->assertDatabaseHas('detalle_pedidos', [
            'pedido_id' => $pedido->id,
            'tipo_item' => 'menu',
            'menu_id' => $menu->id,
            'cantidad' => 2,
            'subtotal' => 25,
        ]);

        foreach ($opciones as $opcion) {
            $this->assertSame(4, $opcion->fresh()->stock_raciones);
        }

        $this->assertDatabaseCount('inventario_movimientos', 3);
    }

    public function test_cash_order_with_insufficient_amount_is_rejected_without_saving(): void
    {
        $producto = $this->crearProducto(['precio' => 10, 'stock_actual' => 5]);

        $this->actingAs(User::factory()->empleado()->create())->post('/empleado/tomar-orden', [
            'cliente_nombre_caja' => 'Ana Torres',
            'tipo_consumo' => Pedido::TIPO_CONSUMO_SALON,
            'metodo_pago' => Pedido::METODO_EFECTIVO,
            'monto_recibido' => 8,
            'productos' => [
                ['tipo' => 'producto', 'id' => $producto->id, 'cantidad' => 1],
            ],
        ])->assertRedirect()
            ->assertSessionHasErrors('monto_recibido');

        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('inventario_movimientos', 0);
        $this->assertSame(5, $producto->fresh()->stock_actual);
    }

    public function test_empty_ticket_shows_required_field_errors(): void
    {
        $this->actingAs(User::factory()->empleado()->create())->post('/empleado/tomar-orden', [])
            ->assertRedirect()
            ->assertSessionHasErrors(['cliente_nombre_caja', 'tipo_consumo', 'metodo_pago', 'productos']);

        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_client_is_forbidden_from_creating_a_counter_order(): void
    {
        $this->actingAs(User::factory()->cliente()->create())->post('/empleado/tomar-orden', [])
            ->assertForbidden();

        $this->assertDatabaseCount('pedidos', 0);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function crearProducto(array $atributos = []): Producto
    {
        return Producto::factory()->create(array_merge([
            'precio' => 5,
            'stock_actual' => 10,
            'activo' => true,
        ], $atributos));
    }

    /**
     * @return array{entrada: MenuDiaOpcion, fondo: MenuDiaOpcion, refresco: MenuDiaOpcion}
     */
    private function crearMenuConOpciones(MenuDia $menu, int $raciones = 5): array
    {
        $entrada = MenuDiaOpcion::factory()
            ->for($menu, 'menu')
            ->for(MenuComponente::factory()->entrada()->create(['nombre' => 'Causa rellena']), 'componente')
            ->conStock($raciones)
            ->create();

        $fondo = MenuDiaOpcion::factory()
            ->for($menu, 'menu')
            ->for(MenuComponente::factory()->fondo()->create(['nombre' => 'Lomo saltado']), 'componente')
            ->conStock($raciones)
            ->create();

        $refresco = MenuDiaOpcion::factory()
            ->for($menu, 'menu')
            ->for(MenuComponente::factory()->refresco()->create(['nombre' => 'Chicha morada']), 'componente')
            ->conStock($raciones)
            ->create();

        return compact('entrada', 'fondo', 'refresco');
    }
}
