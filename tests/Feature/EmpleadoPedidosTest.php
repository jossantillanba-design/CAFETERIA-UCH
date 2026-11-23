<?php

namespace Tests\Feature;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class EmpleadoPedidosTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_live_queue_shows_web_and_counter_orders_with_their_source_badges(): void
    {
        $this->travelTo('2026-11-23 13:00:00');

        $empleado = User::factory()->empleado()->create();
        $clienteWeb = User::factory()->cliente()->create(['name' => 'Allison Web']);
        $clienteEntregado = User::factory()->cliente()->create(['name' => 'Pedido Entregado']);

        $this->crearPedido([
            'cliente_id' => $clienteWeb->id,
            'estado' => 'pendiente',
            'total' => 15,
            'metodo_pago' => 'Yape ••• 001',
        ]);
        $this->crearPedido([
            'atendido_por' => $empleado->id,
            'cliente_nombre_caja' => 'Juan Mostrador',
            'origen' => Pedido::ORIGEN_CAJA,
            'tipo_consumo' => Pedido::TIPO_CONSUMO_SALON,
            'estado' => 'listo',
            'total' => 9,
            'metodo_pago' => Pedido::METODO_EFECTIVO,
            'monto_recibido' => 10,
            'vuelto' => 1,
            'codigo_transaccion' => 'CAJA-20261123-0002',
        ]);
        $this->crearPedido([
            'cliente_id' => $clienteEntregado->id,
            'estado' => 'entregado',
            'total' => 7.5,
        ]);

        $this->actingAs($empleado)->get('/empleado/pedidos')
            ->assertOk()
            ->assertSee('Pedidos en vivo')
            ->assertSee('Cocina y barra')
            ->assertSee('Allison Web')
            ->assertSee('Web · Pagado')
            ->assertSee('Juan Mostrador')
            ->assertSee('Mostrador · Caja')
            ->assertSee('Vuelto S/ 1.00')
            ->assertSee('Confirmar entrega')
            ->assertDontSee('Pedido Entregado');
    }

    public function test_status_filter_limits_the_live_cards(): void
    {
        $pendiente = $this->crearPedido(['estado' => 'pendiente']);
        $listo = $this->crearPedido(['estado' => 'listo']);
        $productoPendiente = Producto::factory()->create(['nombre' => 'Café filtro pendiente']);
        $productoListo = Producto::factory()->create(['nombre' => 'Café filtro listo']);
        $this->crearDetalleProducto($pendiente, $productoPendiente);
        $this->crearDetalleProducto($listo, $productoListo);

        $this->actingAs(User::factory()->empleado()->create())->get('/empleado/pedidos?estado=listo')
            ->assertOk()
            ->assertSee('Café filtro listo')
            ->assertDontSee('Café filtro pendiente');
    }

    #[TestWith(['pendiente', 'en_preparacion'])]
    #[TestWith(['en_preparacion', 'listo'])]
    #[TestWith(['listo', 'entregado'])]
    public function test_quick_action_advances_order_to_the_next_status(string $estadoInicial, string $estadoEsperado): void
    {
        $pedido = $this->crearPedido(['estado' => $estadoInicial]);

        $this->actingAs(User::factory()->empleado()->create())
            ->put('/empleado/pedidos/'.$pedido->id.'/estado', ['estado' => $estadoEsperado])
            ->assertRedirect('/empleado/pedidos');

        $this->assertSame($estadoEsperado, $pedido->fresh()->estado);
    }

    public function test_cancelling_a_counter_order_returns_stock_and_records_an_ingress(): void
    {
        $empleado = User::factory()->empleado()->create();
        $producto = Producto::factory()->create(['stock_actual' => 7, 'activo' => true]);
        $pedido = $this->crearPedido([
            'atendido_por' => $empleado->id,
            'origen' => Pedido::ORIGEN_CAJA,
            'cliente_nombre_caja' => 'Cliente anulado',
            'estado' => 'pendiente',
        ]);
        $this->crearDetalleProducto($pedido, $producto, 3);

        $this->actingAs($empleado)
            ->put('/empleado/pedidos/'.$pedido->id.'/estado', ['estado' => 'cancelado'])
            ->assertRedirect('/empleado/pedidos');

        $this->assertSame('cancelado', $pedido->fresh()->estado);
        $this->assertSame(10, $producto->fresh()->stock_actual);
        $this->assertDatabaseHas('inventario_movimientos', [
            'tipo_item' => 'producto',
            'item_id' => $producto->id,
            'operacion' => 'ingreso',
            'cantidad' => 3,
            'stock_anterior' => 7,
            'stock_nuevo' => 10,
            'motivo_categoria' => 'Anulación de ticket',
            'usuario_id' => $empleado->id,
        ]);
    }

    public function test_invalid_status_transition_is_rejected_without_changing_the_order(): void
    {
        $pedido = $this->crearPedido(['estado' => 'listo']);

        $this->actingAs(User::factory()->empleado()->create())
            ->put('/empleado/pedidos/'.$pedido->id.'/estado', ['estado' => 'cancelado'])
            ->assertRedirect()
            ->assertSessionHasErrors('estado');

        $this->assertSame('listo', $pedido->fresh()->estado);
    }

    public function test_ticket_page_shows_payment_details_and_is_printable(): void
    {
        $empleado = User::factory()->empleado()->create(['name' => 'Ana Cajera']);
        $pedido = $this->crearPedido([
            'atendido_por' => $empleado->id,
            'origen' => Pedido::ORIGEN_CAJA,
            'cliente_nombre_caja' => 'Juan Pérez',
            'tipo_consumo' => Pedido::TIPO_CONSUMO_LLEVAR,
            'estado' => 'entregado',
            'total' => 15,
            'metodo_pago' => Pedido::METODO_EFECTIVO,
            'monto_recibido' => 20,
            'vuelto' => 5,
            'codigo_transaccion' => 'CAJA-20261123-0001',
        ]);
        $producto = Producto::factory()->create(['nombre' => 'Café con leche', 'precio' => 15]);
        $this->crearDetalleProducto($pedido, $producto);

        $this->actingAs(User::factory()->empleado()->create())->get('/empleado/pedidos/'.$pedido->id.'/ticket')
            ->assertOk()
            ->assertSee('COMEDOR UNIVERSITARIO')
            ->assertSee('Detalle de consumos')
            ->assertSee('Café con leche')
            ->assertSee('S/ 15.00')
            ->assertSee('Mostrador · Ana Cajera')
            ->assertSee('Para llevar')
            ->assertSee('Monto recibido (Efectivo)')
            ->assertSee('Vuelto entregado')
            ->assertSee('S/ 5.00')
            ->assertSee('OP: CAJA-20261123-0001')
            ->assertSee('Imprimir ticket')
            ->assertSee('window.print()', false);
    }

    public function test_client_is_forbidden_from_opening_an_employee_ticket(): void
    {
        $pedido = $this->crearPedido();

        $this->actingAs(User::factory()->cliente()->create())
            ->get('/empleado/pedidos/'.$pedido->id.'/ticket')
            ->assertForbidden();
    }

    public function test_client_is_forbidden_from_changing_an_employee_order_status(): void
    {
        $pedido = $this->crearPedido(['estado' => 'pendiente']);

        $this->actingAs(User::factory()->cliente()->create())
            ->put('/empleado/pedidos/'.$pedido->id.'/estado', ['estado' => 'listo'])
            ->assertForbidden();

        $this->assertSame('pendiente', $pedido->fresh()->estado);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function crearPedido(array $atributos = []): Pedido
    {
        return Pedido::create(array_merge([
            'estado' => 'pendiente',
            'total' => 10,
            'pagado' => true,
            'metodo_pago' => Pedido::METODO_EFECTIVO,
        ], $atributos));
    }

    private function crearDetalleProducto(Pedido $pedido, Producto $producto, int $cantidad = 1): void
    {
        DetallePedido::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'tipo_item' => 'producto',
            'cantidad' => $cantidad,
            'precio_unitario' => $producto->precio,
            'subtotal' => (float) $producto->precio * $cantidad,
        ]);
    }
}
