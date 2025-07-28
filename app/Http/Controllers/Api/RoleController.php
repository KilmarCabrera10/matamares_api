<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Lista todos los roles
     */
    public function index()
    {
        // Return predefined roles for the POS system
        $roles = [
            [
                'id' => 'administrador',
                'name' => 'Administrador',
                'description' => 'Acceso completo al sistema'
            ],
            [
                'id' => 'gerente',
                'name' => 'Gerente',
                'description' => 'Gestión de productos, ventas y reportes'
            ],
            [
                'id' => 'cajero',
                'name' => 'Cajero',
                'description' => 'Procesamiento de ventas y consulta de productos'
            ]
        ];
        
        return response()->json([
            'roles' => $roles
        ]);
    }

    /**
     * Crear un nuevo rol (solo admin)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'role' => $role
        ], 201);
    }

    /**
     * Mostrar un rol específico
     */
    public function show(Role $role)
    {
        $role->load('users');
        return response()->json($role);
    }

    /**
     * Actualizar un rol (solo admin)
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $role->update($request->only(['name', 'display_name', 'description', 'is_active']));

        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'role' => $role
        ]);
    }

    /**
     * Eliminar un rol (solo admin)
     */
    public function destroy(Role $role)
    {
        // Prevenir eliminación de roles críticos
        if (in_array($role->name, ['admin', 'user'])) {
            return response()->json([
                'message' => 'No se pueden eliminar roles del sistema'
            ], 400);
        }

        // Verificar si hay usuarios con este rol
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar un rol que tiene usuarios asignados'
            ], 400);
        }

        $role->delete();

        return response()->json([
            'message' => 'Rol eliminado exitosamente'
        ]);
    }
}
