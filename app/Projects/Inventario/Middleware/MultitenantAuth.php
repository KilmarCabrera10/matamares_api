<?php

namespace App\Projects\Inventario\Middleware;

use App\Projects\Inventario\Models\Organization;
use App\Projects\Inventario\Models\OrganizationMember;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MultitenantAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar autenticación
        if (!Auth::check()) {
            return response()->json([
                'error' => 'No autenticado',
                'message' => 'Se requiere autenticación para acceder a este recurso'
            ], 401);
        }

        // Obtener Organization-Id del header
        $organizationId = $request->header('Organization-Id');
        
        if (!$organizationId) {
            return response()->json([
                'error' => 'Organization-Id requerido',
                'message' => 'Debe proporcionar el header Organization-Id'
            ], 400);
        }

        // Verificar que la organización existe y está activa
        $organization = Organization::where('id', $organizationId)
            ->where('is_active', true)
            ->first();

        if (!$organization) {
            return response()->json([
                'error' => 'Organización no válida',
                'message' => 'La organización especificada no existe o está inactiva'
            ], 404);
        }

        // Verificar que el usuario pertenece a la organización
        $user = Auth::user();
        $hasAccess = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'error' => 'Acceso denegado',
                'message' => 'No tiene permisos para acceder a esta organización'
            ], 403);
        }

        // Agregar la organización al request para uso posterior
        $request->attributes->set('organization', $organization);
        $request->attributes->set('organization_id', $organizationId);

        return $next($request);
    }
}
