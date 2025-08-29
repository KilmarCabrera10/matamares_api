<?php

namespace App\Projects\Inventario\Controllers;

use App\Core\Controllers\BaseController;
use App\Projects\Inventario\Models\InventoryStock;
use App\Projects\Inventario\Models\InventoryMovement;
use App\Projects\Inventario\Models\Product;
use App\Projects\Inventario\Models\Location;
use App\Projects\Inventario\Models\TransactionType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends BaseController
{
    /**
     * Obtener resumen de inventario por organización
     */
    public function dashboard(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        if (!$organizationId) {
            return $this->errorResponse('Organization-Id header requerido', 400);
        }

        $dashboard = [
            'total_products' => Product::where('organization_id', $organizationId)
                ->where('is_active', true)->count(),
            
            'total_locations' => Location::where('organization_id', $organizationId)
                ->where('is_active', true)->count(),
            
            'total_stock_value' => InventoryStock::where('organization_id', $organizationId)
                ->sum(DB::raw('quantity * average_cost')),
            
            'low_stock_products' => InventoryStock::where('organization_id', $organizationId)
                ->whereHas('product', function($query) {
                    $query->whereRaw('inventory_stock.quantity <= products.min_stock');
                })->count(),
            
            'recent_movements' => InventoryMovement::with([
                'product:id,name,sku',
                'location:id,name',
                'transactionType:id,name,category'
            ])
                ->where('organization_id', $organizationId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            
            'stock_by_location' => Location::where('organization_id', $organizationId)
                ->withCount(['inventoryStock as products_count'])
                ->with(['inventoryStock' => function($query) {
                    $query->selectRaw('location_id, SUM(quantity * average_cost) as total_value')
                        ->groupBy('location_id');
                }])
                ->get()
        ];

        return $this->successResponse($dashboard);
    }

    /**
     * Obtener stock detallado
     */
    public function stock(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $query = InventoryStock::with([
            'product.category',
            'product.supplier',
            'location'
        ])->where('organization_id', $organizationId);

        // Filtros
        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->has('low_stock')) {
            $query->whereHas('product', function($q) {
                $q->whereRaw('inventory_stock.quantity <= products.min_stock');
            });
        }

        if ($request->has('zero_stock')) {
            $query->where('quantity', $request->boolean('zero_stock') ? 0 : '>', 0);
        }

        $stock = $query->orderBy('updated_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return $this->successResponse($stock);
    }

    /**
     * Obtener movimientos de inventario
     */
    public function movements(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $query = InventoryMovement::with([
            'product:id,name,sku',
            'location:id,name,code',
            'transactionType:id,name,category',
            'creator:id,first_name,last_name'
        ])->where('organization_id', $organizationId);

        // Filtros
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->has('transaction_type_id')) {
            $query->where('transaction_type_id', $request->transaction_type_id);
        }

        if ($request->has('category')) {
            $query->whereHas('transactionType', function($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return $this->successResponse($movements);
    }

    /**
     * Crear un movimiento de inventario (entrada/salida/ajuste)
     */
    public function createMovement(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $validated = $request->validate([
            'product_id' => 'required|uuid|exists:inventario.products,id',
            'location_id' => 'required|uuid|exists:inventario.locations,id',
            'transaction_type_id' => 'required|uuid|exists:inventario.transaction_types,id',
            'quantity' => 'required|numeric',
            'unit_cost' => 'required|numeric|min:0',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|uuid',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::connection('inventario')->beginTransaction();

            // Verificar que el producto y ubicación pertenezcan a la organización
            $product = Product::where('organization_id', $organizationId)
                ->findOrFail($validated['product_id']);
            
            $location = Location::where('organization_id', $organizationId)
                ->findOrFail($validated['location_id']);

            $transactionType = TransactionType::findOrFail($validated['transaction_type_id']);

            // Obtener o crear stock actual
            $stock = InventoryStock::firstOrCreate([
                'organization_id' => $organizationId,
                'product_id' => $validated['product_id'],
                'location_id' => $validated['location_id'],
            ], [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'average_cost' => 0,
            ]);

            $balanceBefore = $stock->quantity;
            $movementQuantity = $validated['quantity'];

            // Ajustar cantidad según el tipo de transacción
            if (in_array($transactionType->category, ['out', 'adjustment']) && $movementQuantity > 0) {
                $movementQuantity = -$movementQuantity;
            }

            $balanceAfter = $balanceBefore + $movementQuantity;

            // Validar que no quede en negativo
            if ($balanceAfter < 0) {
                return $this->errorResponse('Stock insuficiente para realizar el movimiento', 400);
            }

            // Generar número de transacción único
            $transactionNumber = 'TXN-' . date('Ymd') . '-' . str_pad(
                InventoryMovement::where('organization_id', $organizationId)
                    ->whereDate('created_at', today())->count() + 1,
                4, '0', STR_PAD_LEFT
            );

            // Crear el movimiento
            $movement = InventoryMovement::create([
                'organization_id' => $organizationId,
                'transaction_number' => $transactionNumber,
                'transaction_type_id' => $validated['transaction_type_id'],
                'reference_type' => $validated['reference_type'],
                'reference_id' => $validated['reference_id'],
                'product_id' => $validated['product_id'],
                'location_id' => $validated['location_id'],
                'quantity' => $movementQuantity,
                'unit_cost' => $validated['unit_cost'],
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'notes' => $validated['notes'],
                'created_by' => $request->user()->id ?? null,
            ]);

            // Actualizar stock
            if ($transactionType->affects_cost && $movementQuantity > 0) {
                // Calcular costo promedio ponderado para entradas
                $totalCost = ($stock->quantity * $stock->average_cost) + 
                           ($movementQuantity * $validated['unit_cost']);
                $totalQuantity = $stock->quantity + $movementQuantity;
                $newAverageCost = $totalQuantity > 0 ? $totalCost / $totalQuantity : $validated['unit_cost'];
                
                $stock->update([
                    'quantity' => $balanceAfter,
                    'average_cost' => $newAverageCost,
                    'last_movement_at' => now(),
                ]);
            } else {
                $stock->update([
                    'quantity' => $balanceAfter,
                    'last_movement_at' => now(),
                ]);
            }

            DB::connection('inventario')->commit();

            $movement->load(['product', 'location', 'transactionType']);

            return $this->successResponse($movement, 'Movimiento creado exitosamente', 201);

        } catch (\Exception $e) {
            DB::connection('inventario')->rollBack();
            return $this->errorResponse('Error al crear el movimiento: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener tipos de transacciones disponibles
     */
    public function transactionTypes(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        
        $transactionTypes = TransactionType::where(function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId)
                  ->orWhere('is_system', true);
        })
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

        return $this->successResponse($transactionTypes);
    }

    /**
     * Reportes de inventario
     */
    public function reports(Request $request): JsonResponse
    {
        $organizationId = $request->header('Organization-Id');
        $type = $request->get('type', 'summary');

        switch ($type) {
            case 'valuation':
                $data = $this->getValuationReport($organizationId, $request);
                break;
            case 'movement':
                $data = $this->getMovementReport($organizationId, $request);
                break;
            case 'low_stock':
                $data = $this->getLowStockReport($organizationId);
                break;
            default:
                $data = $this->getSummaryReport($organizationId);
        }

        return $this->successResponse($data);
    }

    private function getSummaryReport($organizationId)
    {
        return [
            'total_products' => Product::where('organization_id', $organizationId)->count(),
            'active_products' => Product::where('organization_id', $organizationId)->where('is_active', true)->count(),
            'total_stock_value' => InventoryStock::where('organization_id', $organizationId)
                ->sum(DB::raw('quantity * average_cost')),
            'locations_count' => Location::where('organization_id', $organizationId)->count(),
        ];
    }

    private function getValuationReport($organizationId, $request)
    {
        return InventoryStock::with(['product.category', 'location'])
            ->where('organization_id', $organizationId)
            ->where('quantity', '>', 0)
            ->selectRaw('*, (quantity * average_cost) as total_value')
            ->orderBy('total_value', 'desc')
            ->get();
    }

    private function getMovementReport($organizationId, $request)
    {
        $query = InventoryMovement::with(['product', 'location', 'transactionType'])
            ->where('organization_id', $organizationId);

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    private function getLowStockReport($organizationId)
    {
        return InventoryStock::with(['product.category', 'location'])
            ->where('organization_id', $organizationId)
            ->whereHas('product', function($query) {
                $query->whereRaw('inventory_stock.quantity <= products.min_stock');
            })
            ->orderBy('quantity')
            ->get();
    }
}
