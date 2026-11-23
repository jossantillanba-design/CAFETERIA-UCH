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
        Schema::table('productos', function (Blueprint $table): void {
            $table->foreignId('categoria_id')->nullable()->after('id')->constrained('categorias')->nullOnDelete();
            $table->string('sku', 30)->nullable()->after('nombre')->unique();
            $table->string('alergenos', 150)->nullable()->after('descripcion');
            $table->integer('stock_actual')->default(0)->after('precio');
            $table->integer('stock_minimo')->default(0)->after('stock_actual');
            $table->boolean('destacado')->default(false)->after('activo');
            $table->boolean('es_nuevo')->default(false)->after('destacado');
            $table->boolean('es_popular')->default(false)->after('es_nuevo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->dropColumn([
                'categoria_id',
                'sku',
                'alergenos',
                'stock_actual',
                'stock_minimo',
                'destacado',
                'es_nuevo',
                'es_popular',
            ]);
        });
    }
};
