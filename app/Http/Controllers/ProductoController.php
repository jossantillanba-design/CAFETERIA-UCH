<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\InventarioMovimiento;
use App\Models\MenuDia;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductoController extends Controller
{
    // Menú público
    public function index(): View
    {
        $productos = Producto::with('categoria')
            ->activos()
            ->orderByDesc('destacado')
            ->orderBy('nombre')
            ->get();

        $categorias = Categoria::activas()->get();

        $ordenMenus = ['universitario' => 0, 'ejecutivo' => 1, 'dieta' => 2];
        $menus = MenuDia::with(['opciones.componente'])
            ->activos()
            ->get()
            ->sortBy(fn (MenuDia $menu): int => $ordenMenus[$menu->tipo_menu] ?? 99)
            ->values();

        $iconosPorCategoria = [
            'bebidas' => 'local_cafe',
            'sanguches' => 'lunch_dining',
            'postres' => 'icecream',
            'menu-del-dia' => 'dinner_dining',
        ];

        $categoriasMenu = $categorias->map(fn (Categoria $categoria): array => [
            'nombre' => $categoria->nombre,
            'slug' => $categoria->slug,
            'color' => $categoria->color_hex,
            'icono' => $iconosPorCategoria[$categoria->slug] ?? 'restaurant',
        ])->prepend([
            'nombre' => 'Todos',
            'slug' => 'todos',
            'color' => '#0066B3',
            'icono' => 'restaurant',
        ])->values();

        $productosMenu = $productos->map(fn (Producto $producto): array => [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'descripcion' => $producto->descripcion ?? '',
            'precio' => (float) $producto->precio,
            'imagen' => $producto->imagen_url,
            'categoria' => $producto->categoria->nombre ?? 'Menú del día',
            'color' => $producto->categoria->color_hex ?? '#0066B3',
            'popular' => (bool) $producto->es_popular,
            'rapido' => $producto->categoria?->slug === 'bebidas',
            'stock' => (int) $producto->stock_actual,
            'minimo' => (int) $producto->stock_minimo,
            'disponible' => $producto->stock_actual > 0,
        ])->values();

        $menusMenu = $menus->map(function (MenuDia $menu): array {
            $opcionesPorTiempo = [];

            foreach (['entrada', 'fondo', 'refresco'] as $tiempo) {
                $opcionesPorTiempo[$tiempo] = $menu->opciones
                    ->filter(fn ($opcion): bool => $opcion->habilitado && $opcion->componente?->tiempo === $tiempo)
                    ->map(fn ($opcion): array => [
                        'id' => $opcion->componente_id,
                        'opcion_id' => $opcion->id,
                        'nombre' => $opcion->componente->nombre,
                        'precio' => $opcion->precio_opcion === null ? null : (float) $opcion->precio_opcion,
                        'stock' => $opcion->stock_raciones === null ? null : (int) $opcion->stock_raciones,
                    ])
                    ->values()
                    ->all();
            }

            return [
                'id' => $menu->id,
                'tipo' => $menu->tipo_menu,
                'titulo' => $menu->titulo,
                'descripcion' => $menu->descripcion ?? '',
                'etiqueta' => $menu->etiqueta,
                'imagen' => $menu->imagen
                    ? '/'.ltrim($menu->imagen, '/')
                    : asset('images/productos/quinua.svg'),
                'destacado' => (bool) $menu->destacado,
                'esPrecioVariable' => (bool) $menu->es_precio_variable,
                'precioBase' => (float) $menu->precio_base,
                'precioVisible' => $menu->precioVisible(),
                'agotado' => $menu->agotado,
                'opciones' => $opcionesPorTiempo,
            ];
        })->values();

        $menuHero = $menusMenu->firstWhere('destacado', true) ?? $menusMenu->first();

        return view('menu', compact('productosMenu', 'categoriasMenu', 'menusMenu', 'menuHero'));
    }

    // Listado en el panel de administración
    public function admin(Request $request): View
    {
        $buscar = trim($request->string('buscar')->toString());
        $categoriaFiltro = $request->string('categoria')->toString();
        $estadoFiltro = $request->string('estado')->toString();

        if (! in_array($estadoFiltro, ['disponible', 'stock-bajo', 'agotado', 'inactivo'], true)) {
            $estadoFiltro = 'all';
        }

        $consulta = Producto::with('categoria');

        if ($buscar !== '') {
            $consulta->where(function ($query) use ($buscar): void {
                $query->where('nombre', 'like', '%'.$buscar.'%')
                    ->orWhere('sku', 'like', '%'.$buscar.'%');
            });
        }

        if ($categoriaFiltro !== '' && $categoriaFiltro !== 'all') {
            $consulta->whereHas('categoria', fn ($query) => $query->where('slug', $categoriaFiltro));
        }

        match ($estadoFiltro) {
            'disponible' => $consulta->where('activo', true)->whereColumn('stock_actual', '>', 'stock_minimo'),
            'stock-bajo' => $consulta->where('activo', true)
                ->where('stock_actual', '>', 0)
                ->whereColumn('stock_actual', '<=', 'stock_minimo'),
            'agotado' => $consulta->where('stock_actual', '<=', 0),
            'inactivo' => $consulta->where('activo', false),
            default => null,
        };

        $productos = $consulta->orderBy('nombre')->paginate(8)->withQueryString();

        $metricas = [
            'total' => Producto::count(),
            'categoriasActivas' => Categoria::activas()->count(),
            'disponibles' => Producto::where('activo', true)
                ->whereColumn('stock_actual', '>', 'stock_minimo')
                ->count(),
            'stockCritico' => Producto::stockBajo()->where('stock_actual', '>', 0)->count(),
            'agotadosPausados' => Producto::where(function ($query): void {
                $query->where('stock_actual', '<=', 0)
                    ->orWhere('activo', false);
            })->count(),
        ];

        return view('admin.productos.index', [
            'productos' => $productos,
            'metricas' => $metricas,
            'categoriasFiltro' => Categoria::orderBy('nombre')->get(['id', 'nombre', 'slug']),
            'buscar' => $buscar,
            'categoriaFiltro' => $categoriaFiltro === 'all' ? '' : $categoriaFiltro,
            'estadoFiltro' => $estadoFiltro,
        ]);
    }

    // Formulario para crear
    public function create(): View
    {
        return view('admin.productos.create', ['categorias' => Categoria::activas()->get()]);
    }

    // Guarda el nuevo producto y su stock inicial
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'categoria_id' => 'nullable|exists:categorias,id',
            'nombre' => 'required|string|max:120',
            'sku' => 'nullable|string|max:30|unique:productos,sku',
            'descripcion' => 'nullable|string',
            'alergenos' => 'nullable|string|max:150',
            'precio' => 'required|numeric|min:0',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'imagen' => 'nullable|string|max:500',
            'imagen_archivo' => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:2048',
            'activo' => 'boolean',
            'destacado' => 'boolean',
            'es_nuevo' => 'boolean',
            'es_popular' => 'boolean',
        ]);

        unset($datos['imagen_archivo']);

        $imagenSubida = $this->guardarImagenProducto($request);

        DB::transaction(function () use ($datos, $request, $imagenSubida): void {
            $producto = Producto::create([
                ...$datos,
                'sku' => $datos['sku'] ?: $this->generarSku((int) ($datos['categoria_id'] ?? 0)),
                'descripcion' => $datos['descripcion'] ?? null,
                'alergenos' => $datos['alergenos'] ?? null,
                'imagen' => $imagenSubida ?? ($datos['imagen'] ?? null),
                'activo' => $request->boolean('activo'),
                'destacado' => $request->boolean('destacado'),
                'es_nuevo' => $request->boolean('es_nuevo'),
                'es_popular' => $request->boolean('es_popular'),
            ]);

            if ($producto->stock_actual > 0) {
                InventarioMovimiento::create([
                    'tipo_item' => InventarioMovimiento::TIPO_PRODUCTO,
                    'item_id' => $producto->id,
                    'operacion' => InventarioMovimiento::OPERACION_INGRESO,
                    'cantidad' => $producto->stock_actual,
                    'stock_anterior' => 0,
                    'stock_nuevo' => $producto->stock_actual,
                    'motivo_categoria' => 'Reposición',
                    'comentario' => 'Stock inicial de creación',
                    'usuario_id' => auth()->id(),
                ]);
            }
        });

        return redirect('/admin/productos')->with('mensaje', 'Producto creado correctamente.');
    }

    // Formulario para editar
    public function edit(Producto $producto): View
    {
        return view('admin.productos.edit', [
            'producto' => $producto,
            'categorias' => Categoria::activas()->get(),
        ]);
    }

    // Guarda los cambios. El stock se ajusta solo desde Inventario.
    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $datos = $request->validate([
            'categoria_id' => 'nullable|exists:categorias,id',
            'nombre' => 'required|string|max:120',
            'sku' => ['nullable', 'string', 'max:30', Rule::unique('productos', 'sku')->ignore($producto->id)],
            'descripcion' => 'nullable|string',
            'alergenos' => 'nullable|string|max:150',
            'precio' => 'required|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'imagen' => 'nullable|string|max:500',
            'imagen_archivo' => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:2048',
            'activo' => 'boolean',
            'destacado' => 'boolean',
            'es_nuevo' => 'boolean',
            'es_popular' => 'boolean',
        ]);

        unset($datos['imagen_archivo']);

        $imagenSubida = $this->guardarImagenProducto($request);

        $producto->update([
            ...$datos,
            'sku' => ($datos['sku'] ?? null) ?: $this->generarSku((int) ($datos['categoria_id'] ?? 0)),
            'descripcion' => $datos['descripcion'] ?? null,
            'alergenos' => $datos['alergenos'] ?? null,
            'imagen' => $imagenSubida ?? ($datos['imagen'] ?? $producto->imagen),
            'activo' => $request->boolean('activo'),
            'destacado' => $request->boolean('destacado'),
            'es_nuevo' => $request->boolean('es_nuevo'),
            'es_popular' => $request->boolean('es_popular'),
        ]);

        return redirect('/admin/productos')->with('mensaje', 'Producto actualizado correctamente.');
    }

    // Activa o desactiva un producto
    public function toggle(Producto $producto): RedirectResponse
    {
        $producto->update(['activo' => ! $producto->activo]);

        $mensaje = $producto->activo ? 'Producto activado.' : 'Producto desactivado.';

        return redirect('/admin/productos')->with('mensaje', $mensaje);
    }

    private function guardarImagenProducto(Request $request): ?string
    {
        if (! $request->hasFile('imagen_archivo')) {
            return null;
        }

        $archivo = $request->file('imagen_archivo');
        $nombreBase = Str::slug(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME));
        $nombreArchivo = $nombreBase.'-'.Str::random(8).'.'.$archivo->getClientOriginalExtension();
        $directorio = public_path('images/productos');

        File::ensureDirectoryExists($directorio);
        $archivo->move($directorio, $nombreArchivo);

        return 'images/productos/'.$nombreArchivo;
    }

    private function generarSku(int $categoriaId): string
    {
        $categoria = Categoria::find($categoriaId);

        $prefijos = [
            'bebidas' => 'BEB',
            'sanguches' => 'SAN',
            'postres' => 'POS',
            'menu-del-dia' => 'MEN',
        ];

        $prefijo = $prefijos[$categoria?->slug ?? '']
            ?? strtoupper(Str::substr(Str::slug($categoria?->nombre ?? 'producto'), 0, 3));

        $correlativo = Producto::where('categoria_id', $categoriaId)->count() + 1;

        return sprintf('%s-%03d', $prefijo, $correlativo);
    }
}
