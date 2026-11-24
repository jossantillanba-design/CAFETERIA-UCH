<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    public const ORIGEN_WEB = 'web';

    public const ORIGEN_CAJA = 'caja';

    public const METODO_EFECTIVO = 'efectivo';

    public const METODO_YAPE_PLIN = 'yape_plin';

    public const METODO_TARJETA = 'tarjeta';

    public const TIPO_CONSUMO_SALON = 'salon';

    public const TIPO_CONSUMO_LLEVAR = 'llevar';

    protected $fillable = [
        'cliente_id',
        'atendido_por',
        'origen',
        'cliente_nombre_caja',
        'tipo_consumo',
        'estado',
        'total',
        'pagado',
        'metodo_pago',
        'monto_recibido',
        'vuelto',
        'codigo_transaccion',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'pagado' => 'boolean',
        'monto_recibido' => 'decimal:2',
        'vuelto' => 'decimal:2',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function atendidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }
}
