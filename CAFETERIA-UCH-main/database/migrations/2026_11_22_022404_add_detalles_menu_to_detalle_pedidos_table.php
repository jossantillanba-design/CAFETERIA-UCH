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
        Schema::table('detalle_pedidos', function (Blueprint $table): void {
            $table->string('tipo_item', 20)->default('producto')->after('id');
            $table->foreignId('menu_id')->nullable()->after('producto_id')->constrained('menus_dia')->nullOnDelete();
            $table->foreignId('entrada_id')->nullable()->after('menu_id')->constrained('menu_componentes')->nullOnDelete();
            $table->foreignId('fondo_id')->nullable()->after('entrada_id')->constrained('menu_componentes')->nullOnDelete();
            $table->foreignId('refresco_id')->nullable()->after('fondo_id')->constrained('menu_componentes')->nullOnDelete();
            $table->text('detalle_menu')->nullable()->after('refresco_id');
            $table->decimal('subtotal', 10, 2)->nullable()->after('precio_unitario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table): void {
            $table->dropColumn([
                'tipo_item',
                'menu_id',
                'entrada_id',
                'fondo_id',
                'refresco_id',
                'detalle_menu',
                'subtotal',
            ]);
        });
    }
};
