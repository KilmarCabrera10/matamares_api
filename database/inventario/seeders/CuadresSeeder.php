<?php

namespace Database\Inventario\Seeders;

use Illuminate\Database\Seeder;
use App\Projects\Inventario\Models\Cuadre;
use App\Projects\Inventario\Models\Organization;
use Carbon\Carbon;

class CuadresSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener la primera organización para las pruebas
        $organization = Organization::first();
        
        if (!$organization) {
            return;
        }

        // Crear algunos cuadres de prueba
        $fechas = [
            Carbon::now()->subDays(3),
            Carbon::now()->subDays(2),
            Carbon::now()->subDays(1),
        ];

        $saldoAnterior = 1000;

        foreach ($fechas as $fecha) {
            $ingresos = rand(500, 2000);
            $egresos = rand(200, 800);
            
            $cuadre = Cuadre::create([
                'organization_id' => $organization->id,
                'fecha' => $fecha->format('Y-m-d'),
                'saldo_anterior' => $saldoAnterior,
                'ingresos_efectivo' => $ingresos * 0.6,
                'ingresos_transferencia' => $ingresos * 0.3,
                'ingresos_tarjeta' => $ingresos * 0.1,
                'egresos_efectivo' => $egresos * 0.7,
                'egresos_transferencia' => $egresos * 0.2,
                'egresos_tarjeta' => $egresos * 0.1,
                'saldo_fisico' => $saldoAnterior + $ingresos - $egresos + rand(-50, 50), // Pequeña diferencia simulada
                'observaciones' => 'Cuadre de prueba para ' . $fecha->format('d/m/Y'),
                'cerrado' => true,
                'fecha_cierre' => $fecha->addHours(20), // Cerrado al final del día
            ]);

            // El saldo anterior del siguiente día es el saldo físico del día anterior
            $saldoAnterior = $cuadre->saldo_fisico;
        }

        echo "Cuadres de prueba creados exitosamente.\n";
    }
}
