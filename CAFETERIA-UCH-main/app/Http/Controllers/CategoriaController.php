<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\MenuDia;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    /** @var array<int, string> */
    private const ORDEN_MENUS = ['universitario', 'ejecutivo', 'dieta'];

    /** @var array<string, string> */
    private const COLORES_MENUS = [
        'universitario' => '#0066B3',
        'ejecutivo' => '#F59E0B',
        'dieta' => '#00B4A6',
    ];

    public function index(): View
    {
        $categorias = Categoria::withCount('productos')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $menus = MenuDia::query()->withCount('opcionesHabilitadas')->get();
        $estructura = collect();

        foreach (self::ORDEN_MENUS as $indice => $tipoMenu) {
            $menu = $menus->firstWhere('tipo_menu', $tipoMenu);

            if (! $menu) {
                continue;
            }

            $estructura->push([
                'es_menu' => true,
                'id' => $menu->id,
                'color' => self::COLORES_MENUS[$tipoMenu],
                'nombre' => $menu->titulo,
                'descripcion' => $menu->descripcion,
                'tipo' => 'Menú 3 tiempos',
                'estilo_tipo' => 'bg-[#E8F1FA] text-[#0060A9]',
                'asociados' => $menu->opciones_habilitadas_count > 0 ? '1 menú configurado' : 'Sin configurar',
                'activo' => $menu->activo,
            ]);
        }

        foreach ($categorias as $categoria) {
            $estructura->push([
                'es_menu' => false,
                'id' => $categoria->id,
                'color' => $categoria->color_hex,
                'nombre' => $categoria->nombre,
                'descripcion' => $categoria->descripcion,
                'tipo' => 'Productos',
                'estilo_tipo' => 'bg-uch-bg text-uch-muted',
                'asociados' => $categoria->productos_count.' '.($categoria->productos_count === 1 ? 'producto' : 'productos'),
                'activo' => $categoria->activo,
                'slug' => $categoria->slug,
                'color_hex' => $categoria->color_hex,
                'orden_catalogo' => $categoria->orden,
            ]);
        }

        $totalCategorias = $categorias->count();
        $categoriasActivas = $categorias->where('activo', true)->count();

        $metricas = [
            'catalogosMenu' => $menus->count(),
            'productosIndividuales' => Producto::query()->where('activo', true)->count(),
            'visibilidad' => $totalCategorias > 0 ? (int) round(($categoriasActivas / $totalCategorias) * 100) : 0,
            'totalRegistros' => $estructura->count(),
        ];

        return view('admin.categorias.index', [
            'estructura' => $estructura,
            'metricas' => $metricas,
        ]);
    }

    public function create(): View
    {
        return view('admin.categorias.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:80',
            'slug' => 'nullable|string|max:80|unique:categorias,slug',
            'descripcion' => 'nullable|string|max:255',
            'color_hex' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'orden' => 'nullable|integer|min:0|max:9999',
            'activo' => 'boolean',
        ]);

        Categoria::create([
            'nombre' => $datos['nombre'],
            'slug' => ($datos['slug'] ?? null) ?: Str::slug($datos['nombre']),
            'descripcion' => $datos['descripcion'] ?? null,
            'color_hex' => $datos['color_hex'],
            'orden' => $datos['orden'] ?? 0,
            'activo' => $request->boolean('activo'),
        ]);

        return redirect('/admin/categorias')->with('mensaje', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria): View
    {
        return view('admin.categorias.edit', ['categoria' => $categoria]);
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:80',
            'slug' => ['required', 'string', 'max:80', Rule::unique('categorias', 'slug')->ignore($categoria->id)],
            'descripcion' => 'nullable|string|max:255',
            'color_hex' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'orden' => 'nullable|integer|min:0|max:9999',
            'activo' => 'boolean',
        ]);

        $categoria->update([
            ...$datos,
            'orden' => $datos['orden'] ?? 0,
            'activo' => $request->boolean('activo'),
        ]);

        return redirect('/admin/categorias')->with('mensaje', 'Categoría actualizada correctamente.');
    }

    public function reordenar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:categorias,id'],
        ]);

        DB::transaction(function () use ($datos): void {
            foreach (array_values($datos['ids']) as $indice => $id) {
                Categoria::query()->whereKey($id)->update(['orden' => $indice + 1]);
            }
        });

        return redirect('/admin/categorias')->with('mensaje', 'Orden del catálogo actualizado correctamente.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->productos()->where('activo', true)->exists()) {
            return back()->with('error', 'No puedes eliminar una categoría con productos activos. Desactívala o reasigna sus productos.');
        }

        $categoria->productos()->update(['categoria_id' => null]);
        $categoria->delete();

        return redirect('/admin/categorias')->with('mensaje', 'Categoría eliminada correctamente.');
    }
}
