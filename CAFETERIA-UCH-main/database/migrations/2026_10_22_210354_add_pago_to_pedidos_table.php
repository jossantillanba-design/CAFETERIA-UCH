<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('pedidos', function (Blueprint $table) {
        $table->boolean('pagado')->default(false);
        $table->string('metodo_pago')->nullable();
    });
}

public function down(): void
{
    Schema::table('pedidos', function (Blueprint $table) {
        $table->dropColumn(['pagado', 'metodo_pago']);
    });
}
};