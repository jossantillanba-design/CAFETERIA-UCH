<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table): void {
            $table->foreignId('producto_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Los detalles de menús heredados requieren que producto_id sea nullable.
    }
};
