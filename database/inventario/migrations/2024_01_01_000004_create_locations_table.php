<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'inventario';

    public function up(): void
    {
        Schema::connection('inventario')->create('locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name', 255);
            $table->string('code', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('type', 50)->default('warehouse'); // warehouse, store, kitchen, etc.
            $table->boolean('is_active')->default(true);
            $table->json('settings')->default('{}');
            $table->timestamps();
            
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::connection('inventario')->dropIfExists('locations');
    }
};
