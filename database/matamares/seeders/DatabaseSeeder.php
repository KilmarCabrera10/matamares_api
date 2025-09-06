<?php

namespace Database\Matamares\Seeders;

use App\Projects\Matamares\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar seeders en orden
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
        ]);

        // Crear usuarios de prueba (opcional)
        // User::factory(10)->create();
    }
}
