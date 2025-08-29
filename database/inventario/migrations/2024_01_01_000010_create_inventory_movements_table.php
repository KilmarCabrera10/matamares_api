<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'inventario';

    public function up(): void
    {
        Schema::connection('inventario')->create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');

            // Referencia
            $table->string('transaction_number', 100);
            $table->uuid('transaction_type_id')->nullable();
            $table->string('reference_type', 50)->nullable(); // purchase_order, sale, adjustment, etc.
            $table->uuid('reference_id')->nullable(); // ID del documento relacionado

            // Producto y ubicación
            $table->uuid('product_id');
            $table->uuid('location_id');
            $table->uuid('batch_id')->nullable();

            // Cantidades
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 4)->default(0);

            // Balances (snapshot al momento)
            $table->decimal('balance_before', 15, 4)->default(0);
            $table->decimal('balance_after', 15, 4)->default(0);

            // Metadatos
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('transaction_type_id')->references('id')->on('transaction_types');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');

            $table->unique(['organization_id', 'transaction_number']);
        });
    }

    public function down(): void
    {
        Schema::connection('inventario')->dropIfExists('inventory_movements');
    }
};
