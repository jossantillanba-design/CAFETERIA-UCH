<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('pedidos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cliente_id')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('atendido_por')->nullable()->constrained('users')->nullOnDelete();
        $table->enum('estado', ['pendiente', 'en_preparacion', 'listo', 'entregado', 'cancelado'])->default('pendiente');
        $table->decimal('total', 8, 2)->default(0);
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
