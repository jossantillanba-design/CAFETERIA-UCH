<?php

namespace App\Models;

use Database\Factories\InventarioMovimientoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioMovimiento extends Model
{
    /** @use HasFactory<InventarioMovimientoFactory> */
    use HasFactory;

    public const TIPO_PRODUCTO = 'producto';

    public const TIPO_OPCION_MENU = 'opcion_menu';

    public const OPERACION_INGRESO = 'ingreso';

    public const OPERACION_SALIDA = 'salida';

    public const OPERACION_VENTA = 'venta';

    protected $fillable = [
        'tipo_item',
        'item_id',
        'operacion',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'motivo_categoria',
        'comentario',
        'usuario_id',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_anterior' => 'integer',
        'stock_nuevo' => 'integer',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
