<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'inventario';

    public function up(): void
    {
        Schema::connection('inventario')->create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('category_id')->nullable();
            $table->uuid('supplier_id')->nullable();
            $table->string('sku', 100);
            $table->string('barcode', 100)->nullable();
            $table->string('name', 255);
            $table->text('description')->nullable();

            // Unidades de medida
            $table->string('unit_type', 50); // piece, weight, volume, length, area
            $table->string('unit_name', 50); // kg, liters, pieces, meters, etc.
            $table->integer('unit_precision')->default(2); // decimales

            // Costos y precios
            $table->decimal('cost_price', 15, 4)->default(0);
            $table->decimal('selling_price', 15, 4)->default(0);
            $table->string('currency', 3)->default('USD');

            // Control de stock
            $table->boolean('track_inventory')->default(true);
            $table->decimal('min_stock', 15, 4)->default(0);
            $table->decimal('max_stock', 15, 4)->nullable();
            $table->decimal('reorder_point', 15, 4)->nullable();
            $table->decimal('reorder_quantity', 15, 4)->nullable();

            // Fechas y lotes
            $table->boolean('track_expiry')->default(false);
            $table->boolean('track_batches')->default(false);
            $table->integer('shelf_life_days')->nullable();

            // Estados
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sellable')->default(true);
            $table->boolean('is_purchasable')->default(true);

            // Metadatos extensibles
            $table->json('attributes')->default('{}');

            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users');
            
            $table->unique(['organization_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::connection('inventario')->dropIfExists('products');
    }
};
