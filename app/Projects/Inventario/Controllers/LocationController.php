<?php

namespace App\Projects\Inventario\Controllers;

use App\Core\Controllers\BaseController;
use App\Projects\Inventario\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends BaseController
{
    /**
     * Listar ubicaciones de una organización
     */
    public function index(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        if (!$organizationId) {
            return $this->errorResponse('Organization-Id header requerido', 400);
        }

        $query = Location::with(['inventoryStock.product'])
            ->where('organization_id', $organizationId);

        // Filtros opcionales
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('code', 'ILIKE', "%{$search}%");
            });
        }

        $locations = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return $this->successResponse($locations);
    }

    /**
     * Mostrar una ubicación específica
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $location = Location::with([
            'inventoryStock.product',
            'inventoryStock' => function($query) {
                $query->where('quantity', '>', 0);
            }
        ])
        ->where('organization_id', $organizationId)
        ->findOrFail($id);

        // Agregar estadísticas
        $location->stats = [
            'total_products' => $location->inventoryStock->count(),
            'total_value' => $location->inventoryStock->sum(function($stock) {
                return $stock->quantity * $stock->average_cost;
            }),
            'low_stock_products' => $location->inventoryStock->filter(function($stock) {
                return $stock->quantity <= $stock->product->min_stock;
            })->count()
        ];

        return $this->successResponse($location);
    }

    /**
     * Crear una nueva ubicación
     */
    public function store(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('inventario.locations')->where('organization_id', $organizationId)
            ],
            'address' => 'nullable|string',
            'type' => 'required|string|in:warehouse,store,kitchen,office,production,other',
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        $validated['organization_id'] = $organizationId;

        $location = Location::create($validated);

        return $this->successResponse($location, 'Ubicación creada exitosamente', 201);
    }

    /**
     * Actualizar una ubicación
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $location = Location::where('organization_id', $organizationId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('inventario.locations')->where('organization_id', $organizationId)->ignore($id)
            ],
            'address' => 'nullable|string',
            'type' => 'sometimes|string|in:warehouse,store,kitchen,office,production,other',
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        $location->update($validated);

        return $this->successResponse($location, 'Ubicación actualizada exitosamente');
    }

    /**
     * Eliminar una ubicación
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $location = Location::where('organization_id', $organizationId)->findOrFail($id);
        
        // Verificar si tiene stock antes de eliminar
        $hasStock = $location->inventoryStock()->where('quantity', '>', 0)->exists();
        if ($hasStock) {
            return $this->errorResponse('No se puede eliminar una ubicación con stock existente', 400);
        }

        $location->delete();

        return $this->successResponse(null, 'Ubicación eliminada exitosamente');
    }

    /**
     * Obtener stock por ubicación
     */
    public function stock(Request $request, string $id): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $location = Location::where('organization_id', $organizationId)->findOrFail($id);
        
        $query = $location->inventoryStock()
            ->with(['product.category', 'product.supplier'])
            ->where('quantity', '>', 0);

        // Filtros opcionales
        if ($request->has('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->has('low_stock')) {
            $query->whereRaw('quantity <= products.min_stock');
        }

        $stock = $query->orderBy('updated_at', 'desc')->paginate($request->get('per_page', 20));

        return $this->successResponse($stock);
    }

    /**
     * Transferir stock entre ubicaciones
     */
    public function transfer(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $validated = $request->validate([
            'from_location_id' => 'required|uuid|exists:inventario.locations,id',
            'to_location_id' => 'required|uuid|exists:inventario.locations,id|different:from_location_id',
            'product_id' => 'required|uuid|exists:inventario.products,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        // Verificar que ambas ubicaciones pertenezcan a la organización
        $fromLocation = Location::where('organization_id', $organizationId)
            ->findOrFail($validated['from_location_id']);
        
        $toLocation = Location::where('organization_id', $organizationId)
            ->findOrFail($validated['to_location_id']);

        // TODO: Implementar lógica de transferencia con InventoryMovement
        // Por ahora retornamos éxito para la estructura básica
        
        return $this->successResponse([
            'from_location' => $fromLocation->name,
            'to_location' => $toLocation->name,
            'quantity' => $validated['quantity'],
            'status' => 'pending'
        ], 'Transferencia iniciada exitosamente');
    }
}
