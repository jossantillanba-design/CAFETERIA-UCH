<?php

namespace Tests\Feature;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class EmpleadoHistorialTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_history_shows_only_finished_orders_with_their_source_and_ticket_link(): void
    {
        $this->travelTo('2026-11-23 13:00:00');

        $empleado = User::factory()->empleado()->create();
        $clienteWeb = User::factory()->cliente()->create(['name' => 'Web Finalizada']);
        $clienteActivo = User::factory()->cliente()->create(['name' => 'Pedido Activo']);

        $caja = $this->crearPedido([
            'atendido_por' => $empleado->id,
            'origen' => Pedido::ORIGEN_CAJA,
            'cliente_nombre_caja' => 'Mostrador Finalizado',
            'estado' => 'entregado',
            'total' => 12,
        ]);
        $producto = Producto::factory()->create(['nombre' => 'Empanada finalizada']);
        $this->crearDetalleProducto($caja, $producto);

        $this->crearPedido(['cliente_id' => $clienteWeb->id, 'estado' => 'cancelado']);
        $this->crearPedido(['cliente_id' => $clienteActivo->id, 'estado' => 'pendiente']);

        $this->actingAs($empleado)->get('/empleado/historial')
            ->assertOk()
            ->assertSee('Historial de pedidos')
            ->assertSee('Tickets entregados')
            ->assertSee('Mostrador Finalizado')
            ->assertSee('Empanada finalizada')
            ->assertSee('Anulado')
            ->assertSee('Web Finalizada')
            ->assertSee('Ver ticket')
            ->assertDontSee('Pedido Activo');
    }

    public function test_arqueo_counts_only_delivered_orders_by_canonical_payment_method(): void
    {
        $this->travelTo('2026-11-23 13:00:00');

        $this->crearPedido([
            'origen' => Pedido::ORIGEN_CAJA,
            'estado' => 'entregado',
            'total' => 10,
            'metodo_pago' => Pedido::METODO_EFECTIVO,
        ]);
        $this->crearPedido([
            'origen' => Pedido::ORIGEN_CAJA,
            'estado' => 'entregado',
            'total' => 20,
            'metodo_pago' => Pedido::METODO_YAPE_PLIN,
        ]);
        $this->crearPedido([
            'origen' => Pedido::ORIGEN_CAJA,
            'estado' => 'entregado',
            'total' => 30,
            'metodo_pago' => Pedido::METODO_TARJETA,
        ]);
        $this->crearPedido([
            'origen' => Pedido::ORIGEN_CAJA,
            'estado' => 'cancelado',
            'total' => 100,
            'metodo_pago' => Pedido::METODO_EFECTIVO,
        ]);
        $this->crearPedido(['estado' => 'pendiente', 'total' => 500]);
        $this->crearPedido([
            'estado' => 'entregado',
            'total' => 999,
            'metodo_pago' => 'Yape ••• 001',
        ]);

        $this->actingAs(User::factory()->empleado()->create())->get('/empleado/historial')
            ->assertOk()
            ->assertViewHas('arqueo.efectivo', 10.0)
            ->assertViewHas('arqueo.billeteras', 20.0)
            ->assertViewHas('arqueo.tarjeta', 30.0)
            ->assertViewHas('arqueo.tickets', 4)
            ->assertDontSee('Pedido Pendiente');
    }

    public function test_origin_filter_excludes_orders_from_other_channels(): void
    {
        $this->travelTo('2026-11-23 13:00:00');

        $this->crearPedido([
            'origen' => Pedido::ORIGEN_CAJA,
            'cliente_nombre_caja' => 'Caja Visible',
            'estado' => 'entregado',
        ]);
        $this->crearPedido([
            'origen' => Pedido::ORIGEN_WEB,
            'cliente_nombre_caja' => 'Web Oculta',
            'estado' => 'entregado',
        ]);

        $this->actingAs(User::factory()->empleado()->create())->get('/empleado/historial?origen=caja')
            ->assertOk()
            ->assertViewHas('origenFiltro', Pedido::ORIGEN_CAJA)
            ->assertSee('Caja Visible')
            ->assertDontSee('Web Oculta');
    }

    public function test_payment_method_filter_excludes_other_methods(): void
    {
        $this->travelTo('2026-11-23 13:00:00');

        $this->crearPedido([
            'origen' => Pedido::ORIGEN_CAJA,
            'cliente_nombre_caja' => 'Yape Visible',
            'estado' => 'entregado',
            'metodo_pago' => Pedido::METODO_YAPE_PLIN,
        ]);
        $this->crearPedido([
            'origen' => Pedido::ORIGEN_CAJA,
            'cliente_nombre_caja' => 'Tarjeta Oculta',
            'estado' => 'entregado',
            'metodo_pago' => Pedido::METODO_TARJETA,
        ]);

        $this->actingAs(User::factory()->empleado()->create())->get('/empleado/historial?metodo_pago=yape_plin')
            ->assertOk()
            ->assertViewHas('metodoFiltro', Pedido::METODO_YAPE_PLIN)
            ->assertSee('Yape Visible')
            ->assertDontSee('Tarjeta Oculta');
    }

    public function test_date_filter_excludes_orders_from_other_days(): void
    {
        $this->travelTo('2026-11-23 13:00:00');
        $this->crearPedido(['cliente_nombre_caja' => 'Entregado Hoy', 'estado' => 'entregado']);

        $this->travelTo('2026-11-22 13:00:00');
        $this->crearPedido(['cliente_nombre_caja' => 'Entregado Ayer', 'estado' => 'entregado']);

        $this->actingAs(User::factory()->empleado()->create())->get('/empleado/historial?fecha=2026-11-23')
            ->assertOk()
            ->assertSee('Entregado Hoy')
            ->assertDontSee('Entregado Ayer');
    }

    public function test_search_finds_a_counter_customer_by_name(): void
    {
        $this->travelTo('2026-11-23 13:00:00');

        $this->crearPedido([
            'origen' => Pedido::ORIGEN_CAJA,
            'cliente_nombre_caja' => 'Cliente Mostrador Buscado',
            'estado' => 'entregado',
        ]);
        $this->crearPedido([
            'origen' => Pedido::ORIGEN_CAJA,
            'cliente_nombre_caja' => 'Cliente Oculto',
            'estado' => 'entregado',
        ]);

        $this->actingAs(User::factory()->empleado()->create())->get('/empleado/historial?buscar=Mostrador+Buscado')
            ->assertOk()
            ->assertSee('Cliente Mostrador Buscado')
            ->assertDontSee('Cliente Oculto');
    }

    public function test_search_finds_an_order_by_transaction_code(): void
    {
        $this->travelTo('2026-11-23 13:00:00');

        $pedido = $this->crearPedido([
            'origen' => Pedido::ORIGEN_CAJA,
            'cliente_nombre_caja' => 'Buscado por código',
            'estado' => 'entregado',
            'codigo_transaccion' => 'CAJA-20261123-0001',
        ]);
        $this->crearPedido(['cliente_nombre_caja' => 'Otro código', 'estado' => 'entregado']);

        $this->actingAs(User::factory()->empleado()->create())->get('/empleado/historial?buscar='.$pedido->codigo_transaccion)
            ->assertOk()
            ->assertSee('Buscado por código')
            ->assertDontSee('Otro código');
    }

    public function test_invalid_filters_fall_back_to_safe_defaults(): void
    {
        $ahora = $this->freezeTime();

        $this->actingAs(User::factory()->empleado()->create())
            ->get('/empleado/historial?origen=invalido&metodo_pago=invalido&fecha=no-valida')
            ->assertOk()
            ->assertViewHas('origenFiltro', 'todos')
            ->assertViewHas('metodoFiltro', 'todos')
            ->assertViewHas('fechaHistorial', $ahora->toDateString());
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
