<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $imagenes = [
            '%americano%' => 'images/productos/cafe-americano.jpg',
            '%huevo%'     => 'images/productos/pan-con-huevo.jpg',
            '%pollo%'     => 'images/productos/pan-con-pollo.jpg',
            '%salchicha%' => 'images/productos/pan-con-salchicha-huachana.jpg',
        ];

        foreach ($imagenes as $patron => $ruta) {
            DB::table('productos')
                ->where('nombre', 'like', $patron)
                ->where('nombre', 'not like', 'Empanada%')
                ->update(['imagen' => $ruta]);
        }

        DB::table('productos')->where('nombre', 'Pan con queso')
            ->update(['imagen' => 'images/productos/pan-con-queso.jpg']);

        // Los que aún usan imagen de relleno (.svg)
        $svg = [
            'Quinua'            => 'quinua.svg',
            'Empanada de queso' => 'empanada-queso.svg',
            'Empanada de pollo' => 'empanada-pollo.svg',
            'Empanada de carne' => 'empanada-carne.svg',
            'Brownie'           => 'brownie.svg',
        ];

        foreach ($svg as $nombre => $archivo) {
            DB::table('productos')->where('nombre', $nombre)
                ->update(['imagen' => 'images/productos/' . $archivo]);
        }
    }

    public function down(): void
    {
        //
    }
};
