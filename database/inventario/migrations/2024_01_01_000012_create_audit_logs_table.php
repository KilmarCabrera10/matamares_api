<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'inventario';

    public function up(): void
    {
        Schema::connection('inventario')->create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');

            $table->string('table_name', 100);
            $table->uuid('record_id');
            $table->string('action', 20); // INSERT, UPDATE, DELETE

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('changed_fields')->nullable();

            $table->uuid('user_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');

            $table->index(['organization_id', 'table_name', 'record_id']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::connection('inventario')->dropIfExists('audit_logs');
    }
};
