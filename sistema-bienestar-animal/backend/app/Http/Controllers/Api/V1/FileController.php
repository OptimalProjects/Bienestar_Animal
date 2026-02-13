<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileController extends BaseController
{
    /**
     * Servir archivo privado de S3 a través del backend (proxy).
     *
     * GET /api/v1/files/private?path={ruta_relativa_en_s3}
     */
    public function servePrivate(Request $request)
    {
        $path = $request->query('path');

        if (!$path) {
            return $this->errorResponse('El parámetro path es requerido', null, 400);
        }

        // Prevenir path traversal
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            return $this->forbiddenResponse('Ruta no válida');
        }

        // Validar que la ruta no intente acceder fuera de las carpetas permitidas
        $allowedPrefixes = [
            'documentos/',
        ];

        $isAllowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            return $this->forbiddenResponse('Acceso no permitido a esta ruta');
        }

        try {
            if (!Storage::disk('s3')->exists($path)) {
                return $this->notFoundResponse('Archivo no encontrado');
            }

            $content = Storage::disk('s3')->get($path);
            $mimeType = Storage::disk('s3')->mimeType($path);

            return response($content)
                ->header('Content-Type', $mimeType ?: 'application/octet-stream')
                ->header('Cache-Control', 'private, max-age=1800');

        } catch (\Exception $e) {
            Log::error('Error al servir archivo privado', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Error al obtener el archivo');
        }
    }
}
