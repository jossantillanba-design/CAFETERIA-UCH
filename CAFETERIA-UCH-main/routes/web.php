<?php

use App\Http\Controllers\AdminSearchController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Empleado\HistorialController as EmpleadoHistorialController;
use App\Http\Controllers\Empleado\PedidoController as EmpleadoPedidoController;
use App\Http\Controllers\Empleado\TicketController as EmpleadoTicketController;
use App\Http\Controllers\Empleado\TomarOrdenController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\InventarioMovimientoController;
use App\Http\Controllers\MenuComponenteController;
use App\Http\Controllers\MenuDiaController;
use App\Http\Controllers\MenuImagenController;
use App\Http\Controllers\PedidoClienteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidoHistorialController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect('/login');
    }

    return match (auth()->user()->rol) {
        'administrador' => redirect('/admin/dashboard'),
        'empleado' => redirect('/empleado/tomar-orden'),
        default => redirect('/menu'),
    };
})->name('dashboard');

Route::get('/menu', [ProductoController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/checkout', [CheckoutController::class, 'index']);
    Route::post('/checkout', [CheckoutController::class, 'procesar']);
});

Route::middleware(['auth', 'rol:administrador'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
    Route::get('/admin/productos', [ProductoController::class, 'admin']);
    Route::get('/admin/productos/crear', [ProductoController::class, 'create']);
    Route::post('/admin/productos', [ProductoController::class, 'store']);
    Route::get('/admin/productos/{producto}/editar', [ProductoController::class, 'edit']);
    Route::put('/admin/productos/{producto}', [ProductoController::class, 'update']);
    Route::patch('/admin/productos/{producto}/toggle', [ProductoController::class, 'toggle']);

    Route::post('/admin/categorias/reordenar', [CategoriaController::class, 'reordenar'])->name('categorias.reordenar');
    Route::resource('/admin/categorias', CategoriaController::class)->except(['show']);
    Route::resource('/admin/inventario', InventarioController::class)->only(['index', 'store']);
    Route::get('/admin/movimientos', [InventarioMovimientoController::class, 'index']);

    Route::get('/admin/clientes', [ClienteController::class, 'index']);

    Route::get('/admin/menus', [MenuDiaController::class, 'index']);
    Route::put('/admin/menus/{menu}', [MenuDiaController::class, 'update']);
    Route::patch('/admin/menus/{menu}/toggle', [MenuDiaController::class, 'toggle']);
    Route::post('/admin/menus/{menu}/componentes', [MenuDiaController::class, 'crearComponente']);
    Route::post('/admin/menus/{menu}/imagenes', [MenuImagenController::class, 'store']);
    Route::post('/admin/menus/{menu}/imagenes/orden', [MenuImagenController::class, 'reordenar']);
    Route::delete('/admin/menus/imagenes/{imagen}', [MenuImagenController::class, 'destroy']);

    Route::get('/admin/componentes', [MenuComponenteController::class, 'index']);
    Route::get('/admin/componentes/crear', [MenuComponenteController::class, 'create']);
    Route::post('/admin/componentes', [MenuComponenteController::class, 'store']);
    Route::get('/admin/componentes/{componente}/editar', [MenuComponenteController::class, 'edit']);
    Route::put('/admin/componentes/{componente}', [MenuComponenteController::class, 'update']);
    Route::patch('/admin/componentes/{componente}/toggle', [MenuComponenteController::class, 'toggle']);
});

Route::middleware(['auth', 'rol:administrador'])->group(function () {
    Route::get('/admin/pedidos', [PedidoController::class, 'index']);
    Route::get('/admin/historial-pedidos', [PedidoHistorialController::class, 'index']);
    Route::get('/admin/buscar', AdminSearchController::class);
    Route::put('/admin/pedidos/{pedido}/estado', [PedidoController::class, 'actualizarEstado']);
    Route::get('/admin/pedidos/crear', [PedidoController::class, 'create']);
    Route::get('/admin/pedidos/{pedido}', [PedidoController::class, 'show']);
    Route::post('/admin/pedidos', [PedidoController::class, 'store']);
    Route::put('/admin/pedidos/{pedido}/pagar', [PedidoController::class, 'marcarPagado']);
});

Route::middleware(['auth', 'rol:empleado'])->prefix('empleado')->name('empleado.')->group(function () {
    Route::get('/tomar-orden', [TomarOrdenController::class, 'create'])->name('tomar-orden.create');
    Route::post('/tomar-orden', [TomarOrdenController::class, 'store'])->name('tomar-orden.store');
    Route::get('/pedidos', [EmpleadoPedidoController::class, 'index'])->name('pedidos.index');
    Route::put('/pedidos/{pedido}/estado', [EmpleadoPedidoController::class, 'actualizarEstado'])->name('pedidos.estado');
    Route::get('/pedidos/{pedido}/ticket', [EmpleadoTicketController::class, 'show'])->name('pedidos.ticket');
    Route::get('/historial', [EmpleadoHistorialController::class, 'index'])->name('historial.index');
});

Route::middleware('auth')->group(function () {
    Route::post('/pedidos', [PedidoClienteController::class, 'store']);
    Route::get('/mis-pedidos', [PedidoClienteController::class, 'index']);
});

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

require __DIR__.'/auth.php';
