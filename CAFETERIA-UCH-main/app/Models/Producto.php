<?php

namespace App\Models;

use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

    protected $fillable = [
        'categoria_id',
        'nombre',
        'sku',
        'descripcion',
        'alergenos',
        'precio',
        'stock_actual',
        'stock_minimo',
        'imagen',
        'activo',
        'destacado',
        'es_nuevo',
        'es_popular',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
        'activo' => 'boolean',
        'destacado' => 'boolean',
        'es_nuevo' => 'boolean',
        'es_popular' => 'boolean',
    ];

    protected static array $imagenesPorNombre = [
        'cafe-americano' => 'cafe-americano.jpg',
        'pan-con-huevo-revueto' => 'pan-con-huevo.jpg',
        'pan-con-huevo-revuelto' => 'pan-con-huevo.jpg',
        'pan-con-pollo' => 'pan-con-pollo.jpg',
        'pan-con-salchicha-huachana' => 'pan-con-salchicha-huachana.jpg',
        'pan-con-queso' => 'pan-con-queso.jpg',
        'quinua' => 'quinua.svg',
        'empanada-de-queso' => 'empanada-queso.svg',
        'empanada-de-pollo' => 'empanada-pollo.svg',
        'empanada-de-carne' => 'empanada-carne.svg',
        'brownie' => 'brownie.svg',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(InventarioMovimiento::class, 'item_id')
            ->where('tipo_item', InventarioMovimiento::TIPO_PRODUCTO);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeStockBajo(Builder $query): Builder
    {
        return $query
            ->where('activo', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo');
    }

    public function getEstadoStockAttribute(): string
    {
        if ($this->stock_actual <= 0) {
            return 'Agotado';
        }

        return $this->stock_actual <= $this->stock_minimo ? 'Quedan pocos' : 'Disponible';
    }

    public function getImagenUrlAttribute(): ?string
    {
        $imagen = $this->imagen;

        if (! $imagen) {
            $archivo = static::$imagenesPorNombre[Str::slug($this->nombre)] ?? null;

            if (! $archivo) {
                return null;
            }

            $imagen = 'images/productos/'.$archivo;
        }

        return preg_match('#^https?://#i', $imagen)
            ? $imagen
            : '/'.ltrim($imagen, '/');
    }
}
