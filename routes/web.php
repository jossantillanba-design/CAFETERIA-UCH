<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/menu', [ProductoController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'rol:administrador'])->group(function () {
    Route::get('/admin/productos', [ProductoController::class, 'admin']);
    Route::get('/admin/productos/crear', [ProductoController::class, 'create']);
    Route::post('/admin/productos', [ProductoController::class, 'store']);
    Route::get('/admin/productos/{producto}/editar', [ProductoController::class, 'edit']);
    Route::put('/admin/productos/{producto}', [ProductoController::class, 'update']);
    Route::delete('/admin/productos/{producto}', [ProductoController::class, 'destroy']);
});

require __DIR__.'/auth.php';