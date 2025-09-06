<?php

namespace Database\Matamares\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Projects\Matamares\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Juan Pérez García',
                'email' => 'juan.perez@email.com',
                'phone' => '+52 55 1234 5678',
                'address' => 'Av. Insurgentes Sur 123, Col. Roma Norte, CDMX',
                'document' => '12345678',
            ],
            [
                'name' => 'María González López',
                'email' => 'maria.gonzalez@gmail.com',
                'phone' => '+52 55 9876 5432',
                'address' => 'Calle 5 de Mayo 456, Col. Centro, CDMX',
                'document' => '87654321',
            ],
            [
                'name' => 'Carlos Rodríguez Martínez',
                'email' => 'carlos.rodriguez@outlook.com',
                'phone' => '+52 55 5555 1234',
                'address' => 'Blvd. Manuel Ávila Camacho 789, Col. Polanco, CDMX',
                'document' => '11223344',
            ],
            [
                'name' => 'Ana Sofía Hernández',
                'email' => 'ana.hernandez@yahoo.com',
                'phone' => '+52 55 7777 8888',
                'address' => 'Paseo de la Reforma 321, Col. Juárez, CDMX',
                'document' => '99887766',
            ],
            [
                'name' => 'Luis Alberto Torres',
                'email' => 'luis.torres@hotmail.com',
                'phone' => '+52 55 3333 4444',
                'address' => 'Eje Central Lázaro Cárdenas 654, Col. Doctores, CDMX',
                'document' => '55667788',  
            ],
            [
                'name' => 'Cliente Genérico',
                'email' => null,
                'phone' => null,
                'address' => null,
                'document' => null,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['document' => $customer['document'] ?? $customer['name']],
                $customer
            );
        }
    }
}
