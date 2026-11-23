<?php

namespace App\Http\Controllers;

use App\Models\MenuComponente;
use App\Models\MenuDia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuDiaController extends Controller
{
    public function index(): View
    {
        $orden = ['universitario' => 0, 'ejecutivo' => 1, 'dieta' => 2];

        $menus = MenuDia::with(['opciones.componente'])
            ->get()
            ->sortBy(fn (MenuDia $menu): int => $orden[$menu->tipo_menu] ?? 99)
            ->values();

        $componentes = [
            'entrada' => MenuComponente::delTiempo('entrada')->get(),
            'fondo' => MenuComponente::delTiempo('fondo')->get(),
            'refresco' => MenuComponente::delTiempo('refresco')->get(),
        ];

        return view('admin.menus.index', ['menus' => $menus, 'componentes' => $componentes]);
    }

    public function update(Request $request, MenuDia $menu): RedirectResponse
    {
        $datos = $request->validate([
            'titulo' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'precio_base' => 'required|numeric|min:0',
            'fecha_vigencia' => 'nullable|date',
            'etiqueta' => 'nullable|string|max:40',
            'activo' => 'boolean',
            'destacado' => 'boolean',
            'habilitados' => 'nullable|array',
            'habilitados.*' => 'integer|distinct|exists:menu_componentes,id',
            'stock_raciones' => 'nullable|array',
            'stock_raciones.*' => 'nullable|integer|min:0',
            'precio_opcion' => 'nullable|array',
            'precio_opcion.*' => 'nullable|numeric|min:0',
        ]);

        $habilitados = array_map('intval', $datos['habilitados'] ?? []);
        $componentes = MenuComponente::whereIn('id', $habilitados)->get()->keyBy('id');

        if ($menu->es_precio_variable) {
            foreach ($componentes as $componente) {
                if ($componente->tiempo !== 'fondo') {
                    continue;
                }

                if (! isset($datos['stock_raciones'][$componente->id]) || $datos['stock_raciones'][$componente->id] === '') {
                    return back()->withInput()->with('error', 'Indica las raciones disponibles para cada plato principal.');
                }

                if (! isset($datos['precio_opcion'][$componente->id]) || $datos['precio_opcion'][$componente->id] === '') {
                    return back()->withInput()->with('error', 'El Menú Ejecutivo requiere un precio por cada plato principal habilitado.');
                }
            }
        }

        $tiempos = MenuComponente::where('activo_catalogo', true)->pluck('tiempo', 'id');

        DB::transaction(function () use ($request, $menu, $datos, $habilitados, $tiempos): void {
            $menu->update([
                'titulo' => $datos['titulo'],
                'descripcion' => $datos['descripcion'] ?? null,
                'precio_base' => $datos['precio_base'],
                'fecha_vigencia' => $datos['fecha_vigencia'] ?? null,
                'etiqueta' => $datos['etiqueta'] ?? null,
                'activo' => $request->boolean('activo'),
                'destacado' => $request->boolean('destacado'),
            ]);

            foreach ($tiempos as $componenteId => $tiempo) {
                $esFondo = $tiempo === 'fondo';

                $menu->opciones()->updateOrCreate(
                    ['componente_id' => $componenteId],
                    [
                        'habilitado' => in_array($componenteId, $habilitados, true),
                        'stock_raciones' => $esFondo ? ($datos['stock_raciones'][$componenteId] ?? null) : null,
                        'precio_opcion' => $menu->es_precio_variable && $esFondo
                            ? ($datos['precio_opcion'][$componenteId] ?? null)
                            : null,
                    ],
                );
            }
        });

        return redirect('/admin/menus')->with('mensaje', 'Configuración de '.$menu->titulo.' guardada.');
    }

    public function toggle(MenuDia $menu): RedirectResponse
    {
        $menu->update(['activo' => ! $menu->activo]);

        return redirect('/admin/menus')->with('mensaje', $menu->activo ? 'Menú activado.' : 'Menú desactivado.');
    }
}
