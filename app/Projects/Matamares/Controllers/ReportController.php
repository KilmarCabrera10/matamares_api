<?php

namespace App\Projects\Matamares\Controllers;

use App\Core\Controllers\Controller;
use App\Projects\Matamares\Models\Sale;
use App\Projects\Matamares\Models\Product;
use App\Projects\Matamares\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Sales report by period.
     */
    public function sales(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'groupBy' => 'in:day,week,month',
        ]);

        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $groupBy = $request->get('groupBy', 'day');

        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])->get();

        $totalSales = $sales->count();
        $totalRevenue = $sales->sum('total');
        $averageTicket = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        // Group data by period
        $groupedData = [];
        
        if ($groupBy === 'day') {
            $groupedSales = $sales->groupBy(function($sale) {
                return $sale->created_at->format('Y-m-d');
            });
        } elseif ($groupBy === 'week') {
            $groupedSales = $sales->groupBy(function($sale) {
                return $sale->created_at->format('Y-W');
            });
        } else { // month
            $groupedSales = $sales->groupBy(function($sale) {
                return $sale->created_at->format('Y-m');
            });
        }

        foreach ($groupedSales as $period => $periodSales) {
            $groupedData[] = [
                'date' => $period,
                'sales' => $periodSales->count(),
                'revenue' => $periodSales->sum('total'),
            ];
        }

        return response()->json([
            'report' => [
                'period' => [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ],
                'summary' => [
                    'totalSales' => $totalSales,
                    'totalRevenue' => $totalRevenue,
                    'averageTicket' => round($averageTicket, 2),
                ],
                'data' => $groupedData,
            ]
        ]);
    }

    /**
     * Top selling products report.
     */
    public function products(Request $request)
    {
        $request->validate([
            'startDate' => 'sometimes|date',
            'endDate' => 'sometimes|date|after_or_equal:startDate',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = DB::table('sale_items')
                   ->join('products', 'sale_items.product_id', '=', 'products.id')
                   ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                   ->select(
                       'products.id',
                       'products.name',
                       'products.code',
                       DB::raw('SUM(sale_items.quantity) as total_quantity'),
                       DB::raw('SUM(sale_items.total_price) as total_revenue'),
                       DB::raw('COUNT(DISTINCT sales.id) as sales_count')
                   )
                   ->groupBy('products.id', 'products.name', 'products.code');

        if ($request->has('startDate') && $request->has('endDate')) {
            $query->whereBetween('sales.created_at', [$request->startDate, $request->endDate]);
        }

        $limit = $request->get('limit', 20);
        $products = $query->orderBy('total_quantity', 'desc')
                         ->limit($limit)
                         ->get();

        return response()->json([
            'report' => [
                'period' => [
                    'startDate' => $request->get('startDate'),
                    'endDate' => $request->get('endDate'),
                ],
                'products' => $products->map(function ($product) {
                    return [
                        'productId' => $product->id,
                        'productName' => $product->name,
                        'productCode' => $product->code,
                        'totalQuantitySold' => $product->total_quantity,
                        'totalRevenue' => $product->total_revenue,
                        'salesCount' => $product->sales_count,
                    ];
                }),
            ]
        ]);
    }

    /**
     * User performance report.
     */
    public function users(Request $request)
    {
        $request->validate([
            'startDate' => 'sometimes|date',
            'endDate' => 'sometimes|date|after_or_equal:startDate',
        ]);

        $query = User::with(['sales' => function ($query) use ($request) {
            if ($request->has('startDate') && $request->has('endDate')) {
                $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
            }
        }]);

        $users = $query->get();

        $userStats = $users->map(function ($user) {
            $sales = $user->sales;
            $totalSales = $sales->count();
            $totalRevenue = $sales->sum('total');
            $averageTicket = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

            return [
                'userId' => $user->id,
                'userName' => $user->name,
                'totalSales' => $totalSales,
                'totalRevenue' => $totalRevenue,
                'averageTicket' => round($averageTicket, 2),
            ];
        })->sortByDesc('totalRevenue')->values();

        return response()->json([
            'report' => [
                'period' => [
                    'startDate' => $request->get('startDate'),
                    'endDate' => $request->get('endDate'),
                ],
                'users' => $userStats,
            ]
        ]);
    }
}
