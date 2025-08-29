<?php

namespace App\Projects\Inventario\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Product extends Model
{
    use HasUuids;

    protected $connection = 'inventario';
    protected $table = 'products';

    protected $fillable = [
        'organization_id',
        'category_id',
        'supplier_id',
        'sku',
        'barcode',
        'name',
        'description',
        'unit_type',
        'unit_name',
        'unit_precision',
        'cost_price',
        'selling_price',
        'currency',
        'track_inventory',
        'min_stock',
        'max_stock',
        'reorder_point',
        'reorder_quantity',
        'track_expiry',
        'track_batches',
        'shelf_life_days',
        'is_active',
        'is_sellable',
        'is_purchasable',
        'attributes',
        'created_by',
    ];

    protected $casts = [
        'cost_price' => 'decimal:4',
        'selling_price' => 'decimal:4',
        'min_stock' => 'decimal:4',
        'max_stock' => 'decimal:4',
        'reorder_point' => 'decimal:4',
        'reorder_quantity' => 'decimal:4',
        'track_inventory' => 'boolean',
        'track_expiry' => 'boolean',
        'track_batches' => 'boolean',
        'is_active' => 'boolean',
        'is_sellable' => 'boolean',
        'is_purchasable' => 'boolean',
        'attributes' => 'array',
        'unit_precision' => 'integer',
        'shelf_life_days' => 'integer',
    ];

    /**
     * Obtener la organización
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Obtener la categoría
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Obtener el proveedor principal
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Obtener quien creó el producto
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtener el stock en diferentes ubicaciones
     */
    public function inventoryStock(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    /**
     * Obtener los movimientos de inventario
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
