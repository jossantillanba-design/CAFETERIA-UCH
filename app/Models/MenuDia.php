<?php

namespace App\Models;

use Database\Factories\MenuDiaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class MenuDia extends Model
{
    /** @use HasFactory<MenuDiaFactory> */
    use HasFactory;

    protected $table = 'menus_dia';

    protected $fillable = [
        'tipo_menu',
        'titulo',
        'descripcion',
        'precio_base',
        'es_precio_variable',
        'fecha_vigencia',
        'imagen',
        'activo',
        'destacado',
        'etiqueta',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'es_precio_variable' => 'boolean',
        'fecha_vigencia' => 'date',
        'activo' => 'boolean',
        'destacado' => 'boolean',
    ];

    public function opciones(): HasMany
    {
        return $this->hasMany(MenuDiaOpcion::class, 'menu_id');
    }

    public function opcionesHabilitadas(): HasMany
    {
        return $this->hasMany(MenuDiaOpcion::class, 'menu_id')->where('habilitado', true);
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(MenuImagen::class, 'menu_id')->orderBy('orden')->orderBy('id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function getAgotadoAttribute(): bool
    {
        $fondos = $this->opcionesFondo();

        if ($fondos->isEmpty()) {
            return true;
        }

        if ($fondos->contains(fn (MenuDiaOpcion $opcion): bool => $opcion->stock_raciones === null)) {
            return false;
        }

        return $fondos->sum(fn (MenuDiaOpcion $opcion): int => (int) $opcion->stock_raciones) <= 0;
    }

    public function precioVisible(): float
    {
        if (! $this->es_precio_variable) {
            return (float) $this->precio_base;
        }

        $precios = $this->opcionesFondo()
            ->filter(fn (MenuDiaOpcion $opcion): bool => $opcion->stock_raciones === null || $opcion->stock_raciones > 0)
            ->pluck('precio_opcion')
            ->filter(fn ($precio): bool => $precio !== null)
            ->map(fn ($precio): float => (float) $precio);

        return $precios->min() ?? (float) $this->precio_base;
    }

    private function opcionesFondo(): Collection
    {
        $opciones = $this->relationLoaded('opciones')
            ? $this->opciones
            : $this->opciones()->with('componente')->get();

        return $opciones
            ->filter(fn (MenuDiaOpcion $opcion): bool => $opcion->habilitado && $opcion->componente?->tiempo === 'fondo')
            ->values();
    }
}
