<?php

namespace App\Projects\Inventario\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class InventoryStock extends Model
{
    use HasUuids;

    protected $connection = 'inventario';
    protected $table = 'inventory_stock';

    protected $fillable = [
        'organization_id',
        'product_id',
        'location_id',
        'quantity',
        'reserved_quantity',
        'average_cost',
        'last_movement_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'reserved_quantity' => 'decimal:4',
        'average_cost' => 'decimal:4',
        'last_movement_at' => 'datetime',
    ];

    /**
     * Calcular la cantidad disponible
     */
    public function getAvailableQuantityAttribute(): float
    {
        return $this->quantity - $this->reserved_quantity;
    }

    /**
     * Obtener la organización
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Obtener el producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Obtener la ubicación
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
