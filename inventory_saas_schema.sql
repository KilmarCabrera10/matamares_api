
-- =============================================
-- MULTITENANCY & AUTHENTICATION
-- =============================================

-- Organizaciones/Empresas
CREATE TABLE organizations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    domain VARCHAR(255),
    plan_type VARCHAR(50) DEFAULT 'basic', -- basic, pro, enterprise
    status VARCHAR(20) DEFAULT 'active', -- active, suspended, cancelled
    settings JSONB DEFAULT '{}', -- Configuraciones flexibles
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Usuarios del sistema
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    avatar_url TEXT,
    email_verified BOOLEAN DEFAULT FALSE,
    status VARCHAR(20) DEFAULT 'active',
    last_login_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Relación usuarios-organizaciones (muchos a muchos)
CREATE TABLE organization_members (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID REFERENCES organizations(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(50) DEFAULT 'member', -- owner, admin, manager, member, viewer
    permissions JSONB DEFAULT '{}', -- Permisos granulares
    invited_by UUID REFERENCES users(id),
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'active',
    UNIQUE(organization_id, user_id)
);

-- =============================================
-- CORE INVENTORY SYSTEM
-- =============================================

-- Ubicaciones/Almacenes
CREATE TABLE locations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50), -- Código interno
    address TEXT,
    type VARCHAR(50) DEFAULT 'warehouse', -- warehouse, store, kitchen, etc.
    is_active BOOLEAN DEFAULT TRUE,
    settings JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categorías de productos (jerárquicas)
CREATE TABLE categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID REFERENCES organizations(id) ON DELETE CASCADE,
    parent_id UUID REFERENCES categories(id) ON DELETE SET NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    code VARCHAR(50),
    color VARCHAR(7), -- Hex color
    icon VARCHAR(50),
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Proveedores
CREATE TABLE suppliers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50),
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    tax_id VARCHAR(50),
    payment_terms INTEGER DEFAULT 30, -- días
    currency VARCHAR(3) DEFAULT 'USD',
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Productos (núcleo del sistema)
CREATE TABLE products (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID REFERENCES organizations(id) ON DELETE CASCADE,
    category_id UUID REFERENCES categories(id) ON DELETE SET NULL,
    sku VARCHAR(100) NOT NULL,
    barcode VARCHAR(100),
    name VARCHAR(255) NOT NULL,
    description TEXT,

    -- Unidades de medida
    unit_type VARCHAR(50) NOT NULL, -- piece, weight, volume, length, area
    unit_name VARCHAR(50) NOT NULL, -- kg, liters, pieces, meters, etc.
    unit_precision INTEGER DEFAULT 2, -- decimales

    -- Costos y precios
    cost_price DECIMAL(15,4) DEFAULT 0,
    selling_price DECIMAL(15,4) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'USD',

    -- Control de stock
    track_inventory BOOLEAN DEFAULT TRUE,
    min_stock DECIMAL(15,4) DEFAULT 0,
    max_stock DECIMAL(15,4),
    reorder_point DECIMAL(15,4),
    reorder_quantity DECIMAL(15,4),

    -- Fechas y lotes
    track_expiry BOOLEAN DEFAULT FALSE,
    track_batches BOOLEAN DEFAULT FALSE,
    shelf_life_days INTEGER,

    -- Estados
    is_active BOOLEAN DEFAULT TRUE,
    is_sellable BOOLEAN DEFAULT TRUE,
    is_purchasable BOOLEAN DEFAULT TRUE,

    -- Metadatos extensibles
    attributes JSONB DEFAULT '{}', -- Para especialización futura

    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(organization_id, sku)
);

-- Stock actual por ubicación
CREATE TABLE inventory_stock (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID REFERENCES organizations(id) ON DELETE CASCADE,
    product_id UUID REFERENCES products(id) ON DELETE CASCADE,
    location_id UUID REFERENCES locations(id) ON DELETE CASCADE,

    quantity DECIMAL(15,4) DEFAULT 0,
    reserved_quantity DECIMAL(15,4) DEFAULT 0, -- Para órdenes pendientes
    available_quantity DECIMAL(15,4) GENERATED ALWAYS AS (quantity - reserved_quantity) STORED,

    average_cost DECIMAL(15,4) DEFAULT 0, -- Costo promedio ponderado
    last_movement_at TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(product_id, location_id)
);

-- Lotes/Batches (para productos que lo requieran)
CREATE TABLE product_batches (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID REFERENCES organizations(id) ON DELETE CASCADE,
    product_id UUID REFERENCES products(id) ON DELETE CASCADE,
    location_id UUID REFERENCES locations(id) ON DELETE CASCADE,

    batch_number VARCHAR(100) NOT NULL,
    quantity DECIMAL(15,4) DEFAULT 0,
    cost_price DECIMAL(15,4) DEFAULT 0,

    manufactured_date DATE,
    expiry_date DATE,
    supplier_id UUID REFERENCES suppliers(id),

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(organization_id, product_id, batch_number)
);

-- =============================================
-- MOVIMIENTOS DE INVENTARIO
-- =============================================

-- Tipos de transacciones
CREATE TABLE transaction_types (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID REFERENCES organizations(id) ON DELETE CASCADE,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL, -- in, out, adjustment, transfer
    affects_cost BOOLEAN DEFAULT TRUE,
    requires_approval BOOLEAN DEFAULT FALSE,
    is_system BOOLEAN DEFAULT FALSE, -- Para tipos predefinidos
    is_active BOOLEAN DEFAULT TRUE,

    UNIQUE(organization_id, code)
);

-- Movimientos de inventario (transacciones)
CREATE TABLE inventory_movements (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID REFERENCES organizations(id) ON DELETE CASCADE,

    -- Referencia
    transaction_number VARCHAR(100) NOT NULL,
    transaction_type_id UUID REFERENCES transaction_types(id),
    reference_type VARCHAR(50), -- purchase_order, sale, adjustment, etc.
    reference_id UUID, -- ID del documento relacionado

    -- Producto y ubicación
    product_id UUID REFERENCES products(id) ON DELETE RESTRICT,
    location_id UUID REFERENCES locations(id) ON DELETE RESTRICT,
    batch_id UUID REFERENCES product_batches(id) ON DELETE SET NULL,

    -- Cantidades
    quantity DECIMAL(15,4) NOT NULL,
    unit_cost DECIMAL(15,4) DEFAULT 0,
    total_cost DECIMAL(15,4) GENERATED ALWAYS AS (quantity * unit_cost) STORED,

    -- Balances (snapshot al momento)
    balance_before DECIMAL(15,4) DEFAULT 0,
    balance_after DECIMAL(15,4) DEFAULT 0,

    -- Metadatos
    notes TEXT,
    created_by UUID REFERENCES users(id),
    approved_by UUID REFERENCES users(id),
    approved_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(organization_id, transaction_number)
);

-- =============================================
-- SISTEMA EXTENSIBLE PARA ESPECIALIZACIONES
-- =============================================

-- Módulos/Extensiones activadas por organización
CREATE TABLE organization_modules (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID REFERENCES organizations(id) ON DELETE CASCADE,
    module_code VARCHAR(50) NOT NULL, -- recipes, barcode_scanning, etc.
    is_active BOOLEAN DEFAULT TRUE,
    settings JSONB DEFAULT '{}',
    activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(organization_id, module_code)
);

-- Recetas (para módulo F&B)
CREATE TABLE recipes (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID REFERENCES organizations(id) ON DELETE CASCADE,

    name VARCHAR(255) NOT NULL,
    code VARCHAR(100),
    description TEXT,
    category VARCHAR(100),

    -- Producto final (opcional)
    output_product_id UUID REFERENCES products(id) ON DELETE SET NULL,
    output_quantity DECIMAL(15,4) DEFAULT 1,

    -- Costos
    estimated_cost DECIMAL(15,4) DEFAULT 0,
    selling_price DECIMAL(15,4) DEFAULT 0,

    -- Estados
    is_active BOOLEAN DEFAULT TRUE,
    version INTEGER DEFAULT 1,

    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ingredientes de recetas
CREATE TABLE recipe_ingredients (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    recipe_id UUID REFERENCES recipes(id) ON DELETE CASCADE,
    product_id UUID REFERENCES products(id) ON DELETE CASCADE,

    quantity DECIMAL(15,4) NOT NULL,
    unit_name VARCHAR(50), -- Puede diferir de la unidad base del producto
    conversion_factor DECIMAL(15,4) DEFAULT 1, -- Para convertir a unidad base

    cost_per_unit DECIMAL(15,4) DEFAULT 0,
    total_cost DECIMAL(15,4) GENERATED ALWAYS AS (quantity * cost_per_unit) STORED,

    notes TEXT,
    sort_order INTEGER DEFAULT 0
);

-- =============================================
-- AUDITORÍA Y LOGS
-- =============================================

-- Log de cambios para auditoría
CREATE TABLE audit_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID REFERENCES organizations(id) ON DELETE CASCADE,

    table_name VARCHAR(100) NOT NULL,
    record_id UUID NOT NULL,
    action VARCHAR(20) NOT NULL, -- INSERT, UPDATE, DELETE

    old_values JSONB,
    new_values JSONB,
    changed_fields TEXT[],

    user_id UUID REFERENCES users(id),
    ip_address INET,
    user_agent TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- ÍNDICES PARA PERFORMANCE
-- =============================================

-- Índices principales
CREATE INDEX idx_organizations_slug ON organizations(slug);
CREATE INDEX idx_organization_members_org_user ON organization_members(organization_id, user_id);
CREATE INDEX idx_products_org_sku ON products(organization_id, sku);
CREATE INDEX idx_products_org_category ON products(organization_id, category_id);
CREATE INDEX idx_inventory_stock_product_location ON inventory_stock(product_id, location_id);
CREATE INDEX idx_inventory_movements_org_product ON inventory_movements(organization_id, product_id);
CREATE INDEX idx_inventory_movements_created_at ON inventory_movements(created_at DESC);
CREATE INDEX idx_audit_logs_org_table_record ON audit_logs(organization_id, table_name, record_id);

-- Índices para búsquedas
CREATE INDEX idx_products_name_search ON products USING gin(to_tsvector('spanish', name));
CREATE INDEX idx_products_attributes ON products USING gin(attributes);

-- =============================================
-- TRIGGERS Y FUNCIONES
-- =============================================

-- Función para actualizar updated_at automáticamente
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Aplicar trigger a tablas principales
CREATE TRIGGER update_organizations_updated_at BEFORE UPDATE ON organizations FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_products_updated_at BEFORE UPDATE ON products FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_inventory_stock_updated_at BEFORE UPDATE ON inventory_stock FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- =============================================
-- DATOS INICIALES
-- =============================================

-- Tipos de transacciones por defecto
INSERT INTO transaction_types (id, organization_id, code, name, category, is_system) VALUES
(gen_random_uuid(), NULL, 'PURCHASE', 'Compra', 'in', TRUE),
(gen_random_uuid(), NULL, 'SALE', 'Venta', 'out', TRUE),
(gen_random_uuid(), NULL, 'ADJUSTMENT_IN', 'Ajuste Positivo', 'in', TRUE),
(gen_random_uuid(), NULL, 'ADJUSTMENT_OUT', 'Ajuste Negativo', 'out', TRUE),
(gen_random_uuid(), NULL, 'TRANSFER_IN', 'Transferencia Entrada', 'in', TRUE),
(gen_random_uuid(), NULL, 'TRANSFER_OUT', 'Transferencia Salida', 'out', TRUE),
(gen_random_uuid(), NULL, 'PRODUCTION', 'Producción', 'in', TRUE),
(gen_random_uuid(), NULL, 'CONSUMPTION', 'Consumo', 'out', TRUE);
