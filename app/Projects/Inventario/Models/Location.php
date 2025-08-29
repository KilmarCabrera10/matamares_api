<?php

namespace App\Projects\Inventario\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Location extends Model
{
    use HasUuids;

    protected $connection = 'inventario';
    protected $table = 'locations';

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'address',
        'type',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Obtener la organización
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Obtener el stock de inventario en esta ubicación
     */
    public function inventoryStock(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }
}
