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
        Schema::create('menu_dia_opciones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus_dia')->cascadeOnDelete();
            $table->foreignId('componente_id')->constrained('menu_componentes')->cascadeOnDelete();
            $table->decimal('precio_opcion', 10, 2)->nullable();
            $table->integer('stock_raciones')->nullable();
            $table->boolean('habilitado')->default(true);
            $table->timestamps();

            $table->unique(['menu_id', 'componente_id']);
            $table->index(['menu_id', 'habilitado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_dia_opciones');
    }
};
