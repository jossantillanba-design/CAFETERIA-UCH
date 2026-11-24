<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MenuImagen extends Model
{
    protected $table = 'menu_imagenes';

    protected $fillable = ['menu_id', 'componente_id', 'ruta', 'orden'];

    protected $casts = [
        'orden' => 'integer',
    ];

    protected static function booted(): void
    {
        // Al borrar el registro (o el menú/componente en cascada vía modelo), se limpia el archivo.
        static::deleting(function (MenuImagen $imagen): void {
            Storage::disk('public')->delete($imagen->ruta);
        });
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(MenuDia::class, 'menu_id');
    }

    public function componente(): BelongsTo
    {
        return $this->belongsTo(MenuComponente::class, 'componente_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->ruta);
    }
}
