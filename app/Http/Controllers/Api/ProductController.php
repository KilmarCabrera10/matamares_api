<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        // Datos de ejemplo
        $products = [
            ['id' => 1, 'name' => 'Producto A', 'price' => 10.99],
            ['id' => 2, 'name' => 'Producto B', 'price' => 15.50],
            ['id' => 3, 'name' => 'Producto C', 'price' => 7.25],
        ];

        return response()->json($products);
    }
}