<?php

namespace App\Projects\Inventario\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class InventoryMovement extends Model
{
    use HasUuids;

    protected $connection = 'inventario';
    protected $table = 'inventory_movements';

    protected $fillable = [
        'organization_id',
        'transaction_number',
        'transaction_type_id',
        'reference_type',
        'reference_id',
        'product_id',
        'location_id',
        'batch_id',
        'quantity',
        'unit_cost',
        'balance_before',
        'balance_after',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    /**
     * Calcular el costo total
     */
    public function getTotalCostAttribute(): float
    {
        return $this->quantity * $this->unit_cost;
    }

    /**
     * Obtener la organización
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Obtener el tipo de transacción
     */
    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class);
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

    /**
     * Obtener quien creó el movimiento
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtener quien aprobó el movimiento
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
