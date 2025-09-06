<?php

namespace Database\Inventario\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar los seeders en el orden correcto
        $this->call([
            // 1. Primero los tipos de transacciones (datos base del sistema)
            TransactionTypesSeeder::class,
            
            // 2. Luego los datos de demo (organización, usuarios, productos, etc.)
            InventoryDemoSeeder::class,
            
            // 3. Finalmente los cuadres (requieren que exista organización y datos)
            CuadresSeeder::class,
        ]);

        $this->command->info('🎉 Todos los seeders de Inventario han sido ejecutados exitosamente!');
        $this->command->info('');
        $this->command->info('📊 Datos creados:');
        $this->command->info('   ✅ Organización demo');
        $this->command->info('   ✅ Usuario administrador (admin@demo.com)');
        $this->command->info('   ✅ Ubicaciones y productos de ejemplo');
        $this->command->info('   ✅ Tipos de transacciones');
        $this->command->info('   ✅ Cuadres de ejemplo');
        $this->command->info('');
        $this->command->info('🔑 Credenciales de acceso:');
        $this->command->info('   Email: admin@demo.com');
        $this->command->info('   Password: password');
    }
}
