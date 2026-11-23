<?php

namespace App\Models;

use Database\Factories\MenuComponenteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuComponente extends Model
{
    /** @use HasFactory<MenuComponenteFactory> */
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion', 'tiempo', 'activo_catalogo'];

    protected $casts = [
        'activo_catalogo' => 'boolean',
    ];

    public function opciones(): HasMany
    {
        return $this->hasMany(MenuDiaOpcion::class, 'componente_id');
    }

    public function opcionesHabilitadas(): HasMany
    {
        return $this->hasMany(MenuDiaOpcion::class, 'componente_id')->where('habilitado', true);
    }

    public function scopeDelTiempo(Builder $query, string $tiempo): Builder
    {
        return $query->where('tiempo', $tiempo)
            ->where('activo_catalogo', true)
            ->orderBy('nombre');
    }
}
