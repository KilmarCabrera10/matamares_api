<?php

namespace App\Projects\Matamares\Controllers;

use App\Core\Controllers\Controller;
use App\Projects\Matamares\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Category filter
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Only active products by default
        $query->active();

        // Pagination
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 50);

        $products = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'products' => $products->items(),
            'pagination' => [
                'page' => $products->currentPage(),
                'limit' => $products->perPage(),
                'total' => $products->total(),
                'pages' => $products->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:products,code',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|unique:products,barcode',
        ]);

        $product = Product::create($request->all());

        return response()->json([
            'product' => $product,
            'message' => 'Producto creado exitosamente'
        ], 201);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        return response()->json([
            'product' => $product
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|unique:products,code,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'cost' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'category' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
        ]);

        $product->update($request->all());

        return response()->json([
            'product' => $product,
            'message' => 'Producto actualizado exitosamente'
        ]);
    }

    /**
     * Remove (deactivate) the specified product.
     */
    public function destroy(Product $product)
    {
        $product->update(['active' => false]);

        return response()->json([
            'message' => 'Producto eliminado exitosamente'
        ]);
    }
}