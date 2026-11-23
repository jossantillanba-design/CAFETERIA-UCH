<?php

namespace App\Providers;

use App\Models\MenuDiaOpcion;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || app()->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('components.admin-nav', function ($view): void {
            $view->with('pedidosPendientes', auth()->check()
                ? Pedido::where('estado', 'pendiente')->count()
                : 0);
        });

        View::composer('components.admin-topbar', function ($view): void {
            $alertas = collect();
            $criticas = 0;

            if (auth()->check() && in_array(auth()->user()->rol, ['administrador', 'empleado'], true)) {
                $productos = Producto::stockBajo()->with('categoria')->orderBy('stock_actual')->get();
                $raciones = MenuDiaOpcion::stockBajo()
                    ->with(['componente', 'menu'])
                    ->orderBy('stock_raciones')
                    ->get();

                $alertas = $productos->map(fn (Producto $producto): array => [
                    'clave' => 'producto-'.$producto->id,
                    'nombre' => $producto->nombre,
                    'referencia' => $producto->sku ?: 'PRO-'.$producto->id,
                    'categoria' => $producto->categoria->nombre ?? 'Sin categoría',
                    'stock' => (int) $producto->stock_actual,
                    'minimo' => (int) $producto->stock_minimo,
                    'unidad' => 'unid.',
                    'critica' => (int) $producto->stock_actual <= 0,
                ])->concat($raciones->map(function (MenuDiaOpcion $opcion): array {
                    $stock = (int) $opcion->stock_raciones;

                    return [
                        'clave' => 'racion-'.$opcion->id,
                        'nombre' => $opcion->componente->nombre ?? 'Ración de menú',
                        'referencia' => 'MEN-'.strtoupper(substr($opcion->menu->tipo_menu ?? 'men', 0, 3)).'-'.str_pad((string) $opcion->id, 3, '0', STR_PAD_LEFT),
                        'categoria' => $opcion->menu->titulo ?? 'Menú del día',
                        'stock' => $stock,
                        'minimo' => 5,
                        'unidad' => 'rac.',
                        'critica' => $stock <= 0,
                    ];
                }))->values()->take(4);

                $criticas = $productos->filter(fn (Producto $producto): bool => (int) $producto->stock_actual <= 0)->count()
                    + $raciones->filter(fn (MenuDiaOpcion $opcion): bool => (int) $opcion->stock_raciones <= 0)->count();
            }

            $view->with([
                'alertasTopbar' => $alertas,
                'totalAlertasTopbar' => $alertas->count(),
                'criticasTopbar' => $criticas,
            ]);
        });
    }
}
