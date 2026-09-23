<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = ['cliente_id', 'atendido_por', 'estado', 'total'];

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }
}