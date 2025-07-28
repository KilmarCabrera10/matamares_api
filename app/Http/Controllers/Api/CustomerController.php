<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Only active customers by default
        $query->active();

        $customers = $query->get();

        return response()->json([
            'customers' => $customers
        ]);
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'document' => 'nullable|string|max:20',
        ]);

        $customer = Customer::create($request->all());

        return response()->json([
            'customer' => $customer,
            'message' => 'Cliente creado exitosamente'
        ], 201);
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        return response()->json([
            'customer' => $customer
        ]);
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'document' => 'nullable|string|max:20',
        ]);

        $customer->update($request->all());

        return response()->json([
            'customer' => $customer,
            'message' => 'Cliente actualizado exitosamente'
        ]);
    }

    /**
     * Remove (deactivate) the specified customer.
     */
    public function destroy(Customer $customer)
    {
        $customer->update(['active' => false]);

        return response()->json([
            'message' => 'Cliente eliminado exitosamente'
        ]);
    }
}
