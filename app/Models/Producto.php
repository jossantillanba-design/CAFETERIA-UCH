<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'precio', 'imagen'];

    // URL para <img>: enlace completo o ruta relativa dentro de /public (relativa para que funcione en cualquier dominio)
    public function getImagenUrlAttribute(): ?string
    {
        if (!$this->imagen) {
            return null;
        }

        return preg_match('#^https?://#i', $this->imagen)
            ? $this->imagen
            : '/' . ltrim($this->imagen, '/');
    }
}
