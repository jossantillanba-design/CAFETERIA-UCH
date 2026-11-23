<?php

namespace App\Models;

use Database\Factories\CategoriaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    /** @use HasFactory<CategoriaFactory> */
    use HasFactory;

    protected $fillable = ['nombre', 'slug', 'descripcion', 'color_hex', 'orden', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true)->orderBy('orden')->orderBy('nombre');
    }
}
