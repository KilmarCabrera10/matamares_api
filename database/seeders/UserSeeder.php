<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'María García - Gerente',
                'email' => 'gerente@matamares.com',
                'password' => Hash::make('Gerente123!'),
                'role' => 'gerente',
                'active' => true,
            ],
            [
                'name' => 'Carlos López - Cajero',
                'email' => 'cajero@matamares.com',
                'password' => Hash::make('Cajero123!'),
                'role' => 'cajero',
                'active' => true,
            ],
            [
                'name' => 'Ana Martínez - Cajero',
                'email' => 'cajero2@matamares.com',
                'password' => Hash::make('Cajero123!'),
                'role' => 'cajero',
                'active' => true,
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Asignar rol si no lo tiene
            if (!$user->hasRole($role)) {
                $user->assignRole($role);
            }
        }

        $this->command->info('Usuarios de prueba creados:');
        $this->command->info('Gerente: gerente@matamares.com / Gerente123!');
        $this->command->info('Cajero 1: cajero@matamares.com / Cajero123!');
        $this->command->info('Cajero 2: cajero2@matamares.com / Cajero123!');
    }
}
