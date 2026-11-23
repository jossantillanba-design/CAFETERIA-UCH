<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menus_dia', function (Blueprint $table): void {
            $table->id();
            $table->enum('tipo_menu', ['universitario', 'ejecutivo', 'dieta'])->unique();
            $table->string('titulo', 100);
            $table->string('descripcion', 255)->nullable();
            $table->decimal('precio_base', 10, 2)->default(0);
            $table->boolean('es_precio_variable')->default(false);
            $table->date('fecha_vigencia')->nullable();
            $table->string('imagen', 500)->nullable();
            $table->boolean('activo')->default(false);
            $table->boolean('destacado')->default(false);
            $table->string('etiqueta', 40)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus_dia');
    }
};
