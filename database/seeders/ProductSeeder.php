<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop HP EliteBook',
                'code' => 'ELBOOK001',
                'description' => 'Laptop empresarial HP EliteBook con procesador i7',
                'price' => 1299.99,
                'cost' => 900.00,
                'stock' => 15,
                'category' => 'Electrónicos',
                'barcode' => '7501234567890',
            ],
            [
                'name' => 'Mouse Inalámbrico Logitech',
                'code' => 'MOUSE001',
                'description' => 'Mouse inalámbrico Logitech MX Master 3',
                'price' => 89.99,
                'cost' => 60.00,
                'stock' => 50,
                'category' => 'Accesorios',
                'barcode' => '7501234567891',
            ],
            [
                'name' => 'Teclado Mecánico Gaming',
                'code' => 'KEYB001',
                'description' => 'Teclado mecánico RGB para gaming',
                'price' => 149.99,
                'cost' => 100.00,
                'stock' => 25,
                'category' => 'Accesorios',
                'barcode' => '7501234567892',
            ],
            [
                'name' => 'Monitor LED 24"',
                'code' => 'MON24001',
                'description' => 'Monitor LED de 24 pulgadas Full HD',
                'price' => 299.99,
                'cost' => 200.00,
                'stock' => 20,
                'category' => 'Monitores',
                'barcode' => '7501234567893',
            ],
            [
                'name' => 'Audífonos Bluetooth Sony',
                'code' => 'AUD001',
                'description' => 'Audífonos inalámbricos Sony WH-1000XM4',
                'price' => 349.99,
                'cost' => 250.00,
                'stock' => 10,
                'category' => 'Audio',
                'barcode' => '7501234567894',
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['code' => $product['code']],
                $product
            );
        }
    }
}
