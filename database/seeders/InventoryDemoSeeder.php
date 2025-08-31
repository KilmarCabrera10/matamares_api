<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Crear una organización de demo
        $orgId = Str::uuid();
        DB::connection('inventario')->table('organizations')->insert([
            'id' => $orgId,
            'name' => 'Empresa Demo Inventario',
            'slug' => 'empresa-demo',
            'domain' => 'demo.inventario.com',
            'plan_type' => 'pro',
            'status' => 'active',
            'settings' => json_encode([
                'currency' => 'USD',
                'timezone' => 'America/Mexico_City',
                'features' => ['recipes', 'batches', 'advanced_reports']
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear un usuario de demo
        $userId = Str::uuid();
        DB::connection('inventario')->table('users')->insert([
            'id' => $userId,
            'email' => 'admin@demo.com',
            'password_hash' => bcrypt('password'),
            'first_name' => 'Administrador',
            'last_name' => 'Demo',
            'email_verified' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Relacionar usuario con organización
        DB::connection('inventario')->table('organization_members')->insert([
            'id' => Str::uuid(),
            'organization_id' => $orgId,
            'user_id' => $userId,
            'role' => 'owner',
            'permissions' => json_encode(['*']),
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Crear una ubicación de demo
        $locationId = Str::uuid();
        DB::connection('inventario')->table('locations')->insert([
            'id' => $locationId,
            'organization_id' => $orgId,
            'name' => 'Almacén Principal',
            'code' => 'ALM-001',
            'address' => 'Calle Principal 123, Ciudad Demo',
            'type' => 'warehouse',
            'is_active' => true,
            'settings' => json_encode([
                'max_capacity' => 1000,
                'units' => 'cubic_meters'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear una categoría de demo
        $categoryId = Str::uuid();
        DB::connection('inventario')->table('categories')->insert([
            'id' => $categoryId,
            'organization_id' => $orgId,
            'parent_id' => null,
            'name' => 'Productos de Oficina',
            'description' => 'Artículos y suministros para oficina',
            'code' => 'OFICINA',
            'color' => '#3498db',
            'icon' => 'office',
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear un proveedor de demo
        $supplierId = Str::uuid();
        DB::connection('inventario')->table('suppliers')->insert([
            'id' => $supplierId,
            'organization_id' => $orgId,
            'name' => 'Proveedor Demo S.A.',
            'code' => 'PROV-001',
            'contact_person' => 'Juan Pérez',
            'email' => 'ventas@proveedor-demo.com',
            'phone' => '+52 55 1234 5678',
            'address' => 'Av. Proveedores 456, Ciudad Industrial',
            'tax_id' => 'RFC123456789',
            'payment_terms' => 30,
            'currency' => 'USD',
            'is_active' => true,
            'notes' => 'Proveedor confiable con entregas puntuales',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear un producto de demo
        $productId = Str::uuid();
        DB::connection('inventario')->table('products')->insert([
            'id' => $productId,
            'organization_id' => $orgId,
            'category_id' => $categoryId,
            'supplier_id' => $supplierId,
            'sku' => 'DEMO-001',
            'barcode' => '1234567890123',
            'name' => 'Resma de Papel A4',
            'description' => 'Resma de papel bond tamaño carta, 500 hojas',
            'unit_type' => 'piece',
            'unit_name' => 'resmas',
            'unit_precision' => 0,
            'cost_price' => 5.50,
            'selling_price' => 8.00,
            'currency' => 'USD',
            'track_inventory' => true,
            'min_stock' => 10,
            'max_stock' => 100,
            'reorder_point' => 15,
            'reorder_quantity' => 50,
            'track_expiry' => false,
            'track_batches' => false,
            'is_active' => true,
            'is_sellable' => true,
            'is_purchasable' => true,
            'attributes' => json_encode([
                'color' => 'blanco',
                'weight' => '2.5kg',
                'dimensions' => '21x29.7cm'
            ]),
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear stock inicial
        DB::connection('inventario')->table('inventory_stock')->insert([
            'id' => Str::uuid(),
            'organization_id' => $orgId,
            'product_id' => $productId,
            'location_id' => $locationId,
            'quantity' => 25,
            'reserved_quantity' => 0,
            'average_cost' => 5.50,
            'last_movement_at' => now(),
            'updated_at' => now(),
        ]);

        echo "Datos de demo creados exitosamente:\n";
        echo "- Organización: Empresa Demo Inventario\n";
        echo "- Usuario: admin@demo.inventario.com (password: password)\n";
        echo "- Ubicación: Almacén Principal\n";
        echo "- Producto: Resma de Papel A4 (Stock: 25 unidades)\n";
    }
}
