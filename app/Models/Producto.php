<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Producto extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'precio', 'imagen'];

    // Imagen por defecto según el nombre, para cuando el campo "imagen" está vacío
    protected static array $imagenesPorNombre = [
        'cafe-americano'             => 'cafe-americano.jpg',
        'pan-con-huevo-revueto'      => 'pan-con-huevo.jpg',
        'pan-con-huevo-revuelto'     => 'pan-con-huevo.jpg',
        'pan-con-pollo'              => 'pan-con-pollo.jpg',
        'pan-con-salchicha-huachana' => 'pan-con-salchicha-huachana.jpg',
        'pan-con-queso'              => 'pan-con-queso.jpg',
        'quinua'                     => 'quinua.svg',
        'empanada-de-queso'          => 'empanada-queso.svg',
        'empanada-de-pollo'          => 'empanada-pollo.svg',
        'empanada-de-carne'          => 'empanada-carne.svg',
        'brownie'                    => 'brownie.svg',
    ];

    // URL para <img>: enlace completo o ruta relativa dentro de /public
    public function getImagenUrlAttribute(): ?string
    {
        $imagen = $this->imagen;

        if (!$imagen) {
            $archivo = static::$imagenesPorNombre[Str::slug($this->nombre)] ?? null;

            if (!$archivo) {
                return null;
            }

            $imagen = 'images/productos/' . $archivo;
        }

        return preg_match('#^https?://#i', $imagen)
            ? $imagen
            : '/' . ltrim($imagen, '/');
    }
}
