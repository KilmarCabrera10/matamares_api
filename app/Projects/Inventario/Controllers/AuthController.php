<?php

namespace App\Projects\Inventario\Controllers;

use App\Core\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Projects\Inventario\Models\User;
use App\Projects\Inventario\Models\Organization;
use App\Projects\Inventario\Models\OrganizationMember;

class AuthController extends BaseController
{
    /**
     * Registrar un nuevo usuario
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:inventario.users,email',
            'password' => 'required|min:6|confirmed',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'organization_id' => 'nullable|exists:inventario.organizations,id'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Errores de validación', 422, $validator->errors());
        }

        try {
            $user = User::create([
                'id' => Str::uuid(),
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email_verified' => false,
                'status' => 'active'
            ]);

            // Si se proporciona una organización, agregar el usuario
            if ($request->organization_id) {
                OrganizationMember::create([
                    'id' => Str::uuid(),
                    'organization_id' => $request->organization_id,
                    'user_id' => $user->id,
                    'role' => 'employee', // Rol por defecto
                    'status' => 'active'
                ]);
            }

            // Crear token de autenticación
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'status' => $user->status
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'Usuario registrado exitosamente', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear el usuario: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Iniciar sesión
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Errores de validación', 422, $validator->errors());
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password_hash)) {
                return $this->errorResponse('Credenciales incorrectas', 401);
            }

            if ($user->status !== 'active') {
                return $this->errorResponse('Usuario inactivo', 403);
            }

            // Actualizar último login
            $user->update(['last_login_at' => now()]);

            // Crear token de autenticación
            $token = $user->createToken('auth_token')->plainTextToken;

            // Obtener organizaciones del usuario
            $organizations = $user->organizations()->with('organization')->get()->map(function ($membership) {
                return [
                    'id' => $membership->organization->id,
                    'name' => $membership->organization->name,
                    'role' => $membership->role,
                    'status' => $membership->status
                ];
            });

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'status' => $user->status,
                    'last_login_at' => $user->last_login_at
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'organizations' => $organizations
            ], 'Login exitoso');

        } catch (\Exception $e) {
            return $this->errorResponse('Error en el login: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->successResponse(null, 'Logout exitoso');
        } catch (\Exception $e) {
            return $this->errorResponse('Error en el logout: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener información del usuario autenticado
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();
            
            // Obtener organizaciones del usuario
            $organizations = $user->organizations()->with('organization')->get()->map(function ($membership) {
                return [
                    'id' => $membership->organization->id,
                    'name' => $membership->organization->name,
                    'role' => $membership->role,
                    'status' => $membership->status
                ];
            });

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'status' => $user->status,
                    'email_verified' => $user->email_verified,
                    'last_login_at' => $user->last_login_at
                ],
                'organizations' => $organizations
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener información del usuario: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Errores de validación', 422, $validator->errors());
        }

        try {
            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password_hash)) {
                return $this->errorResponse('La contraseña actual es incorrecta', 400);
            }

            $user->update([
                'password_hash' => Hash::make($request->new_password)
            ]);

            return $this->successResponse(null, 'Contraseña cambiada exitosamente');

        } catch (\Exception $e) {
            return $this->errorResponse('Error al cambiar la contraseña: ' . $e->getMessage(), 500);
        }
    }
}
