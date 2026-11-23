<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PedidoHistorialControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_history_module_shows_only_finished_orders_and_hides_live_ones(): void
    {
        $this->travelTo('2026-11-21 13:00:00');

        $clienteHistorial = User::factory()->cliente()->create(['name' => 'Carlos Mendoza']);
        $clienteActivo = User::factory()->cliente()->create(['name' => 'Allison Activa']);

        $entregado = $this->crearPedido([
            'cliente_id' => $clienteHistorial->id,
            'estado' => 'entregado',
            'total' => 7.5,
            'metodo_pago' => 'Efectivo',
        ]);
        $queso = Producto::factory()->create(['nombre' => 'Empanada de queso', 'precio' => 7.5]);
        $this->crearDetalleProducto($entregado, $queso);

        $this->crearPedido(['cliente_id' => $clienteActivo->id, 'estado' => 'pendiente']);

        $this->actingAs(User::factory()->administrador()->create())->get('/admin/historial-pedidos')
            ->assertOk()
            ->assertSee('Historial de pedidos')
            ->assertSee('Carlos Mendoza')
            ->assertSee('Empanada de queso')
            ->assertSee('Ver detalle')
            ->assertSee('S/ 7.50')
            ->assertDontSee('Allison Activa');
    }

    public function test_history_search_finds_orders_by_client_name(): void
    {
        $this->travelTo('2026-11-21 13:00:00');

        $allison = User::factory()->cliente()->create(['name' => 'Allison Filtrada']);
        $carlos = User::factory()->cliente()->create(['name' => 'Carlos Oculto']);

        $this->crearPedido(['cliente_id' => $allison->id, 'estado' => 'entregado']);
        $this->crearPedido(['cliente_id' => $carlos->id, 'estado' => 'entregado']);

        $this->actingAs(User::factory()->administrador()->create())->get('/admin/historial-pedidos?buscar=Allison')
            ->assertOk()
            ->assertSee('Allison Filtrada')
            ->assertDontSee('Carlos Oculto');
    }

    public function test_history_date_filter_excludes_orders_from_other_days(): void
    {
        $this->travelTo('2026-11-21 13:00:00');
        $hoy = User::factory()->cliente()->create(['name' => 'Entregado Hoy']);
        $this->crearPedido(['cliente_id' => $hoy->id, 'estado' => 'entregado']);

        $this->travelTo('2026-11-20 13:00:00');
        $ayer = User::factory()->cliente()->create(['name' => 'Entregado Ayer']);
        $this->crearPedido(['cliente_id' => $ayer->id, 'estado' => 'entregado']);

        $this->actingAs(User::factory()->administrador()->create())->get('/admin/historial-pedidos?fecha=2026-11-21')
            ->assertOk()
            ->assertSee('Entregado Hoy')
            ->assertDontSee('Entregado Ayer');
    }

    public function test_search_finds_a_delivered_order_in_the_history(): void
    {
        $this->travelTo('2026-11-21 13:00:00');

        $cliente = User::factory()->cliente()->create(['name' => 'Buscado en historial']);
        $pedido = $this->crearPedido(['cliente_id' => $cliente->id, 'estado' => 'entregado']);

        $this->actingAs(User::factory()->administrador()->create())->get('/admin/historial-pedidos?buscar='.$pedido->id)
            ->assertOk()
            ->assertSee('Historial de pedidos')
            ->assertSee('Buscado en historial');
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
            'metodo_pago' => 'Yape ••• 001',
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
            'subtotal' => $producto->precio * $cantidad,
        ]);
    }
}
