<?php

namespace App\Projects\Matamares\Controllers;

use App\Core\Controllers\Controller;
use App\Projects\Matamares\Models\User;
use App\Projects\Matamares\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users (admin only).
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Role filter
        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Status filter
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Pagination
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);

        $users = $query->orderBy('name')
                      ->paginate($limit, ['*'], 'page', $page);

        $formattedUsers = $users->getCollection()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'active' => $user->active,
                'role' => $user->roles->first()?->name ?? 'sin_rol',
                'createdAt' => $user->created_at->toISOString(),
                'updatedAt' => $user->updated_at->toISOString(),
            ];
        });

        return response()->json([
            'users' => $formattedUsers,
            'pagination' => [
                'page' => $users->currentPage(),
                'limit' => $users->perPage(),
                'total' => $users->total(),
                'pages' => $users->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'active' => true,
        ]);

        // Assign role
        $role = Role::where('name', $request->role)->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        $user->load('roles');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'active' => $user->active,
                'role' => $user->roles->first()?->name ?? 'sin_rol',
                'createdAt' => $user->created_at->toISOString(),
                'updatedAt' => $user->updated_at->toISOString(),
            ],
            'message' => 'Usuario creado exitosamente'
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('roles');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'active' => $user->active,
                'role' => $user->roles->first()?->name ?? 'sin_rol',
                'createdAt' => $user->created_at->toISOString(),
                'updatedAt' => $user->updated_at->toISOString(),
            ]
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:6',
            'role' => 'sometimes|required|string|exists:roles,name',
        ]);

        $updateData = $request->only(['name', 'email']);
        
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Update role if provided
        if ($request->has('role')) {
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        }

        $user->load('roles');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'active' => $user->active,
                'role' => $user->roles->first()?->name ?? 'sin_rol',
                'createdAt' => $user->created_at->toISOString(),
                'updatedAt' => $user->updated_at->toISOString(),
            ],
            'message' => 'Usuario actualizado exitosamente'
        ]);
    }

    /**
     * Remove (deactivate) the specified user.
     */
    public function destroy(User $user)
    {
        // Don't allow deleting the current user
        if ($user->id === auth('sanctum')->id()) {
            return response()->json([
                'message' => 'No puedes eliminar tu propio usuario'
            ], 422);
        }

        $user->update(['active' => false]);

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(User $user)
    {
        // Don't allow deactivating the current user
        if ($user->id === auth('sanctum')->id() && $user->active) {
            return response()->json([
                'message' => 'No puedes desactivar tu propio usuario'
            ], 422);
        }

        $user->update(['active' => !$user->active]);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'active' => $user->active,
                'role' => $user->roles->first()?->name ?? 'sin_rol',
                'createdAt' => $user->created_at->toISOString(),
                'updatedAt' => $user->updated_at->toISOString(),
            ],
            'message' => $user->active ? 'Usuario activado exitosamente' : 'Usuario desactivado exitosamente'
        ]);
    }
}
