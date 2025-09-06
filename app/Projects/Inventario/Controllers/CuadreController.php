<?php

namespace App\Projects\Inventario\Controllers;

use App\Core\Controllers\BaseController;
use App\Projects\Inventario\Models\Cuadre;
use App\Projects\Inventario\Models\InventoryMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CuadreController extends BaseController
{
    /**
     * Obtener el saldo anterior para un nuevo cuadre
     */
    public function saldoAnterior(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        // Buscar el último cuadre cerrado
        $ultimoCuadre = Cuadre::where('organization_id', $organizationId)
            ->where('cerrado', true)
            ->orderBy('fecha', 'desc')
            ->first();

        $saldoAnterior = 0;
        
        if ($ultimoCuadre) {
            // Si hay un cuadre anterior, usar su saldo físico o calculado
            $saldoAnterior = $ultimoCuadre->saldo_fisico ?? $ultimoCuadre->saldo_calculado;
        }

        return $this->successResponse([
            'saldo_anterior' => $saldoAnterior,
            'fecha_ultimo_cuadre' => $ultimoCuadre?->fecha,
            'ultimo_cuadre_id' => $ultimoCuadre?->id
        ]);
    }

    /**
     * Obtener estadísticas de movimientos para un día específico
     */
    public function estadisticasDia(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        $fecha = $request->get('fecha', now()->format('Y-m-d'));

        // Validar formato de fecha
        try {
            $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
        } catch (\Exception $e) {
            return $this->errorResponse('Formato de fecha inválido. Use YYYY-MM-DD', 400);
        }

        // Aquí deberías adaptar según tu lógica de negocio
        // Por ejemplo, si tienes movimientos de ventas/compras en otra tabla
        $estadisticas = [
            'fecha' => $fecha,
            'ingresos' => [
                'efectivo' => 0,
                'transferencia' => 0,
                'tarjeta' => 0,
                'total' => 0
            ],
            'egresos' => [
                'efectivo' => 0,
                'transferencia' => 0,
                'tarjeta' => 0,
                'total' => 0
            ],
            'movimientos_count' => 0
        ];

        // Si tienes una tabla de ventas o movimientos de caja, aquí calcularías las estadísticas reales
        // Por ahora devolvemos estructura vacía
        
        return $this->successResponse($estadisticas);
    }

    /**
     * Obtener historial de cuadres
     */
    public function historial(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        $limit = $request->get('limit', 10);

        $cuadres = Cuadre::where('organization_id', $organizationId)
            ->with(['creador:id,first_name,last_name', 'cerrador:id,first_name,last_name'])
            ->orderBy('fecha', 'desc')
            ->limit($limit)
            ->get();

        return $this->successResponse($cuadres);
    }

    /**
     * Crear un nuevo cuadre
     */
    public function store(Request $request): JsonResponse
    {

        dd('--- IGNORE ---');
        $organizationId = $request->header('Organization-Id');
        
        $validated = $request->validate([
            'fecha' => 'required|date|date_format:Y-m-d',
            'saldo_anterior' => 'required|numeric|min:0',
            'ingresos_efectivo' => 'required|numeric|min:0',
            'ingresos_transferencia' => 'required|numeric|min:0',
            'ingresos_tarjeta' => 'required|numeric|min:0',
            'egresos_efectivo' => 'required|numeric|min:0',
            'egresos_transferencia' => 'required|numeric|min:0',
            'egresos_tarjeta' => 'required|numeric|min:0',
            'saldo_fisico' => 'nullable|numeric',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            // Verificar que no existe un cuadre para esa fecha
            $cuadreExistente = Cuadre::where('organization_id', $organizationId)
                ->where('fecha', $validated['fecha'])
                ->first();

            if ($cuadreExistente) {
                return $this->errorResponse('Ya existe un cuadre para la fecha ' . $validated['fecha'], 400);
            }

            $validated['organization_id'] = $organizationId;
            $validated['creado_por'] = $request->user()->id ?? null;

            $cuadre = Cuadre::create($validated);
            $cuadre->load(['creador', 'cerrador']);

            return $this->successResponse($cuadre, 'Cuadre creado exitosamente', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear el cuadre: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Validar si se puede crear un cuadre para una fecha
     */
    public function validarFecha(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        $fecha = $request->get('fecha');

        if (!$fecha) {
            return $this->errorResponse('Parámetro fecha requerido', 400);
        }

        // Validar formato de fecha
        try {
            $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
        } catch (\Exception $e) {
            return $this->errorResponse('Formato de fecha inválido. Use YYYY-MM-DD', 400);
        }

        // Verificar si ya existe un cuadre para esa fecha
        $cuadreExistente = Cuadre::where('organization_id', $organizationId)
            ->where('fecha', $fecha)
            ->first();

        $valida = !$cuadreExistente;

        return $this->successResponse([
            'fecha' => $fecha,
            'valida' => $valida,
            'mensaje' => $valida ? 'Fecha disponible para crear cuadre' : 'Ya existe un cuadre para esta fecha',
            'cuadre_existente' => $cuadreExistente ? [
                'id' => $cuadreExistente->id,
                'cerrado' => $cuadreExistente->cerrado,
                'saldo_calculado' => $cuadreExistente->saldo_calculado
            ] : null
        ]);
    }

    /**
     * Obtener cuadre por fecha
     */
    public function porFecha(Request $request, string $fecha): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');

        // Validar formato de fecha
        try {
            $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
        } catch (\Exception $e) {
            return $this->errorResponse('Formato de fecha inválido. Use YYYY-MM-DD', 400);
        }

        $cuadre = Cuadre::where('organization_id', $organizationId)
            ->where('fecha', $fecha)
            ->with(['creador:id,first_name,last_name', 'cerrador:id,first_name,last_name'])
            ->first();

        if (!$cuadre) {
            return $this->errorResponse('No se encontró cuadre para la fecha ' . $fecha, 404);
        }

        return $this->successResponse($cuadre);
    }

    /**
     * Actualizar un cuadre
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $cuadre = Cuadre::where('organization_id', $organizationId)->findOrFail($id);

        // No permitir actualizar cuadres cerrados
        if ($cuadre->cerrado) {
            return $this->errorResponse('No se puede modificar un cuadre cerrado', 400);
        }

        $validated = $request->validate([
            'saldo_anterior' => 'sometimes|numeric|min:0',
            'ingresos_efectivo' => 'sometimes|numeric|min:0',
            'ingresos_transferencia' => 'sometimes|numeric|min:0',
            'ingresos_tarjeta' => 'sometimes|numeric|min:0',
            'egresos_efectivo' => 'sometimes|numeric|min:0',
            'egresos_transferencia' => 'sometimes|numeric|min:0',
            'egresos_tarjeta' => 'sometimes|numeric|min:0',
            'saldo_fisico' => 'nullable|numeric',
            'observaciones' => 'nullable|string|max:1000',
            'cerrado' => 'sometimes|boolean',
        ]);

        // Si se está cerrando el cuadre
        if (isset($validated['cerrado']) && $validated['cerrado'] && !$cuadre->cerrado) {
            $validated['cerrado_por'] = $request->user()->id ?? null;
            $validated['fecha_cierre'] = now();
        }

        $cuadre->update($validated);
        $cuadre->load(['creador', 'cerrador']);

        return $this->successResponse($cuadre, 'Cuadre actualizado exitosamente');
    }

    /**
     * Eliminar un cuadre
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $cuadre = Cuadre::where('organization_id', $organizationId)->findOrFail($id);

        // No permitir eliminar cuadres cerrados
        if ($cuadre->cerrado) {
            return $this->errorResponse('No se puede eliminar un cuadre cerrado', 400);
        }

        $cuadre->delete();

        return $this->successResponse(null, 'Cuadre eliminado exitosamente');
    }
}
