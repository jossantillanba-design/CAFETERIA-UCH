<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePedido extends Model
{
    protected $fillable = [
        'pedido_id',
        'producto_id',
        'tipo_item',
        'menu_id',
        'entrada_id',
        'fondo_id',
        'refresco_id',
        'detalle_menu',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(MenuDia::class, 'menu_id');
    }

    public function entrada(): BelongsTo
    {
        return $this->belongsTo(MenuComponente::class, 'entrada_id');
    }

    public function fondo(): BelongsTo
    {
        return $this->belongsTo(MenuComponente::class, 'fondo_id');
    }

    public function refresco(): BelongsTo
    {
        return $this->belongsTo(MenuComponente::class, 'refresco_id');
    }
}
