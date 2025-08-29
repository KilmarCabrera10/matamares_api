# API Inventario - Documentación

## Configuración de Autenticación

### Headers Requeridos
```http
Authorization: Bearer {token}
Organization-Id: {uuid-de-organizacion}
Content-Type: application/json
```

## Endpoints Disponibles

### 🏢 Organizaciones

#### Listar organizaciones del usuario
```http
GET /api/inventario/organizations
Authorization: Bearer {token}
```

#### Crear organización
```http
POST /api/inventario/organizations
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Mi Empresa",
    "description": "Descripción de la empresa",
    "industry": "retail",
    "settings": {
        "currency": "USD",
        "timezone": "America/Mexico_City"
    }
}
```

#### Obtener organización específica
```http
GET /api/inventario/organizations/{id}
Authorization: Bearer {token}
```

#### Actualizar organización
```http
PUT /api/inventario/organizations/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Empresa Actualizada",
    "description": "Nueva descripción"
}
```

---

### 📦 Productos

#### Listar productos
```http
GET /api/inventario/products
Authorization: Bearer {token}
Organization-Id: {uuid}

# Parámetros opcionales:
?category_id={uuid}
&supplier_id={uuid}
&search={termino}
&is_active=true
&per_page=20
&page=1
```

#### Crear producto
```http
POST /api/inventario/products
Authorization: Bearer {token}
Organization-Id: {uuid}
Content-Type: application/json

{
    "name": "Producto Ejemplo",
    "description": "Descripción del producto",
    "sku": "PROD-001",
    "barcode": "1234567890123",
    "category_id": "uuid-categoria",
    "supplier_id": "uuid-proveedor",
    "cost": 10.50,
    "price": 15.99,
    "min_stock": 5,
    "max_stock": 100,
    "attributes": {
        "color": "azul",
        "size": "M"
    }
}
```

#### Buscar productos
```http
GET /api/inventario/products/search/{termino}
Authorization: Bearer {token}
Organization-Id: {uuid}
```

#### Productos con stock bajo
```http
GET /api/inventario/products/low-stock/alert
Authorization: Bearer {token}
Organization-Id: {uuid}
```

---

### 📍 Ubicaciones

#### Listar ubicaciones
```http
GET /api/inventario/locations
Authorization: Bearer {token}
Organization-Id: {uuid}
```

#### Crear ubicación
```http
POST /api/inventario/locations
Authorization: Bearer {token}
Organization-Id: {uuid}
Content-Type: application/json

{
    "name": "Bodega Principal",
    "code": "BOD-01",
    "type": "warehouse",
    "parent_id": null,
    "settings": {
        "max_capacity": 1000,
        "temperature_controlled": true
    }
}
```

#### Ver stock de ubicación
```http
GET /api/inventario/locations/{id}/stock
Authorization: Bearer {token}
Organization-Id: {uuid}
```

#### Transferir entre ubicaciones
```http
POST /api/inventario/locations/{fromLocationId}/transfer/{toLocationId}
Authorization: Bearer {token}
Organization-Id: {uuid}
Content-Type: application/json

{
    "product_id": "uuid-producto",
    "quantity": 10,
    "notes": "Transferencia por reorganización"
}
```

---

### 📊 Inventario

#### Dashboard de inventario
```http
GET /api/inventario/inventory/dashboard
Authorization: Bearer {token}
Organization-Id: {uuid}
```

#### Ver stock detallado
```http
GET /api/inventario/inventory/stock
Authorization: Bearer {token}
Organization-Id: {uuid}

# Filtros opcionales:
?location_id={uuid}
&product_id={uuid}
&category_id={uuid}
&low_stock=true
&zero_stock=false
&per_page=20
```

#### Historial de movimientos
```http
GET /api/inventario/inventory/movements
Authorization: Bearer {token}
Organization-Id: {uuid}

# Filtros opcionales:
?product_id={uuid}
&location_id={uuid}
&transaction_type_id={uuid}
&category=in|out|adjustment
&date_from=2024-01-01
&date_to=2024-12-31
```

#### Crear movimiento de inventario
```http
POST /api/inventario/inventory/movements
Authorization: Bearer {token}
Organization-Id: {uuid}
Content-Type: application/json

{
    "product_id": "uuid-producto",
    "location_id": "uuid-ubicacion",
    "transaction_type_id": "uuid-tipo-transaccion",
    "quantity": 50,
    "unit_cost": 10.25,
    "reference_type": "purchase_order",
    "reference_id": "uuid-orden-compra",
    "notes": "Entrada por compra"
}
```

#### Tipos de transacciones
```http
GET /api/inventario/inventory/transaction-types
Authorization: Bearer {token}
Organization-Id: {uuid}
```

#### Reportes
```http
GET /api/inventario/inventory/reports
Authorization: Bearer {token}
Organization-Id: {uuid}

# Tipos de reporte:
?type=summary          # Resumen general
?type=valuation        # Valorización de inventario
?type=movement         # Reporte de movimientos
?type=low_stock        # Productos con stock bajo

# Para reporte de movimientos:
&date_from=2024-01-01
&date_to=2024-12-31
```

---

## Códigos de Respuesta

| Código | Descripción |
|--------|-------------|
| 200    | Éxito |
| 201    | Creado exitosamente |
| 400    | Solicitud incorrecta |
| 401    | No autenticado |
| 403    | Sin permisos |
| 404    | No encontrado |
| 422    | Error de validación |
| 500    | Error interno del servidor |

## Estructura de Respuestas

### Respuesta exitosa
```json
{
    "success": true,
    "data": {...},
    "message": "Operación exitosa"
}
```

### Respuesta de error
```json
{
    "success": false,
    "error": "Código de error",
    "message": "Descripción del error",
    "errors": {...}  // Solo en errores de validación
}
```

### Respuesta paginada
```json
{
    "success": true,
    "data": {
        "data": [...],
        "current_page": 1,
        "per_page": 20,
        "total": 100,
        "last_page": 5,
        "next_page_url": "...",
        "prev_page_url": null
    }
}
```

## Ejemplos de Uso

### 1. Flujo completo: Crear organización y producto

```bash
# 1. Crear organización
curl -X POST "http://localhost:8000/api/inventario/organizations" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mi Empresa",
    "industry": "retail"
  }'

# 2. Crear producto (usar Organization-Id de la respuesta anterior)
curl -X POST "http://localhost:8000/api/inventario/products" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Organization-Id: ORGANIZATION_UUID" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Producto Test",
    "sku": "TEST-001",
    "cost": 10.00,
    "price": 15.00,
    "min_stock": 5
  }'
```

### 2. Registrar entrada de inventario

```bash
curl -X POST "http://localhost:8000/api/inventario/inventory/movements" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Organization-Id: ORGANIZATION_UUID" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": "PRODUCT_UUID",
    "location_id": "LOCATION_UUID",
    "transaction_type_id": "TRANSACTION_TYPE_UUID",
    "quantity": 100,
    "unit_cost": 10.00,
    "notes": "Compra inicial"
  }'
```
