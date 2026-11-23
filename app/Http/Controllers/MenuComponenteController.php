<?php

namespace App\Http\Controllers;

use App\Models\MenuComponente;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuComponenteController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim($request->string('buscar')->toString());
        $tiempoFiltro = $request->string('tiempo')->toString();
        $estadoFiltro = $request->string('estado')->toString();

        if (! in_array($tiempoFiltro, ['entrada', 'fondo', 'refresco'], true)) {
            $tiempoFiltro = 'all';
        }

        if (! in_array($estadoFiltro, ['activo', 'inactivo'], true)) {
            $estadoFiltro = 'all';
        }

        $consulta = MenuComponente::withCount('opcionesHabilitadas');

        if ($buscar !== '') {
            $consulta->where(function (EloquentBuilder $query) use ($buscar): void {
                $query->where('nombre', 'like', '%'.$buscar.'%')
                    ->orWhere('descripcion', 'like', '%'.$buscar.'%');
            });
        }

        if ($tiempoFiltro !== 'all') {
            $consulta->where('tiempo', $tiempoFiltro);
        }

        if ($estadoFiltro !== 'all') {
            $consulta->where('activo_catalogo', $estadoFiltro === 'activo');
        }

        $componentes = $consulta->orderBy('tiempo')->orderBy('nombre')->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        $metricas = [
            'total' => MenuComponente::count(),
            'activos' => MenuComponente::where('activo_catalogo', true)->count(),
            'enMenus' => MenuComponente::whereHas('opcionesHabilitadas')->count(),
            'inactivos' => MenuComponente::where('activo_catalogo', false)->count(),
        ];

        return view('admin.componentes.index', [
            'componentes' => $componentes,
            'metricas' => $metricas,
            'buscar' => $buscar,
            'tiempoFiltro' => $tiempoFiltro === 'all' ? '' : $tiempoFiltro,
            'estadoFiltro' => $estadoFiltro,
        ]);
    }

    public function create(): View
    {
        return view('admin.componentes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => [
                'required', 'string', 'max:120',
                Rule::unique('menu_componentes', 'nombre')
                    ->where(fn (Builder $query): Builder => $query->where('tiempo', $request->input('tiempo'))),
            ],
            'descripcion' => ['nullable', 'string', 'max:200'],
            'tiempo' => ['required', Rule::in(['entrada', 'fondo', 'refresco'])],
        ], [
            'nombre.unique' => 'Ese componente ya existe en el banco de opciones.',
        ]);

        MenuComponente::create([
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'tiempo' => $datos['tiempo'],
            'activo_catalogo' => $request->boolean('activo_catalogo', true),
        ]);

        return redirect('/admin/componentes')->with('mensaje', 'Componente añadido al catálogo.');
    }

    public function edit(MenuComponente $componente): View
    {
        return view('admin.componentes.edit', ['componente' => $componente]);
    }

    public function update(Request $request, MenuComponente $componente): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => [
                'required', 'string', 'max:120',
                Rule::unique('menu_componentes', 'nombre')
                    ->where(fn (Builder $query): Builder => $query->where('tiempo', $request->input('tiempo')))
                    ->ignore($componente->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:200'],
            'tiempo' => ['required', Rule::in(['entrada', 'fondo', 'refresco'])],
        ], [
            'nombre.unique' => 'Ese componente ya existe en el banco de opciones.',
        ]);

        DB::transaction(function () use ($componente, $datos, $request): void {
            $componente->update([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'tiempo' => $datos['tiempo'],
                'activo_catalogo' => $request->boolean('activo_catalogo'),
            ]);

            if (! $componente->activo_catalogo) {
                $componente->opciones()->update(['habilitado' => false]);
            }
        });

        return redirect('/admin/componentes')->with('mensaje', 'Componente actualizado correctamente.');
    }

    public function toggle(MenuComponente $componente): RedirectResponse
    {
        DB::transaction(function () use ($componente): void {
            $componente->update(['activo_catalogo' => ! $componente->activo_catalogo]);

            if (! $componente->activo_catalogo) {
                $componente->opciones()->update(['habilitado' => false]);
            }
        });

        return redirect('/admin/componentes')
            ->with('mensaje', $componente->activo_catalogo ? 'Componente activado.' : 'Componente desactivado.');
    }
}
