<?php

use App\Http\Controllers\AdminSearchController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MenuDiaController;
use App\Http\Controllers\PedidoClienteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect('/login');
    }

    return match (auth()->user()->rol) {
        'administrador' => redirect('/admin/dashboard'),
        'empleado' => redirect('/admin/pedidos'),
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

    Route::get('/admin/clientes', [ClienteController::class, 'index']);

    Route::get('/admin/menus', [MenuDiaController::class, 'index']);
    Route::put('/admin/menus/{menu}', [MenuDiaController::class, 'update']);
    Route::patch('/admin/menus/{menu}/toggle', [MenuDiaController::class, 'toggle']);
    Route::post('/admin/menus/componentes', [MenuDiaController::class, 'storeComponente']);
});

Route::middleware(['auth', 'rol:administrador,empleado'])->group(function () {
    Route::get('/admin/pedidos', [PedidoController::class, 'index']);
    Route::get('/admin/buscar', AdminSearchController::class);
    Route::put('/admin/pedidos/{pedido}/estado', [PedidoController::class, 'actualizarEstado']);
    Route::get('/admin/pedidos/crear', [PedidoController::class, 'create']);
    Route::get('/admin/pedidos/{pedido}', [PedidoController::class, 'show']);
    Route::post('/admin/pedidos', [PedidoController::class, 'store']);
    Route::put('/admin/pedidos/{pedido}/pagar', [PedidoController::class, 'marcarPagado']);
});

Route::middleware('auth')->group(function () {
    Route::post('/pedidos', [PedidoClienteController::class, 'store']);
    Route::get('/mis-pedidos', [PedidoClienteController::class, 'index']);
});

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

require __DIR__.'/auth.php';
