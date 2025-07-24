<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
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
            ]
        );

        // Asignar rol de administrador
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$admin->hasRole('admin')) {
            $admin->roles()->attach($adminRole->id, [
                'assigned_at' => now(),
                'assigned_by' => null, // Auto-asignado
            ]);
        }

        // Mostrar credenciales en la consola
        $this->command->info('Usuario administrador creado:');
        $this->command->info('Email: admin@matamares.com');
        $this->command->info('Password: Admin123!');
    }
}
