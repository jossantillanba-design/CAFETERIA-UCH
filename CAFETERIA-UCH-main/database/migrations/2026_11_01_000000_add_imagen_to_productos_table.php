<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('productos', 'imagen')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->string('imagen', 500)->nullable()->after('precio');
            });
        }

        // Imagen inicial para los productos que ya existen (según su nombre)
        $imagenes = [
            'Café americano' => 'cafe-americano',
            'Pan con huevo revueto' => 'pan-con-huevo',
            'Pan con pollo' => 'pan-con-pollo',
            'Pan con salchicha huachana' => 'pan-con-salchicha',
            'Pan con queso' => 'pan-con-queso',
            'Quinua' => 'quinua',
            'Empanada de queso' => 'empanada-queso',
            'Empanada de pollo' => 'empanada-pollo',
            'Empanada de carne' => 'empanada-carne',
            'Brownie' => 'brownie',
        ];

        foreach ($imagenes as $nombre => $archivo) {
            DB::table('productos')
                ->where('nombre', $nombre)
                ->whereNull('imagen')
                ->update(['imagen' => "images/productos/{$archivo}.svg"]);
        }
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('imagen');
        });
    }
};
