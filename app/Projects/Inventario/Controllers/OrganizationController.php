<?php

namespace App\Projects\Inventario\Controllers;

use App\Core\Controllers\BaseController;
use App\Projects\Inventario\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends BaseController
{
    /**
     * Listar organizaciones
     */
    public function index(): JsonResponse
    {
        $organizations = Organization::with(['members', 'locations'])
            ->paginate(15);

        return $this->successResponse($organizations);
    }

    /**
     * Mostrar una organización específica
     */
    public function show(string $id): JsonResponse
    {
        $organization = Organization::with([
            'members.user',
            'locations',
            'categories',
            'products',
            'suppliers'
        ])->findOrFail($id);

        return $this->successResponse($organization);
    }

    /**
     * Crear una nueva organización
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:organizations,slug',
            'domain' => 'nullable|string|max:255',
            'plan_type' => 'nullable|string|in:basic,pro,enterprise',
            'settings' => 'nullable|array',
        ]);

        $organization = Organization::create($validated);

        return $this->successResponse($organization, 'Organización creada exitosamente', 201);
    }

    /**
     * Actualizar una organización
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $organization = Organization::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:100|unique:organizations,slug,' . $id,
            'domain' => 'nullable|string|max:255',
            'plan_type' => 'nullable|string|in:basic,pro,enterprise',
            'status' => 'nullable|string|in:active,suspended,cancelled',
            'settings' => 'nullable|array',
        ]);

        $organization->update($validated);

        return $this->successResponse($organization, 'Organización actualizada exitosamente');
    }

    /**
     * Eliminar una organización
     */
    public function destroy(string $id): JsonResponse
    {
        $organization = Organization::findOrFail($id);
        $organization->delete();

        return $this->successResponse(null, 'Organización eliminada exitosamente');
    }
}
