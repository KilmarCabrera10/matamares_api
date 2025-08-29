# Proyecto Inventario - Sistema SaaS Multitenancy

## Descripción
Sistema de gestión de inventario con arquitectura multitenancy, diseñado para manejar múltiples organizaciones con sus respectivos productos, ubicaciones, categorías y movimientos de stock.

## Características Principales

### 🏢 **Multitenancy**
- Organizaciones independientes con sus propios datos
- Usuarios pueden pertenecer a múltiples organizaciones
- Roles y permisos granulares por organización

### 📦 **Gestión de Productos**
- Productos con múltiples unidades de medida
- Control de costos y precios por producto
- Atributos extensibles mediante JSON
- Seguimiento de lotes y fechas de vencimiento

### 🏪 **Ubicaciones y Almacenes**
- Múltiples ubicaciones por organización
- Diferentes tipos: almacén, tienda, cocina, etc.
- Stock independiente por ubicación

### 📊 **Control de Inventario**
- Stock en tiempo real por producto/ubicación
- Movimientos de inventario auditados
- Tipos de transacciones configurables
- Cálculo de costo promedio ponderado

### 🔧 **Extensibilidad**
- Sistema modular para especialización
- Recetas para industria F&B
- Logs de auditoría completos

## Estructura de Base de Datos

### Tablas Principales:
1. **organizations** - Empresas/organizaciones
2. **users** - Usuarios del sistema
3. **organization_members** - Relación usuarios-organizaciones
4. **locations** - Ubicaciones/almacenes
5. **categories** - Categorías jerárquicas de productos
6. **suppliers** - Proveedores
7. **products** - Productos del inventario
8. **inventory_stock** - Stock actual por ubicación
9. **transaction_types** - Tipos de movimientos
10. **inventory_movements** - Movimientos de inventario

### Tablas Especializadas:
- **product_batches** - Lotes/batches de productos
- **recipes** - Recetas (módulo F&B)
- **recipe_ingredients** - Ingredientes de recetas
- **organization_modules** - Módulos activos por organización
- **audit_logs** - Logs de auditoría

## Instalación y Configuración

### 1. Configurar Variables de Entorno
Copiar y configurar las variables de entorno:
```bash
cp .env.inventario.example .env.local
```

Agregar al archivo `.env` principal:
```env
# Configuración de base de datos para Inventario
INVENTARIO_DB_HOST=127.0.0.1
INVENTARIO_DB_PORT=5432
INVENTARIO_DB_DATABASE=inventario_db
INVENTARIO_DB_USERNAME=postgres
INVENTARIO_DB_PASSWORD=tu_password
```

### 2. Crear Base de Datos
```sql
CREATE DATABASE inventario_db;
```

### 3. Ejecutar Migraciones
```bash
php artisan migrate --database=inventario --path=database/inventario/migrations
```

### 4. Ejecutar Seeders
```bash
php artisan db:seed --database=inventario --class="Database\Seeders\Inventario\TransactionTypesSeeder"
```

## Modelos Disponibles

### Core Models:
- `Organization` - Gestión de organizaciones
- `User` - Usuarios del sistema  
- `OrganizationMember` - Miembros de organizaciones
- `Location` - Ubicaciones/almacenes
- `Category` - Categorías de productos
- `Supplier` - Proveedores
- `Product` - Productos
- `InventoryStock` - Stock de inventario

## API Endpoints

### Organizaciones
- `GET /api/organizations` - Listar organizaciones
- `POST /api/organizations` - Crear organización
- `GET /api/organizations/{id}` - Mostrar organización
- `PUT /api/organizations/{id}` - Actualizar organización
- `DELETE /api/organizations/{id}` - Eliminar organización

## Próximos Pasos

### Controladores por Implementar:
- [ ] `LocationController` - Gestión de ubicaciones
- [ ] `CategoryController` - Gestión de categorías
- [ ] `ProductController` - Gestión de productos
- [ ] `SupplierController` - Gestión de proveedores
- [ ] `InventoryController` - Control de inventario y movimientos

### Funcionalidades Avanzadas:
- [ ] Sistema de autenticación multitenancy
- [ ] Middleware para scope de organización
- [ ] Reportes de inventario
- [ ] Alertas de stock mínimo
- [ ] Sistema de approval para ajustes
- [ ] Módulo de recetas (F&B)
- [ ] API para escáner de códigos de barras

## Comandos Útiles

```bash
# Verificar configuración de base de datos
php artisan config:cache
php artisan config:clear

# Ejecutar migraciones específicas
php artisan migrate --database=inventario

# Rollback de migraciones
php artisan migrate:rollback --database=inventario

# Status de migraciones
php artisan migrate:status --database=inventario
```
