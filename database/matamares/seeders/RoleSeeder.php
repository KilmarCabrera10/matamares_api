<?php

namespace Database\Matamares\Seeders;

use Illuminate\Database\Seeder;
use App\Projects\Matamares\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'administrador',
                'display_name' => 'Administrador',
                'description' => 'Acceso completo al sistema',
                'is_active' => true,
            ],
            [
                'name' => 'gerente',
                'display_name' => 'Gerente',
                'description' => 'Gestión de productos, ventas y reportes',
                'is_active' => true,
            ],
            [
                'name' => 'cajero',
                'display_name' => 'Cajero',
                'description' => 'Procesamiento de ventas y consulta de productos',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
