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
        Schema::create('menu_componentes', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 120);
            $table->string('descripcion', 200)->nullable();
            $table->enum('tiempo', ['entrada', 'fondo', 'refresco']);
            $table->boolean('activo_catalogo')->default(true);
            $table->timestamps();

            $table->index(['tiempo', 'activo_catalogo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_componentes');
    }
};
