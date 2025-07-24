<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Lista todos los usuarios (solo admin)
     */
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        
        return response()->json([
            'users' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ]);
    }

    /**
     * Crear un nuevo usuario (solo admin)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Asignar roles si se proporcionan
        if ($request->has('roles')) {
            foreach ($request->roles as $roleName) {
                $user->assignRole($roleName, Auth::id());
            }
        }

        $user->load('roles');

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'user' => $user
        ], 201);
    }

    /**
     * Mostrar un usuario específico
     */
    public function show(User $user)
    {
        $user->load('roles');
        return response()->json($user);
    }

    /**
     * Actualizar un usuario (solo admin)
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        $user->load('roles');

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'user' => $user
        ]);
    }

    /**
     * Eliminar un usuario (solo admin)
     */
    public function destroy(User $user)
    {
        // Prevenir que el admin se elimine a sí mismo
        if ($user->id === Auth::id()) {
            return response()->json([
                'message' => 'No puedes eliminar tu propia cuenta'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }

    /**
     * Asignar rol a un usuario (solo admin)
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        if ($user->hasRole($request->role)) {
            return response()->json([
                'message' => 'El usuario ya tiene este rol'
            ], 400);
        }

        $user->assignRole($request->role, Auth::id());
        $user->load('roles');

        return response()->json([
            'message' => 'Rol asignado exitosamente',
            'user' => $user
        ]);
    }

    /**
     * Remover rol de un usuario (solo admin)
     */
    public function removeRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        if (!$user->hasRole($request->role)) {
            return response()->json([
                'message' => 'El usuario no tiene este rol'
            ], 400);
        }

        // Prevenir que se remueva el rol de admin del último administrador
        if ($request->role === 'admin') {
            $adminCount = User::whereHas('roles', function($query) {
                $query->where('name', 'admin');
            })->count();

            if ($adminCount <= 1) {
                return response()->json([
                    'message' => 'No se puede remover el rol de admin del último administrador'
                ], 400);
            }
        }

        $user->removeRole($request->role);
        $user->load('roles');

        return response()->json([
            'message' => 'Rol removido exitosamente',
            'user' => $user
        ]);
    }
}
