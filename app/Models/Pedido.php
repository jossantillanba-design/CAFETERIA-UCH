<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $fillable = [
        'cliente_id',
        'atendido_por',
        'estado',
        'total',
        'pagado',
        'metodo_pago',
        'codigo_transaccion',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'pagado' => 'boolean',
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
