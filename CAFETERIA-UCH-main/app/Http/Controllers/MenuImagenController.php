<?php

namespace App\Http\Controllers;

use App\Models\MenuDia;
use App\Models\MenuImagen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuImagenController extends Controller
{
    /**
     * Sube (o reemplaza) la imagen de un plato principal habilitado en el menú.
     * No hay límite de imágenes: hay una por cada plato principal.
     */
    public function store(Request $request, MenuDia $menu): JsonResponse
    {
        $datos = $request->validate([
            'componente_id' => [
                'required',
                'integer',
                Rule::exists('menu_dia_opciones', 'componente_id')
                    ->where('menu_id', $menu->id)
                    ->where('habilitado', true),
            ],
            'imagen' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'componente_id.exists' => 'El plato debe estar habilitado y guardado en este menú.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'Solo se permiten imágenes JPG, PNG o WebP.',
            'imagen.max' => 'La imagen no puede pesar más de 2 MB.',
        ]);

        $existente = $menu->imagenes()->where('componente_id', $datos['componente_id'])->first();
        $ruta = $request->file('imagen')->store('menu-imagenes', 'public');

        if ($existente) {
            // Se borra el archivo anterior y se conserva la posición dentro del carrusel.
            \Illuminate\Support\Facades\Storage::disk('public')->delete($existente->ruta);
            $existente->update(['ruta' => $ruta]);
            $imagen = $existente;
        } else {
            $imagen = $menu->imagenes()->create([
                'componente_id' => $datos['componente_id'],
                'ruta' => $ruta,
                'orden' => ((int) $menu->imagenes()->max('orden')) + 1,
            ]);
        }

        return response()->json($this->payload($imagen), 201);
    }

    public function destroy(MenuImagen $imagen): JsonResponse
    {
        $imagen->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Guarda el orden del carrusel. Recibe los componente_id en el orden deseado.
     */
    public function reordenar(Request $request, MenuDia $menu): JsonResponse
    {
        $datos = $request->validate([
            'orden' => 'required|array',
            'orden.*' => 'integer|distinct',
        ]);

        foreach (array_values($datos['orden']) as $posicion => $componenteId) {
            $menu->imagenes()->where('componente_id', $componenteId)->update(['orden' => $posicion + 1]);
        }

        return response()->json(['ok' => true]);
    }

    private function payload(MenuImagen $imagen): array
    {
        return [
            'id' => $imagen->id,
            'componente_id' => $imagen->componente_id,
            'url' => $imagen->url,
            'orden' => $imagen->orden,
        ];
    }
}
