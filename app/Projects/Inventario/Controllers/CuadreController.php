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
            'saldo' => $saldoAnterior,
        ]);
    }

    /**
     * Obtener estadísticas de movimientos para un día específico
     */
    public function estadisticasDia(Request $request): JsonResponse {
        $organizationId = $request->header('Organization-Id');
        $fecha = $request->get('fecha', now()->format('Y-m-d'));

        // Validar formato de fecha
        try {
            $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
        } catch (\Exception $e) {
            return $this->errorResponse('Formato de fecha inválido. Use YYYY-MM-DD', 400);
        }

        // Buscar el cuadre para esa fecha
        $cuadre = Cuadre::where('organization_id', $organizationId)
            ->where('fecha', $fecha)
            ->first();

        $ingresos = 0;
        $gastos = 0;

        if ($cuadre) {
            // Calcular ingresos totales
            $ingresos = $cuadre->ingresos_efectivo + 
                       $cuadre->ingresos_transferencia + 
                       $cuadre->ingresos_tarjeta;

            // Calcular gastos/egresos totales
            $gastos = $cuadre->egresos_efectivo + 
                     $cuadre->egresos_transferencia + 
                     $cuadre->egresos_tarjeta;
        }

        // Calcular diferencia
        $diferencia = $ingresos - $gastos;

        $estadisticas = [
            'ingresos' => (float) $ingresos,
            'gastos' => (float) $gastos,
            'diferencia' => (float) $diferencia
        ];

        return $this->successResponse($estadisticas);
    }

    /**
     * Obtener historial de cuadres
     */
    public function historial(Request $request): JsonResponse {
        $organizationId = $request->header('Organization-Id');
        $limit = $request->get('limit', 3);

        $cuadres = Cuadre::where('organization_id', $organizationId)
            ->with(['creador:id,first_name,last_name'])
            ->orderBy('fecha', 'desc')
            ->limit($limit)
            ->get();

        // Transformar los datos al formato esperado por el frontend
        $historialTransformado = $cuadres->map(function ($cuadre) {
            return [
                'id' => $cuadre->id,
                'fecha' => $cuadre->fecha,
                'saldoInicial' => (float) $cuadre->saldo_anterior,
                'efectivoReal' => (float) $cuadre->saldo_fisico,
                'diferencia' => (float) $cuadre->diferencia,
                'observaciones' => $cuadre->observaciones ?? '',
                'usuarioId' => $cuadre->creado_por,
                'usuario' => $cuadre->creador 
                    ? trim($cuadre->creador->first_name . ' ' . $cuadre->creador->last_name)
                    : 'Usuario no disponible',
                'createdAt' => $cuadre->created_at?->toISOString(),
                'updatedAt' => $cuadre->updated_at?->toISOString(),
            ];
        });

        return $this->successResponse($historialTransformado);
    }

    /**
     * Crear un nuevo cuadre
     */
    public function store(Request $request): JsonResponse{
        $organizationId = $request->header('Organization-Id');
        
        // Verificar que el usuario esté autenticado
        if (!$request->user()) {
            return $this->errorResponse('Usuario no autenticado', 401);
        }
        
        $validated = $request->validate([
            'fecha'                                    => 'required|date',
            'cuadre.saldoInicial'                      => 'required|numeric|min:0',
            'cuadre.observaciones'                     => 'nullable|string|max:1000',
            'conteoEfectivo.billetes.cien'             => 'numeric|min:0',
            'conteoEfectivo.billetes.cincuenta'        => 'numeric|min:0',
            'conteoEfectivo.billetes.veinte'           => 'numeric|min:0',
            'conteoEfectivo.billetes.diez'             => 'numeric|min:0',
            'conteoEfectivo.billetes.cinco'            => 'numeric|min:0',
            'conteoEfectivo.billetes.dos'              => 'numeric|min:0',
            'conteoEfectivo.billetes.uno'              => 'numeric|min:0',
            'conteoEfectivo.monedas.dollar'            => 'numeric|min:0',
            'conteoEfectivo.monedas.cincuentaCentavos' => 'numeric|min:0',
            'conteoEfectivo.monedas.veinticinco'       => 'numeric|min:0',
            'conteoEfectivo.monedas.diez'              => 'numeric|min:0',
            'conteoEfectivo.monedas.cinco'             => 'numeric|min:0',
            'conteoEfectivo.monedas.uno'               => 'numeric|min:0',
            'totalEfectivoContado'                     => 'required|numeric|min:0',
        ]);

        try {
            // Convertir fecha ISO a formato Y-m-d
            $fecha = Carbon::parse($validated['fecha'])->format('Y-m-d');
            
            // Verificar que no existe un cuadre para esa fecha
            $cuadreExistente = Cuadre::where('organization_id', $organizationId)
                ->where('fecha', $fecha)
                ->first();

            if ($cuadreExistente) {
                //return $this->errorResponse('Ya existe un cuadre para la fecha ' . $fecha, 400);
            }

            // Calcular saldo físico del conteo de efectivo
            $billetes = $validated['conteoEfectivo']['billetes'];
            $monedas = $validated['conteoEfectivo']['monedas'];
            
            $saldoFisico = 
                ($billetes['cien']             * 100)  +
                ($billetes['cincuenta']        * 50)   +
                ($billetes['veinte']           * 20)   +
                ($billetes['diez']             * 10)   +
                ($billetes['cinco']            * 5)    +
                ($billetes['dos']              * 2)    +
                ($billetes['uno']              * 1)    +
                ($monedas['dollar']            * 1)    +
                ($monedas['cincuentaCentavos'] * 0.50) +
                ($monedas['veinticinco']       * 0.25) +
                ($monedas['diez']              * 0.10) +
                ($monedas['cinco']             * 0.05) +
                ($monedas['uno']               * 0.01);

            // Preparar datos para crear el cuadre
            $cuadreData = [
                'organization_id'        => $organizationId,
                'fecha'                  => $fecha,
                'saldo_anterior'         => $validated['cuadre']['saldoInicial'],
                'saldo_fisico'           => $saldoFisico,
                'observaciones'          => $validated['cuadre']['observaciones'],
                'creado_por'             => $request->user()->id,
                // Para MVP, inicializar ingresos y egresos en 0
                'ingresos_efectivo'      => 0,
                'ingresos_transferencia' => 0,
                'ingresos_tarjeta'       => 0,
                'egresos_efectivo'       => 0,
                'egresos_transferencia'  => 0,
                'egresos_tarjeta'        => 0,
                'cerrado'                => true,
                'cerrado_por'            => $request->user()->id,
            ];

            $cuadre = Cuadre::create($cuadreData);
            $cuadre->load(['creador', 'cerrador']);

            return $this->successResponse([
                'cuadre' => $cuadre,
                'conteo_detalle' => [
                    'billetes'        => $billetes,
                    'monedas'         => $monedas,
                    'total_calculado' => $saldoFisico
                ]
            ], 'Cuadre creado exitosamente', 201);

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
