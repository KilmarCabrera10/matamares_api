<?php

namespace App\Projects\Inventario\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Cuadre extends Model
{
    use HasUuids;

    protected $connection = 'inventario';
    protected $table = 'cuadres';

    protected $fillable = [
        'organization_id',
        'fecha',
        'saldo_anterior',
        'ingresos_efectivo',
        'ingresos_transferencia',
        'ingresos_tarjeta',
        'egresos_efectivo',
        'egresos_transferencia',
        'egresos_tarjeta',
        'saldo_fisico',
        'observaciones',
        'cerrado',
        'creado_por',
        'cerrado_por',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha' => 'date',
        'saldo_anterior' => 'decimal:4',
        'ingresos_efectivo' => 'decimal:4',
        'ingresos_transferencia' => 'decimal:4',
        'ingresos_tarjeta' => 'decimal:4',
        'egresos_efectivo' => 'decimal:4',
        'egresos_transferencia' => 'decimal:4',
        'egresos_tarjeta' => 'decimal:4',
        'saldo_calculado' => 'decimal:4',
        'saldo_fisico' => 'decimal:4',
        'diferencia' => 'decimal:4',
        'cerrado' => 'boolean',
        'fecha_cierre' => 'datetime',
    ];

    protected $appends = [
        'total_ingresos',
        'total_egresos',
    ];

    /**
     * Calcular el total de ingresos
     */
    public function getTotalIngresosAttribute(): float
    {
        return $this->ingresos_efectivo + $this->ingresos_transferencia + $this->ingresos_tarjeta;
    }

    /**
     * Calcular el total de egresos
     */
    public function getTotalEgresosAttribute(): float
    {
        return $this->egresos_efectivo + $this->egresos_transferencia + $this->egresos_tarjeta;
    }

    /**
     * Obtener la organización
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Obtener quien creó el cuadre
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Obtener quien cerró el cuadre
     */
    public function cerrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    /**
     * Scope para obtener cuadres por fecha
     */
    public function scopeByFecha($query, $fecha)
    {
        return $query->where('fecha', $fecha);
    }

    /**
     * Scope para obtener cuadres abiertos
     */
    public function scopeAbiertos($query)
    {
        return $query->where('cerrado', false);
    }

    /**
     * Scope para obtener cuadres cerrados
     */
    public function scopeCerrados($query)
    {
        return $query->where('cerrado', true);
    }
}
