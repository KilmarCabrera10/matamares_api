<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'inventario';

    public function up(): void
    {
        // Agregar índices para mejor performance
        Schema::connection('inventario')->table('organizations', function (Blueprint $table) {
            $table->index('slug');
        });

        Schema::connection('inventario')->table('organization_members', function (Blueprint $table) {
            $table->index(['organization_id', 'user_id']);
        });

        Schema::connection('inventario')->table('products', function (Blueprint $table) {
            $table->index(['organization_id', 'sku']);
            $table->index(['organization_id', 'category_id']);
        });

        Schema::connection('inventario')->table('inventory_stock', function (Blueprint $table) {
            $table->index(['product_id', 'location_id']);
        });

        Schema::connection('inventario')->table('inventory_movements', function (Blueprint $table) {
            $table->index(['organization_id', 'product_id']);
            $table->index(['created_at']);
        });

        // Agregar índice de búsqueda de texto completo para productos (PostgreSQL)
        DB::connection('inventario')->statement("CREATE INDEX idx_products_name_search ON products USING gin(to_tsvector('spanish', name))");
    }

    public function down(): void
    {
        DB::connection('inventario')->statement("DROP INDEX IF EXISTS idx_products_name_search");

        Schema::connection('inventario')->table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'product_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::connection('inventario')->table('inventory_stock', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'location_id']);
        });

        Schema::connection('inventario')->table('products', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'sku']);
            $table->dropIndex(['organization_id', 'category_id']);
        });

        Schema::connection('inventario')->table('organization_members', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'user_id']);
        });

        Schema::connection('inventario')->table('organizations', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });
    }
};
