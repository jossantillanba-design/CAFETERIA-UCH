<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table): void {
            $table->enum('origen', ['web', 'caja'])->default('web')->after('cliente_id');
            $table->string('cliente_nombre_caja', 120)->nullable()->after('atendido_por');
            $table->enum('tipo_consumo', ['salon', 'llevar'])->default('salon')->after('cliente_nombre_caja');
            $table->decimal('monto_recibido', 10, 2)->nullable()->after('metodo_pago');
            $table->decimal('vuelto', 10, 2)->nullable()->after('monto_recibido');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table): void {
            $table->dropColumn(['origen', 'cliente_nombre_caja', 'tipo_consumo', 'monto_recibido', 'vuelto']);
        });
    }
};
