<?php

namespace App\Projects\Inventario\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Organization extends Model
{
    use HasUuids;

    protected $connection = 'inventario';
    protected $table = 'organizations';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'plan_type',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Obtener los miembros de la organización
     */
    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    /**
     * Obtener las ubicaciones de la organización
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Obtener las categorías de la organización
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Obtener los productos de la organización
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Obtener los proveedores de la organización
     */
    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }
}
