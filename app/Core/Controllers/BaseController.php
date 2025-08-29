<?php

namespace App\Core\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

abstract class BaseController extends Controller
{
    /**
     * Respuesta exitosa estándar
     */
    protected function successResponse($data = null, string $message = 'Operación exitosa', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Respuesta de error estándar
     */
    protected function errorResponse(string $message = 'Error en la operación', int $status = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Respuesta para recursos no encontrados
     */
    protected function notFoundResponse(string $message = 'Recurso no encontrado'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Respuesta para errores de validación
     */
    protected function validationErrorResponse($errors, string $message = 'Datos de entrada inválidos'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }
}
