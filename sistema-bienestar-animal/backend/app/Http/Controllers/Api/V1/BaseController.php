<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;

class BaseController extends Controller
{
    /**
     * Respuesta exitosa.
     */
    protected function successResponse($data = null, string $message = 'Operación exitosa', int $code = 200)
    {
        return ApiResponse::success($data, $message, $code);
    }

    /**
     * Respuesta de error.
     */
    protected function errorResponse(string $message = 'Error en la operación', $errors = null, int $code = 400)
    {
        return ApiResponse::error($message, $errors, $code);
    }

    /**
     * Respuesta de recurso creado.
     */
    protected function createdResponse($data, string $message = 'Recurso creado exitosamente')
    {
        return ApiResponse::created($data, $message);
    }

    /**
     * Respuesta de recurso no encontrado.
     */
    protected function notFoundResponse(string $message = 'Recurso no encontrado')
    {
        return ApiResponse::notFound($message);
    }

    /**
     * Respuesta de no autorizado.
     */
    protected function unauthorizedResponse(string $message = 'No autorizado')
    {
        return ApiResponse::unauthorized($message);
    }

    /**
     * Respuesta de prohibido.
     */
    protected function forbiddenResponse(string $message = 'Acceso denegado')
    {
        return ApiResponse::forbidden($message);
    }

    /**
     * Respuesta de validación fallida.
     */
    protected function validationErrorResponse($errors, string $message = 'Error de validación')
    {
        return ApiResponse::validationError($errors, $message);
    }

    /**
     * Respuesta de error del servidor.
     */
    protected function serverErrorResponse(string $message = 'Error interno del servidor')
    {
        return ApiResponse::serverError($message);
    }
}