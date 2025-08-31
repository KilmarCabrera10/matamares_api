<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'inventario';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::connection('inventario')->table('users')
            ->where('email', 'admin@demo.inventario.com')
            ->update(['email' => 'admin@demo.com']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('inventario')->table('users')
            ->where('email', 'admin@demo.com')
            ->update(['email' => 'admin@demo.inventario.com']);
    }
};
