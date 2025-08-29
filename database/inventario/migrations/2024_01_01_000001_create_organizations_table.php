<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'inventario';

    public function up(): void
    {
        Schema::connection('inventario')->create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('slug', 100)->unique();
            $table->string('domain', 255)->nullable();
            $table->string('plan_type', 50)->default('basic'); // basic, pro, enterprise
            $table->string('status', 20)->default('active'); // active, suspended, cancelled
            $table->json('settings')->default('{}');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('inventario')->dropIfExists('organizations');
    }
};
