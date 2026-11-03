<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    // Menú público
    public function index()
    {
        $productos = Producto::all();
        return view('menu', ['productos' => $productos]);
    }

    // Listado en el panel de administración
    public function admin()
    {
        $productos = Producto::all();
        return view('admin.productos.index', ['productos' => $productos]);
    }

    // Formulario para crear
    public function create()
    {
        return view('admin.productos.create');
    }

    // Guarda el nuevo producto
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|string|max:500',
        ]);

        $datos = $request->all();
        if (!\Illuminate\Support\Facades\Schema::hasColumn('productos', 'imagen')) {
            unset($datos['imagen']);
        }
        Producto::create($datos);

        return redirect('/admin/productos')->with('mensaje', 'Producto creado correctamente.');
    }

    // Formulario para editar
    public function edit(Producto $producto)
    {
        return view('admin.productos.edit', ['producto' => $producto]);
    }

    // Guarda los cambios
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|string|max:500',
        ]);

        $datos = $request->all();
        if (!\Illuminate\Support\Facades\Schema::hasColumn('productos', 'imagen')) {
            unset($datos['imagen']);
        }
        $producto->update($datos);

        return redirect('/admin/productos')->with('mensaje', 'Producto actualizado correctamente.');
    }

    // Elimina un producto
    public function destroy(Producto $producto)
    {
        $producto->delete();

        return redirect('/admin/productos')->with('mensaje', 'Producto eliminado.');
    }
}