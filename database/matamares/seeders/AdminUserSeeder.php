<?php

namespace Database\Matamares\Seeders;

use Illuminate\Database\Seeder;
use App\Projects\Matamares\Models\User;
use App\Projects\Matamares\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@matamares.com'],
            [
                'name' => 'Administrador General',
                'email' => 'admin@matamares.com',
                'password' => Hash::make('Admin123!'),
                'email_verified_at' => now(),
                'active' => true,
            ]
        );

        // Asignar rol de administrador
        if (!$admin->hasRole('administrador')) {
            $admin->assignRole('administrador');
        }

        // Mostrar credenciales en la consola
        $this->command->info('Usuario administrador creado:');
        $this->command->info('Email: admin@matamares.com');
        $this->command->info('Password: Admin123!');
    }
}
