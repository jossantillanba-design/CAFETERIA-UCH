<?php

namespace Tests\Feature\Http\Controllers;

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

class CheckoutControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login_when_processing_checkout(): void
    {
        $response = $this->post('/checkout', []);

        $response->assertRedirect('/login');
    }

    public function test_product_checkout_reduces_stock_and_records_sale(): void
    {
        $cliente = User::factory()->cliente()->create();
        $producto = Producto::factory()->create([
            'nombre' => 'Café americano',
            'precio' => 10,
            'stock_actual' => 10,
        ]);

        $response = $this->actingAs($cliente)->post('/checkout', [
            'metodo' => 'efectivo',
            'productos' => [
                [
                    'id' => $producto->id,
                    'tipo' => 'producto',
                    'cantidad' => 2,
                ],
            ],
        ]);

        $response
            ->assertRedirect('/mis-pedidos')
            ->assertSessionHas('mensaje');

        $this->assertSame(8, $producto->fresh()->stock_actual);
        $this->assertDatabaseHas('pedidos', [
            'cliente_id' => $cliente->id,
            'estado' => 'pendiente',
            'total' => '20.00',
            'pagado' => false,
            'metodo_pago' => 'Efectivo (pago en caja)',
        ]);
        $this->assertDatabaseHas('detalle_pedidos', [
            'tipo_item' => 'producto',
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'precio_unitario' => '10.00',
            'subtotal' => '20.00',
        ]);
        $this->assertDatabaseHas('inventario_movimientos', [
            'tipo_item' => InventarioMovimiento::TIPO_PRODUCTO,
            'item_id' => $producto->id,
            'operacion' => InventarioMovimiento::OPERACION_VENTA,
            'cantidad' => 2,
            'stock_anterior' => 10,
            'stock_nuevo' => 8,
            'motivo_categoria' => 'Venta web',
            'usuario_id' => $cliente->id,
        ]);
    }

    public function test_checkout_rejects_insufficient_stock_without_creating_an_order(): void
    {
        $producto = Producto::factory()->create([
            'nombre' => 'Brownie',
            'precio' => 8,
            'stock_actual' => 1,
        ]);

        $response = $this->actingAs(User::factory()->cliente()->create())->post('/checkout', [
            'metodo' => 'tarjeta',
            ...$this->datosTarjeta(),
            'productos' => [
                [
                    'id' => $producto->id,
                    'tipo' => 'producto',
                    'cantidad' => 3,
                ],
            ],
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHasErrors([
                'productos' => 'Stock insuficiente para Brownie.',
            ]);

        $this->assertSame(1, $producto->fresh()->stock_actual);
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('detalle_pedidos', 0);
        $this->assertDatabaseCount('inventario_movimientos', 0);
    }

    public function test_variable_price_menu_checkout_uses_selected_main_course_and_decrements_rations(): void
    {
        $cliente = User::factory()->cliente()->create();
        $menu = $this->crearMenuConTresTiempos([
            'titulo' => 'Menú Ejecutivo',
            'es_precio_variable' => true,
            'precio_base' => 12,
        ]);

        $response = $this->actingAs($cliente)->post('/checkout', [
            'metodo' => 'tarjeta',
            ...$this->datosTarjeta(),
            'productos' => [
                [
                    'id' => 'menu-'.$menu['menu']->id,
                    'tipo' => 'menu',
                    'menu_id' => $menu['menu']->id,
                    'cantidad' => 2,
                    'opciones' => [
                        'entrada' => $menu['entrada']->id,
                        'fondo' => $menu['fondo']->id,
                        'refresco' => $menu['refresco']->id,
                    ],
                ],
            ],
        ]);

        $response->assertRedirect('/mis-pedidos');

        $pedido = Pedido::firstOrFail();
        $detalle = DetallePedido::firstOrFail();

        $this->assertSame(3, $menu['opcionFondo']->fresh()->stock_raciones);
        $this->assertSame('37.00', (string) $pedido->total);
        $this->assertTrue($pedido->pagado);
        $this->assertNotNull($pedido->codigo_transaccion);
        $this->assertSame('menu', $detalle->tipo_item);
        $this->assertSame($menu['menu']->id, $detalle->menu_id);
        $this->assertSame($menu['entrada']->id, $detalle->entrada_id);
        $this->assertSame($menu['fondo']->id, $detalle->fondo_id);
        $this->assertSame($menu['refresco']->id, $detalle->refresco_id);
        $this->assertSame('18.50', (string) $detalle->precio_unitario);
        $this->assertSame('37.00', (string) $detalle->subtotal);
        $this->assertStringContainsString('Fondo: '.$menu['fondo']->nombre, $detalle->detalle_menu);
        $this->assertDatabaseHas('inventario_movimientos', [
            'tipo_item' => InventarioMovimiento::TIPO_OPCION_MENU,
            'item_id' => $menu['opcionFondo']->id,
            'operacion' => InventarioMovimiento::OPERACION_VENTA,
            'cantidad' => 2,
            'stock_anterior' => 5,
            'stock_nuevo' => 3,
            'usuario_id' => $cliente->id,
        ]);
    }

    public function test_menu_checkout_resolves_options_by_component_id_when_ids_differ(): void
    {
        $menusPrevios = MenuDia::factory()->count(2)->create();
        $componentesPrevios = MenuComponente::factory()->count(3)->entrada()->create();

        foreach ($componentesPrevios as $componentePrevio) {
            foreach ($menusPrevios as $menuPrevio) {
                MenuDiaOpcion::factory()
                    ->for($menuPrevio, 'menu')
                    ->for($componentePrevio, 'componente')
                    ->create();
            }
        }

        $menu = $this->crearMenuConTresTiempos([
            'titulo' => 'Menú Ejecutivo',
            'es_precio_variable' => true,
            'precio_base' => 12,
        ]);

        $this->assertNotSame($menu['entrada']->id, $menu['opcionEntrada']->id);

        $this->actingAs(User::factory()->cliente()->create())->post('/checkout', [
            'metodo' => 'efectivo',
            'productos' => [
                [
                    'id' => 'menu-'.$menu['menu']->id,
                    'tipo' => 'menu',
                    'menu_id' => $menu['menu']->id,
                    'cantidad' => 2,
                    'opciones' => [
                        'entrada' => $menu['entrada']->id,
                        'fondo' => $menu['fondo']->id,
                        'refresco' => $menu['refresco']->id,
                    ],
                ],
            ],
        ])->assertRedirect('/mis-pedidos');

        $pedido = Pedido::firstOrFail();
        $detalle = DetallePedido::firstOrFail();

        $this->assertSame($menu['entrada']->id, $detalle->entrada_id);
        $this->assertSame($menu['fondo']->id, $detalle->fondo_id);
        $this->assertSame($menu['refresco']->id, $detalle->refresco_id);
        $this->assertSame(3, $menu['opcionFondo']->fresh()->stock_raciones);
        $this->assertSame('18.50', (string) $detalle->precio_unitario);
        $this->assertSame('37.00', (string) $pedido->total);
        $this->assertStringContainsString('Fondo: '.$menu['fondo']->nombre, $detalle->detalle_menu);
        $this->assertDatabaseHas('inventario_movimientos', [
            'tipo_item' => InventarioMovimiento::TIPO_OPCION_MENU,
            'item_id' => $menu['opcionFondo']->id,
            'cantidad' => 2,
            'stock_nuevo' => 3,
        ]);
    }

    public function test_checkout_rejects_menu_option_from_another_menu(): void
    {
        $menu = $this->crearMenuConTresTiempos();
        $componenteAjeno = MenuComponente::factory()->fondo()->create();

        $response = $this->actingAs(User::factory()->cliente()->create())->post('/checkout', [
            'metodo' => 'efectivo',
            'productos' => [
                [
                    'id' => 'menu-'.$menu['menu']->id,
                    'tipo' => 'menu',
                    'menu_id' => $menu['menu']->id,
                    'cantidad' => 1,
                    'opciones' => [
                        'entrada' => $menu['entrada']->id,
                        'fondo' => $componenteAjeno->id,
                        'refresco' => $menu['refresco']->id,
                    ],
                ],
            ],
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHasErrors('productos');

        $this->assertSame(5, $menu['opcionFondo']->fresh()->stock_raciones);
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('detalle_pedidos', 0);
        $this->assertDatabaseCount('inventario_movimientos', 0);
    }

    public function test_rejected_payment_does_not_create_order_or_change_stock(): void
    {
        $producto = Producto::factory()->create([
            'precio' => 10,
            'stock_actual' => 5,
        ]);

        $response = $this->actingAs(User::factory()->cliente()->create())->post('/checkout', [
            'metodo' => 'tarjeta',
            'numero_tarjeta' => '4000040000000008',
            'nombre_tarjeta' => 'Cliente UCH',
            'vencimiento' => '12/30',
            'cvv' => '123',
            'productos' => [
                [
                    'id' => $producto->id,
                    'tipo' => 'producto',
                    'cantidad' => 2,
                ],
            ],
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('error', 'Pago rechazado: Fondos insuficientes.');

        $this->assertSame(5, $producto->fresh()->stock_actual);
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('inventario_movimientos', 0);
    }

    /**
     * @param  array<string, mixed>  $atributos
     * @return array<string, mixed>
     */
    private function crearMenuConTresTiempos(array $atributos = []): array
    {
        $menu = MenuDia::factory()->create($atributos);
        $entrada = MenuComponente::factory()->entrada()->create();
        $fondo = MenuComponente::factory()->fondo()->create();
        $refresco = MenuComponente::factory()->refresco()->create();

        $opcionEntrada = MenuDiaOpcion::factory()
            ->for($menu, 'menu')
            ->for($entrada, 'componente')
            ->create();
        $opcionFondo = MenuDiaOpcion::factory()
            ->for($menu, 'menu')
            ->for($fondo, 'componente')
            ->create([
                'stock_raciones' => 5,
                'precio_opcion' => 18.50,
            ]);
        $opcionRefresco = MenuDiaOpcion::factory()
            ->for($menu, 'menu')
            ->for($refresco, 'componente')
            ->create();

        return compact(
            'menu',
            'entrada',
            'fondo',
            'refresco',
            'opcionEntrada',
            'opcionFondo',
            'opcionRefresco',
        );
    }

    /**
     * @return array<string, string>
     */
    private function datosTarjeta(): array
    {
        return [
            'numero_tarjeta' => '4242 4242 4242 4242',
            'nombre_tarjeta' => 'Cliente UCH',
            'vencimiento' => '12/30',
            'cvv' => '123',
        ];
    }
}
