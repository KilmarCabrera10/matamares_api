<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'inventario';

    public function up(): void
    {
        Schema::connection('inventario')->create('transaction_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->string('code', 50);
            $table->string('name', 255);
            $table->string('category', 50); // in, out, adjustment, transfer
            $table->boolean('affects_cost')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_system')->default(false); // Para tipos predefinidos
            $table->boolean('is_active')->default(true);

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            
            $table->unique(['organization_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::connection('inventario')->dropIfExists('transaction_types');
    }
};
