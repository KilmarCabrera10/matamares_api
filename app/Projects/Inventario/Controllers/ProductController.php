<?php

namespace App\Projects\Inventario\Controllers;

use App\Core\Controllers\BaseController;
use App\Projects\Inventario\Models\Product;
use App\Projects\Inventario\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends BaseController
{
    /**
     * Listar productos de una organización
     */
    public function index(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        if (!$organizationId) {
            return $this->errorResponse('Organization-Id header requerido', 400);
        }

        $query = Product::with(['category', 'supplier', 'creator', 'inventoryStock.location'])
            ->where('organization_id', $organizationId);

        // Filtros opcionales
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('sku', 'ILIKE', "%{$search}%")
                  ->orWhere('barcode', 'ILIKE', "%{$search}%");
            });
        }

        $products = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($products);
    }

    /**
     * Mostrar un producto específico
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $product = Product::with([
            'category', 
            'supplier', 
            'creator',
            'inventoryStock.location',
            'movements' => function($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }
        ])
        ->where('organization_id', $organizationId)
        ->findOrFail($id);

        return $this->successResponse($product);
    }

    /**
     * Crear un nuevo producto
     */
    public function store(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $validated = $request->validate([
            'category_id' => 'nullable|uuid|exists:inventario.categories,id',
            'supplier_id' => 'nullable|uuid|exists:inventario.suppliers,id',
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('inventario.products')->where('organization_id', $organizationId)
            ],
            'barcode' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_type' => 'required|string|in:piece,weight,volume,length,area',
            'unit_name' => 'required|string|max:50',
            'unit_precision' => 'integer|min:0|max:4',
            'cost_price' => 'numeric|min:0',
            'selling_price' => 'numeric|min:0',
            'currency' => 'string|size:3',
            'track_inventory' => 'boolean',
            'min_stock' => 'numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'nullable|numeric|min:0',
            'track_expiry' => 'boolean',
            'track_batches' => 'boolean',
            'shelf_life_days' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_sellable' => 'boolean',
            'is_purchasable' => 'boolean',
            'attributes' => 'nullable|array',
        ]);

        $validated['organization_id'] = $organizationId;
        $validated['created_by'] = $request->user()->id ?? null;

        $product = Product::create($validated);
        $product->load(['category', 'supplier']);

        return $this->successResponse($product, 'Producto creado exitosamente', 201);
    }

    /**
     * Actualizar un producto
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $product = Product::where('organization_id', $organizationId)->findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'nullable|uuid|exists:inventario.categories,id',
            'supplier_id' => 'nullable|uuid|exists:inventario.suppliers,id',
            'sku' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('inventario.products')->where('organization_id', $organizationId)->ignore($id)
            ],
            'barcode' => 'nullable|string|max:100',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'unit_type' => 'sometimes|string|in:piece,weight,volume,length,area',
            'unit_name' => 'sometimes|string|max:50',
            'unit_precision' => 'integer|min:0|max:4',
            'cost_price' => 'numeric|min:0',
            'selling_price' => 'numeric|min:0',
            'currency' => 'string|size:3',
            'track_inventory' => 'boolean',
            'min_stock' => 'numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'nullable|numeric|min:0',
            'track_expiry' => 'boolean',
            'track_batches' => 'boolean',
            'shelf_life_days' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_sellable' => 'boolean',
            'is_purchasable' => 'boolean',
            'attributes' => 'nullable|array',
        ]);

        $product->update($validated);
        $product->load(['category', 'supplier']);

        return $this->successResponse($product, 'Producto actualizado exitosamente');
    }

    /**
     * Eliminar un producto
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $product = Product::where('organization_id', $organizationId)->findOrFail($id);
        
        // Verificar si tiene stock antes de eliminar
        $hasStock = $product->inventoryStock()->where('quantity', '>', 0)->exists();
        if ($hasStock) {
            return $this->errorResponse('No se puede eliminar un producto con stock existente', 400);
        }

        $product->delete();

        return $this->successResponse(null, 'Producto eliminado exitosamente');
    }

    /**
     * Obtener productos con stock bajo
     */
    public function lowStock(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $products = Product::with(['inventoryStock.location'])
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->whereHas('inventoryStock', function($query) {
                $query->whereRaw('quantity <= products.min_stock');
            })
            ->get();

        return $this->successResponse($products);
    }
}
