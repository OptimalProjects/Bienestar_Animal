<?php

namespace App\Http\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Respuesta exitosa.
     */
    public static function success($data = null, string $message = 'Operación exitosa', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ], $code);
    }

    /**
     * Respuesta de error.
     */
    public static function error(string $message = 'Error en la operación', $errors = null, int $code = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Respuesta de recurso creado.
     */
    public static function created($data, string $message = 'Recurso creado exitosamente'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * Respuesta de recurso no encontrado.
     */
    public static function notFound(string $message = 'Recurso no encontrado'): JsonResponse
    {
        return self::error($message, null, 404);
    }

    /**
     * Respuesta de no autorizado.
     */
    public static function unauthorized(string $message = 'No autorizado'): JsonResponse
    {
        return self::error($message, null, 401);
    }

    /**
     * Respuesta de prohibido.
     */
    public static function forbidden(string $message = 'Acceso denegado'): JsonResponse
    {
        return self::error($message, null, 403);
    }

    /**
     * Respuesta de validación fallida.
     */
    public static function validationError($errors, string $message = 'Error de validación'): JsonResponse
    {
        return self::error($message, $errors, 422);
    }

    /**
     * Respuesta de error del servidor.
     */
    public static function serverError(string $message = 'Error interno del servidor'): JsonResponse
    {
        return self::error($message, null, 500);
    }
}