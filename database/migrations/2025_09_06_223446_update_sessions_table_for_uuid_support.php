<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, eliminamos los datos existentes en sessions para evitar conflictos
        DB::table('sessions')->truncate();
        
        Schema::table('sessions', function (Blueprint $table) {
            // Eliminar la columna user_id actual
            $table->dropColumn('user_id');
        });
        
        Schema::table('sessions', function (Blueprint $table) {
            // Crear la nueva columna user_id como uuid
            $table->uuid('user_id')->nullable()->index()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Eliminar la columna uuid
            $table->dropColumn('user_id');
        });
        
        Schema::table('sessions', function (Blueprint $table) {
            // Recrear como bigint
            $table->foreignId('user_id')->nullable()->index()->after('id');
        });
    }
};
