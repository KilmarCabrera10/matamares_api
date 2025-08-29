<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'inventario';

    public function up(): void
    {
        Schema::connection('inventario')->create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('parent_id')->nullable();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('code', 50)->nullable();
            $table->string('color', 7)->nullable(); // Hex color
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            // La foreign key para parent_id se agregará después
        });

        // Agregar la foreign key auto-referencial después de crear la tabla
        Schema::connection('inventario')->table('categories', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection('inventario')->table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
        
        Schema::connection('inventario')->dropIfExists('categories');
    }
};
