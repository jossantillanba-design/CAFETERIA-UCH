<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'precio', 'imagen'];

    // URL lista para usar en <img>: acepta enlaces completos o rutas dentro de /public
    public function getImagenUrlAttribute(): ?string
    {
        if (!$this->imagen) {
            return null;
        }

        return preg_match('#^https?://#i', $this->imagen)
            ? $this->imagen
            : asset(ltrim($this->imagen, '/'));
    }
}
