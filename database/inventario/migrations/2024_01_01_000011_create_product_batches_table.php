<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'inventario';

    public function up(): void
    {
        Schema::connection('inventario')->create('product_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('product_id');
            $table->uuid('location_id');

            $table->string('batch_number', 100);
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('cost_price', 15, 4)->default(0);

            $table->date('manufactured_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->uuid('supplier_id')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers');

            $table->unique(['organization_id', 'product_id', 'batch_number']);
        });
    }

    public function down(): void
    {
        Schema::connection('inventario')->dropIfExists('product_batches');
    }
};
