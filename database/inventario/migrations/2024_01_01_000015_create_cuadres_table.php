<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('inventario')->create('cuadres', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->date('fecha');
            $table->decimal('saldo_anterior', 15, 4)->default(0);
            $table->decimal('ingresos_efectivo', 15, 4)->default(0);
            $table->decimal('ingresos_transferencia', 15, 4)->default(0);
            $table->decimal('ingresos_tarjeta', 15, 4)->default(0);
            $table->decimal('egresos_efectivo', 15, 4)->default(0);
            $table->decimal('egresos_transferencia', 15, 4)->default(0);
            $table->decimal('egresos_tarjeta', 15, 4)->default(0);
            $table->decimal('saldo_calculado', 15, 4)->storedAs('saldo_anterior + ingresos_efectivo + ingresos_transferencia + ingresos_tarjeta - egresos_efectivo - egresos_transferencia - egresos_tarjeta');
            $table->decimal('saldo_fisico', 15, 4)->nullable();
            $table->decimal('diferencia', 15, 4)->nullable()->storedAs('saldo_fisico - (saldo_anterior + ingresos_efectivo + ingresos_transferencia + ingresos_tarjeta - egresos_efectivo - egresos_transferencia - egresos_tarjeta)');
            $table->text('observaciones')->nullable();
            $table->boolean('cerrado')->default(false);
            $table->uuid('creado_por')->nullable();
            $table->uuid('cerrado_por')->nullable();
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('creado_por')->references('id')->on('users');
            $table->foreign('cerrado_por')->references('id')->on('users');
            
            $table->unique(['organization_id', 'fecha']);
            $table->index(['organization_id', 'fecha']);
            $table->index(['organization_id', 'cerrado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('inventario')->dropIfExists('cuadres');
    }
};
