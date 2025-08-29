<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'inventario';

    public function up(): void
    {
        Schema::connection('inventario')->create('organization_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('user_id');
            $table->string('role', 50)->default('member'); // owner, admin, manager, member, viewer
            $table->json('permissions')->default('{}');
            $table->uuid('invited_by')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->string('status', 20)->default('active');
            
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('users');
            
            $table->unique(['organization_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('inventario')->dropIfExists('organization_members');
    }
};
