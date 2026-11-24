<?php

namespace App\Models;

use Database\Factories\MenuDiaOpcionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuDiaOpcion extends Model
{
    /** @use HasFactory<MenuDiaOpcionFactory> */
    use HasFactory;

    protected $table = 'menu_dia_opciones';

    protected $fillable = [
        'menu_id',
        'componente_id',
        'precio_opcion',
        'stock_raciones',
        'habilitado',
    ];

    protected $casts = [
        'precio_opcion' => 'decimal:2',
        'stock_raciones' => 'integer',
        'habilitado' => 'boolean',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(MenuDia::class, 'menu_id');
    }

    public function componente(): BelongsTo
    {
        return $this->belongsTo(MenuComponente::class, 'componente_id');
    }

    public function scopeHabilitadas(Builder $query): Builder
    {
        return $query->where('habilitado', true);
    }

    public function scopeStockBajo(Builder $query): Builder
    {
        return $query
            ->whereNotNull('stock_raciones')
            ->where('stock_raciones', '<=', 5);
    }
}
