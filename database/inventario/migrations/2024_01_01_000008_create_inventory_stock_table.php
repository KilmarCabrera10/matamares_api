<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'inventario';

    public function up(): void
    {
        Schema::connection('inventario')->create('inventory_stock', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('product_id');
            $table->uuid('location_id');

            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('reserved_quantity', 15, 4)->default(0); // Para órdenes pendientes
            
            $table->decimal('average_cost', 15, 4)->default(0); // Costo promedio ponderado
            $table->timestamp('last_movement_at')->nullable();

            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');

            $table->unique(['product_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('inventario')->dropIfExists('inventory_stock');
    }
};
