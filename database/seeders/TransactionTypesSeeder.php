<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionTypesSeeder extends Seeder
{
    public function run(): void
    {
        $transactionTypes = [
            [
                'id' => Str::uuid(),
                'organization_id' => null,
                'code' => 'PURCHASE',
                'name' => 'Compra',
                'category' => 'in',
                'affects_cost' => true,
                'requires_approval' => false,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'organization_id' => null,
                'code' => 'SALE',
                'name' => 'Venta',
                'category' => 'out',
                'affects_cost' => true,
                'requires_approval' => false,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'organization_id' => null,
                'code' => 'ADJUSTMENT_IN',
                'name' => 'Ajuste Positivo',
                'category' => 'in',
                'affects_cost' => false,
                'requires_approval' => true,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'organization_id' => null,
                'code' => 'ADJUSTMENT_OUT',
                'name' => 'Ajuste Negativo',
                'category' => 'out',
                'affects_cost' => false,
                'requires_approval' => true,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'organization_id' => null,
                'code' => 'TRANSFER_IN',
                'name' => 'Transferencia Entrada',
                'category' => 'in',
                'affects_cost' => false,
                'requires_approval' => false,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'organization_id' => null,
                'code' => 'TRANSFER_OUT',
                'name' => 'Transferencia Salida',
                'category' => 'out',
                'affects_cost' => false,
                'requires_approval' => false,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'organization_id' => null,
                'code' => 'PRODUCTION',
                'name' => 'Producción',
                'category' => 'in',
                'affects_cost' => true,
                'requires_approval' => false,
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'organization_id' => null,
                'code' => 'CONSUMPTION',
                'name' => 'Consumo',
                'category' => 'out',
                'affects_cost' => true,
                'requires_approval' => false,
                'is_system' => true,
                'is_active' => true,
            ],
        ];

        DB::connection('inventario')->table('transaction_types')->insert($transactionTypes);
    }
}
