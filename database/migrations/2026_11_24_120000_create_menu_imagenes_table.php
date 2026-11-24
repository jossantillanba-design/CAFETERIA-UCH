<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Imágenes del carrusel de cada menú del día: una por plato principal.
     */
    public function up(): void
    {
        Schema::create('menu_imagenes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus_dia')->cascadeOnDelete();
            $table->foreignId('componente_id')->constrained('menu_componentes')->cascadeOnDelete();
            $table->string('ruta');
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->unique(['menu_id', 'componente_id']);
            $table->index(['menu_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_imagenes');
    }
};
