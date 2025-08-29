<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'inventario';

    public function up(): void
    {
        // Agregar foreign key para batch_id en inventory_movements
        Schema::connection('inventario')->table('inventory_movements', function (Blueprint $table) {
            $table->foreign('batch_id')->references('id')->on('product_batches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection('inventario')->table('inventory_movements', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
        });
    }
};
