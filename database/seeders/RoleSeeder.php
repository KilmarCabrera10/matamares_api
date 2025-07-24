<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrador',
                'description' => 'Acceso completo al sistema',
                'is_active' => true,
            ],
            [
                'name' => 'user',
                'display_name' => 'Usuario',
                'description' => 'Usuario estándar del sistema',
                'is_active' => true,
            ],
            [
                'name' => 'moderator',
                'display_name' => 'Moderador',
                'description' => 'Acceso limitado de moderación',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
