<?php

namespace App\Http\Controllers\Api\V1\Animal;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Animal\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AnimalCertificateController extends BaseController
{
    /**
     * Adjuntar certificado de esterilización a un animal
     * POST /api/v1/animals/{id}/certificado-esterilizacion
     */
    public function attachSterilizationCertificate(Request $request, $id)
    {
        try {
            // Buscar el animal
            $animal = Animal::find($id);
            
            if (!$animal) {
                return $this->errorResponse('Animal no encontrado', [], 404);
            }

            // Validar que el animal esté esterilizado
            if (!$animal->esterilizacion) {
                return $this->errorResponse(
                    'El animal no está registrado como esterilizado',
                    ['animal' => ['El animal debe estar marcado como esterilizado']],
                    422
                );
            }

            // Validar el archivo
            $validator = Validator::make($request->all(), [
                'certificado' => 'required|file|mimes:pdf,jpeg,jpg,png|max:5120', // 5MB
                'notas' => 'nullable|string|max:255'
            ], [
                'certificado.required' => 'El certificado es obligatorio',
                'certificado.mimes' => 'El archivo debe ser PDF, JPEG o PNG',
                'certificado.max' => 'El archivo no debe exceder 5 MB',
                'notas.max' => 'Las notas no deben exceder 255 caracteres'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    'Validación fallida',
                    $validator->errors()->toArray(),
                    422
                );
            }

            try {
                // Generar nombre único para el archivo
                $file = $request->file('certificado');
                $originalName = $file->getClientOriginalName();
                $fileName = "animal_{$id}_cert_" . now()->format('YmdHis') . '.' . $file->getClientOriginalExtension();
                
                // Guardar archivo en storage/public/certificados
                $path = $file->storeAs(
                    'documentos/certificados/esterilizacion',
                    $fileName,
                    's3'
                );

                // Actualizar el animal con la referencia al certificado
                $animal->update([
                    'certificado_esterilizacion' => $path,
                    'fecha_adjuncion_certificado' => now(),
                    'notas_certificado' => $request->input('notas') ?? null
                ]);

                // Log de la acción
                Log::info("Certificado adjuntado", [
                    'animal_id' => $id,
                    'path' => $path,
                    'usuario_id' => auth()->id()
                ]);

                return $this->successResponse(
                    [
                        'animal_id' => $animal->id,
                        'certificado_path' => $path,
                        'archivo_original' => $originalName,
                        'notas' => $request->input('notas') ?? null,
                        'fecha_adjuncion' => $animal->fecha_adjuncion_certificado->toIso8601String(),
                        'message' => 'Certificado adjuntado correctamente'
                    ],
                    'Certificado de esterilización adjuntado correctamente',
                    200
                );

            } catch (\Exception $storageError) {
                Log::error("Error al guardar certificado", [
                    'animal_id' => $id,
                    'error' => $storageError->getMessage()
                ]);

                return $this->errorResponse(
                    'Error al procesar el certificado',
                    ['certificado' => [$storageError->getMessage()]],
                    500
                );
            }

        } catch (\Exception $e) {
            Log::error("Error inesperado en adjuntar certificado", [
                'animal_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                'Error inesperado al procesar la solicitud',
                [],
                500
            );
        }
    }

    /**
     * Descargar certificado de un animal
     * GET /api/v1/animals/{id}/certificado-esterilizacion
     */
    public function downloadCertificate($id)
    {
        try {
            $animal = Animal::find($id);
            
            if (!$animal) {
                return $this->errorResponse('Animal no encontrado', [], 404);
            }

            if (!$animal->certificado_esterilizacion || 
                !Storage::disk('s3')->exists($animal->certificado_esterilizacion)) {
                return $this->errorResponse(
                    'Certificado no encontrado',
                    [],
                    404
                );
            }

            // Log de descarga
            Log::info("Certificado descargado", [
                'animal_id' => $id,
                'usuario_id' => auth()->id()
            ]);

            return Storage::disk('s3')->download($animal->certificado_esterilizacion);

        } catch (\Exception $e) {
            Log::error("Error al descargar certificado", [
                'animal_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                'Error al descargar el certificado',
                [],
                500
            );
        }
    }

    /**
     * Eliminar certificado de un animal
     * DELETE /api/v1/animals/{id}/certificado-esterilizacion
     */
    public function deleteCertificate($id)
    {
        try {
            $animal = Animal::find($id);
            
            if (!$animal) {
                return $this->errorResponse('Animal no encontrado', [], 404);
            }

            if ($animal->certificado_esterilizacion) {
                // Eliminar archivo
                if (Storage::disk('s3')->exists($animal->certificado_esterilizacion)) {
                    Storage::disk('s3')->delete($animal->certificado_esterilizacion);
                }
                
                // Actualizar animal
                $animal->update([
                    'certificado_esterilizacion' => null,
                    'fecha_adjuncion_certificado' => null,
                    'notas_certificado' => null
                ]);

                // Log
                Log::info("Certificado eliminado", [
                    'animal_id' => $id,
                    'usuario_id' => auth()->id()
                ]);
            }

            return $this->successResponse(
                ['animal_id' => $id],
                'Certificado eliminado correctamente',
                200
            );

        } catch (\Exception $e) {
            Log::error("Error al eliminar certificado", [
                'animal_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                'Error al eliminar el certificado',
                [],
                500
            );
        }
    }

    /**
     * Obtener información del certificado
     * GET /api/v1/animals/{id}/certificado-esterilizacion/info
     */
    public function certificateInfo($id)
    {
        try {
            $animal = Animal::find($id);
            
            if (!$animal) {
                return $this->errorResponse('Animal no encontrado', [], 404);
            }

            if (!$animal->certificado_esterilizacion) {
                return $this->successResponse(
                    ['adjuntado' => false],
                    'El animal no tiene certificado adjuntado',
                    200
                );
            }

            return $this->successResponse(
                [
                    'adjuntado' => true,
                    'animal_id' => $animal->id,
                    'certificado_path' => $animal->certificado_esterilizacion,
                    'fecha_adjuncion' => $animal->fecha_adjuncion_certificado?->toIso8601String(),
                    'notas' => $animal->notas_certificado
                ],
                'Información del certificado obtenida',
                200
            );

        } catch (\Exception $e) {
            Log::error("Error al obtener información del certificado", [
                'animal_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                'Error al obtener información del certificado',
                [],
                500
            );
        }
    }
}
