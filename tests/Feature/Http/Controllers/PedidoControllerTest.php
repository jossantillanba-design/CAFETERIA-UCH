<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\MenuComponente;
use App\Models\MenuDia;
use App\Models\MenuDiaOpcion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PedidoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_employee_sees_live_card_details_and_order_history(): void
    {
        $this->travelTo('2026-11-21 13:00:00');

        $empleado = User::factory()->empleado()->create();
        $cliente = User::factory()->cliente()->create(['name' => 'Allison Aguirre']);
        $clienteHistorial = User::factory()->cliente()->create(['name' => 'Carlos Mendoza']);

        $pendiente = $this->crearPedido([
            'cliente_id' => $cliente->id,
            'estado' => 'pendiente',
            'total' => 15,
        ]);
        $this->crearDetalleMenu($pendiente, $this->crearMenuConTresTiempos('Menú Universitario'));
        $cafe = Producto::factory()->create(['nombre' => 'Café americano', 'precio' => 3.5]);
        $this->crearDetalleProducto($pendiente, $cafe);

        $listo = $this->crearPedido(['estado' => 'listo', 'total' => 23]);
        $empanada = Producto::factory()->create(['nombre' => 'Empanada de pollo', 'precio' => 4.5]);
        $this->crearDetalleProducto($listo, $empanada, 2);

        $entregado = $this->crearPedido([
            'cliente_id' => $clienteHistorial->id,
            'estado' => 'entregado',
            'total' => 7.5,
            'metodo_pago' => 'Efectivo',
        ]);
        $queso = Producto::factory()->create(['nombre' => 'Empanada de queso', 'precio' => 7.5]);
        $this->crearDetalleProducto($entregado, $queso);

        $this->actingAs($empleado)->get('/admin/pedidos')
            ->assertOk()
            ->assertSee('Pedidos en vivo')
            ->assertSee('Cocina y barra')
            ->assertSee('Todos activos')
            ->assertSee('Pedido #'.str_pad((string) $pendiente->id, 3, '0', STR_PAD_LEFT))
            ->assertSee('Allison Aguirre')
            ->assertSee('Causa rellena de pollo')
            ->assertSee('Lomo saltado')
            ->assertSee('Chicha morada')
            ->assertSee('Preparar orden')
            ->assertSee('Confirmar entrega')
            ->assertSee('Historial de pedidos')
            ->assertSee('Carlos Mendoza')
            ->assertSee('Empanada de queso')
            ->assertSee('Ver detalle')
            ->assertSee('S/ 7.50');
    }

    public function test_status_filter_limits_the_live_cards(): void
    {
        $pendiente = $this->crearPedido(['estado' => 'pendiente']);
        $cafePendiente = Producto::factory()->create(['nombre' => 'Café filtro pendiente']);
        $this->crearDetalleProducto($pendiente, $cafePendiente);

        $listo = $this->crearPedido(['estado' => 'listo']);
        $cafeListo = Producto::factory()->create(['nombre' => 'Café filtro listo']);
        $this->crearDetalleProducto($listo, $cafeListo);

        $this->actingAs(User::factory()->empleado()->create())->get('/admin/pedidos?estado=listo')
            ->assertOk()
            ->assertSee('Café filtro listo')
            ->assertDontSee('Café filtro pendiente');
    }

    public function test_unknown_status_filter_falls_back_to_all_active_orders(): void
    {
        $pedido = $this->crearPedido(['estado' => 'pendiente']);
        $producto = Producto::factory()->create(['nombre' => 'Pan de yema filtrado']);
        $this->crearDetalleProducto($pedido, $producto);

        $this->actingAs(User::factory()->empleado()->create())->get('/admin/pedidos?estado=entregado')
            ->assertOk()
            ->assertSee('Pan de yema filtrado');
    }

    #[TestWith(['pendiente', 'en_preparacion'])]
    #[TestWith(['en_preparacion', 'listo'])]
    #[TestWith(['listo', 'entregado'])]
    public function test_quick_action_advances_order_to_the_next_status(string $estadoInicial, string $estadoEsperado): void
    {
        $pedido = $this->crearPedido(['estado' => $estadoInicial]);

        $this->actingAs(User::factory()->empleado()->create())
            ->put('/admin/pedidos/'.$pedido->id.'/estado', ['estado' => $estadoEsperado])
            ->assertRedirect('/admin/pedidos');

        $this->assertSame($estadoEsperado, $pedido->fresh()->estado);
    }

    public function test_invalid_status_update_is_rejected_without_changing_the_order(): void
    {
        $pedido = $this->crearPedido(['estado' => 'pendiente']);

        $this->actingAs(User::factory()->empleado()->create())
            ->put('/admin/pedidos/'.$pedido->id.'/estado', ['estado' => 'estado_inexistente'])
            ->assertRedirect()
            ->assertSessionHasErrors('estado');

        $this->assertSame('pendiente', $pedido->fresh()->estado);
    }

    public function test_history_search_finds_orders_by_client_name(): void
    {
        $this->travelTo('2026-11-21 13:00:00');

        $allison = User::factory()->cliente()->create(['name' => 'Allison Filtrada']);
        $carlos = User::factory()->cliente()->create(['name' => 'Carlos Oculto']);

        $this->crearPedido(['cliente_id' => $allison->id, 'estado' => 'entregado']);
        $this->crearPedido(['cliente_id' => $carlos->id, 'estado' => 'entregado']);

        $this->actingAs(User::factory()->empleado()->create())->get('/admin/pedidos?historial=Allison')
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

        $this->actingAs(User::factory()->empleado()->create())->get('/admin/pedidos?fecha=2026-11-21')
            ->assertOk()
            ->assertSee('Entregado Hoy')
            ->assertDontSee('Entregado Ayer');
    }

    public function test_global_search_finds_a_delivered_order_in_the_history(): void
    {
        $this->travelTo('2026-11-21 13:00:00');

        $cliente = User::factory()->cliente()->create(['name' => 'Buscado en historial']);
        $pedido = $this->crearPedido(['cliente_id' => $cliente->id, 'estado' => 'entregado']);

        $this->actingAs(User::factory()->empleado()->create())->get('/admin/pedidos?buscar='.$pedido->id)
            ->assertOk()
            ->assertSee('Historial de pedidos')
            ->assertSee('Buscado en historial');
    }

    public function test_employee_can_open_ticket_detail_with_full_order_information(): void
    {
        $cliente = User::factory()->cliente()->create(['name' => 'Ticket Detallado']);
        $pedido = $this->crearPedido([
            'cliente_id' => $cliente->id,
            'estado' => 'pendiente',
            'total' => 20,
            'pagado' => false,
            'metodo_pago' => null,
        ]);
        $this->crearDetalleMenu($pedido, $this->crearMenuConTresTiempos('Menú Ejecutivo'), 2);

        $this->actingAs(User::factory()->empleado()->create())->get('/admin/pedidos/'.$pedido->id)
            ->assertOk()
            ->assertSee('Ticket #'.str_pad((string) $pedido->id, 3, '0', STR_PAD_LEFT))
            ->assertSee('Ticket Detallado')
            ->assertSee('Menú Ejecutivo')
            ->assertSee('Causa rellena de pollo')
            ->assertSee('Pendiente de cobro')
            ->assertSee('COMEDOR UNIVERSITARIO')
            ->assertSee('Comprobante Interno')
            ->assertSee('Detalle de Consumos')
            ->assertSee('Subtotal afecto')
            ->assertSee('IGV (18%)')
            ->assertSee('OP: UCH-')
            ->assertSee('Imprimir ticket')
            ->assertSee('Trazabilidad')
            ->assertSee('S/ 20.00');
    }

    public function test_customer_forbidden_from_opening_ticket_detail(): void
    {
        $pedido = $this->crearPedido();

        $this->actingAs(User::factory()->cliente()->create())
            ->get('/admin/pedidos/'.$pedido->id)
            ->assertForbidden();
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

    /**
     * @return array{menu: MenuDia, entrada: MenuDiaOpcion, fondo: MenuDiaOpcion, refresco: MenuDiaOpcion}
     */
    private function crearMenuConTresTiempos(string $titulo): array
    {
        $menu = MenuDia::factory()->create(['titulo' => $titulo, 'precio_base' => 10]);
        $componenteEntrada = MenuComponente::factory()->entrada()->create(['nombre' => 'Causa rellena de pollo']);
        $componenteFondo = MenuComponente::factory()->fondo()->create(['nombre' => 'Lomo saltado']);
        $componenteRefresco = MenuComponente::factory()->refresco()->create(['nombre' => 'Chicha morada']);

        $entrada = MenuDiaOpcion::factory()->for($menu, 'menu')->for($componenteEntrada, 'componente')->create();
        $fondo = MenuDiaOpcion::factory()->for($menu, 'menu')->for($componenteFondo, 'componente')->create();
        $refresco = MenuDiaOpcion::factory()->for($menu, 'menu')->for($componenteRefresco, 'componente')->create();

        return compact('menu', 'entrada', 'fondo', 'refresco');
    }

    /**
     * @param  array{menu: MenuDia, entrada: MenuDiaOpcion, fondo: MenuDiaOpcion, refresco: MenuDiaOpcion}  $opciones
     */
    private function crearDetalleMenu(Pedido $pedido, array $opciones, int $cantidad = 1): void
    {
        DetallePedido::create([
            'pedido_id' => $pedido->id,
            'tipo_item' => 'menu',
            'menu_id' => $opciones['menu']->id,
            'entrada_id' => $opciones['entrada']->componente_id,
            'fondo_id' => $opciones['fondo']->componente_id,
            'refresco_id' => $opciones['refresco']->componente_id,
            'detalle_menu' => 'Entrada: Causa rellena de pollo · Fondo: Lomo saltado · Refresco: Chicha morada',
            'cantidad' => $cantidad,
            'precio_unitario' => 10,
            'subtotal' => 10 * $cantidad,
        ]);
    }
}
