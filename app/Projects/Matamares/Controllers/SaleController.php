<?php

namespace App\Projects\Matamares\Controllers;

use App\Core\Controllers\Controller;
use App\Projects\Matamares\Models\Sale;
use App\Projects\Matamares\Models\SaleItem;
use App\Projects\Matamares\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    /**
     * Display a listing of sales.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'user', 'items.product']);

        // Date range filter
        if ($request->has('startDate') && $request->has('endDate')) {
            $query->dateRange($request->startDate, $request->endDate);
        }

        // User filter
        if ($request->has('userId')) {
            $query->byUser($request->userId);
        }

        // Pagination
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 25);

        $sales = $query->orderBy('created_at', 'desc')
                      ->paginate($limit, ['*'], 'page', $page);

        $formattedSales = $sales->getCollection()->map(function ($sale) {
            return [
                'id' => $sale->id,
                'invoiceNumber' => $sale->invoice_number,
                'customerId' => $sale->customer_id,
                'customer' => $sale->customer ? [
                    'id' => $sale->customer->id,
                    'name' => $sale->customer->name,
                    'document' => $sale->customer->document,
                ] : null,
                'userId' => $sale->user_id,
                'user' => [
                    'id' => $sale->user->id,
                    'name' => $sale->user->name,
                ],
                'items' => $sale->items->map(function ($item) {
                    return [
                        'productId' => $item->product_id,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'code' => $item->product->code,
                        ],
                        'quantity' => $item->quantity,
                        'unitPrice' => $item->unit_price,
                        'totalPrice' => $item->total_price,
                    ];
                }),
                'subtotal' => $sale->subtotal,
                'tax' => $sale->tax,
                'discount' => $sale->discount,
                'total' => $sale->total,
                'paymentMethod' => $sale->payment_method,
                'status' => $sale->status,
                'createdAt' => $sale->created_at->toISOString(),
                'updatedAt' => $sale->updated_at->toISOString(),
            ];
        });

        return response()->json([
            'sales' => $formattedSales,
            'pagination' => [
                'page' => $sales->currentPage(),
                'limit' => $sales->perPage(),
                'total' => $sales->total(),
                'pages' => $sales->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created sale.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customerId' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.productId' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unitPrice' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'paymentMethod' => 'required|in:efectivo,tarjeta,transferencia,mixto',
        ]);

        DB::beginTransaction();

        try {
            // Create the sale
            $sale = Sale::create([
                'invoice_number' => Sale::generateInvoiceNumber(),
                'customer_id' => $request->customerId,
                'user_id' => Auth::id(),
                'subtotal' => $request->subtotal,
                'tax' => $request->tax ?? 0,
                'discount' => $request->discount ?? 0,
                'total' => $request->total,
                'payment_method' => $request->paymentMethod,
                'status' => 'completada',
            ]);

            // Create sale items and update stock
            foreach ($request->items as $itemData) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $itemData['productId'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unitPrice'],
                    'total_price' => $itemData['quantity'] * $itemData['unitPrice'],
                ]);

                // Update product stock
                $product = Product::find($itemData['productId']);
                $product->decrement('stock', $itemData['quantity']);
            }

            DB::commit();

            // Load relationships for response
            $sale->load(['customer', 'user', 'items.product']);

            return response()->json([
                'sale' => [
                    'id' => $sale->id,
                    'invoiceNumber' => $sale->invoice_number,
                    'customerId' => $sale->customer_id,
                    'userId' => $sale->user_id,
                    'subtotal' => $sale->subtotal,
                    'tax' => $sale->tax,
                    'discount' => $sale->discount,
                    'total' => $sale->total,
                    'paymentMethod' => $sale->payment_method,
                    'status' => $sale->status,
                    'createdAt' => $sale->created_at->toISOString(),
                ],
                'message' => 'Venta creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Error al procesar la venta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product']);

        return response()->json([
            'sale' => [
                'id' => $sale->id,
                'invoiceNumber' => $sale->invoice_number,
                'customerId' => $sale->customer_id,
                'customer' => $sale->customer ? [
                    'id' => $sale->customer->id,
                    'name' => $sale->customer->name,
                    'document' => $sale->customer->document,
                ] : null,
                'userId' => $sale->user_id,
                'user' => [
                    'id' => $sale->user->id,
                    'name' => $sale->user->name,
                ],
                'items' => $sale->items->map(function ($item) {
                    return [
                        'productId' => $item->product_id,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'code' => $item->product->code,
                        ],
                        'quantity' => $item->quantity,
                        'unitPrice' => $item->unit_price,
                        'totalPrice' => $item->total_price,
                    ];
                }),
                'subtotal' => $sale->subtotal,
                'tax' => $sale->tax,
                'discount' => $sale->discount,
                'total' => $sale->total,
                'paymentMethod' => $sale->payment_method,
                'status' => $sale->status,
                'createdAt' => $sale->created_at->toISOString(),
                'updatedAt' => $sale->updated_at->toISOString(),
            ]
        ]);
    }

    /**
     * Get today's sales statistics.
     */
    public function todayStats()
    {
        $today = now()->format('Y-m-d');
        
        $todaySales = Sale::whereDate('created_at', $today)
                         ->with('items.product')
                         ->get();

        $totalSales = $todaySales->count();
        $totalRevenue = $todaySales->sum('total');
        $averageTicket = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        // Get top products
        $productSales = [];
        foreach ($todaySales as $sale) {
            foreach ($sale->items as $item) {
                $productId = $item->product_id;
                if (!isset($productSales[$productId])) {
                    $productSales[$productId] = [
                        'productId' => $productId,
                        'productName' => $item->product->name,
                        'quantitySold' => 0,
                    ];
                }
                $productSales[$productId]['quantitySold'] += $item->quantity;
            }
        }

        $topProducts = collect($productSales)
                      ->sortByDesc('quantitySold')
                      ->take(5)
                      ->values();

        return response()->json([
            'stats' => [
                'totalSales' => $totalSales,
                'totalRevenue' => $totalRevenue,
                'averageTicket' => round($averageTicket, 2),
                'topProducts' => $topProducts,
            ]
        ]);
    }
}
