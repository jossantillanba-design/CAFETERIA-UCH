<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidoClienteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/menu', [ProductoController::class, 'index']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'rol:administrador'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
    Route::get('/admin/productos', [ProductoController::class, 'admin']);
    Route::get('/admin/productos/crear', [ProductoController::class, 'create']);
    Route::post('/admin/productos', [ProductoController::class, 'store']);
    Route::get('/admin/productos/{producto}/editar', [ProductoController::class, 'edit']);
    Route::put('/admin/productos/{producto}', [ProductoController::class, 'update']);
    Route::delete('/admin/productos/{producto}', [ProductoController::class, 'destroy']);
});

Route::middleware(['auth', 'rol:administrador,empleado'])->group(function () {
    Route::get('/admin/pedidos', [PedidoController::class, 'index']);
    Route::put('/admin/pedidos/{pedido}/estado', [PedidoController::class, 'actualizarEstado']);
    Route::get('/admin/pedidos/crear', [PedidoController::class, 'create']);
    Route::post('/admin/pedidos', [PedidoController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/pedidos', [PedidoClienteController::class, 'store']);
    Route::get('/mis-pedidos', [PedidoClienteController::class, 'index']);
});
require __DIR__.'/auth.php';