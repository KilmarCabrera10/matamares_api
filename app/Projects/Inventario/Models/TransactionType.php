<?php

namespace App\Projects\Inventario\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TransactionType extends Model
{
    use HasUuids;

    protected $connection = 'inventario';
    protected $table = 'transaction_types';

    protected $fillable = [
        'organization_id',
        'code',
        'name',
        'category',
        'affects_cost',
        'requires_approval',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'affects_cost' => 'boolean',
        'requires_approval' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Obtener la organización (puede ser null para tipos del sistema)
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Obtener los movimientos de este tipo
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Scope para tipos del sistema
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope para tipos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para tipos de entrada
     */
    public function scopeIn($query)
    {
        return $query->where('category', 'in');
    }

    /**
     * Scope para tipos de salida
     */
    public function scopeOut($query)
    {
        return $query->where('category', 'out');
    }
}
