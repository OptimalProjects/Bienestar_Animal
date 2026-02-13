<?php

namespace App\Http\Controllers\Api\V1\Veterinary;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Animal\Animal;
use App\Models\Veterinaria\Cirugia;
use App\Models\Veterinaria\Vacuna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\FileService;

class CertificadoVeterinarioController extends BaseController
{
    /**
     * Adjuntar certificado (unificado para esterilización, cirugía, vacunación).
     * POST /api/v1/certificados-veterinarios
     * 
     * NOTA: Para cirugías y vacunas, ahora soporta MÚLTIPLES certificados
     */
    public function adjuntar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|in:esterilizacion,cirugia,vacunacion',
            'animal_id' => 'required|exists:animals,id',
            'registro_id' => 'nullable|uuid', // ID de la cirugía o vacuna específica
            'certificado' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'notas' => 'nullable|string|max:500',
        ], [
            'tipo.required' => 'El tipo de certificado es requerido',
            'tipo.in' => 'El tipo debe ser: esterilizacion, cirugia o vacunacion',
            'animal_id.required' => 'El ID del animal es requerido',
            'animal_id.exists' => 'El animal no existe',
            'certificado.required' => 'El certificado es requerido',
            'certificado.file' => 'El certificado debe ser un archivo',
            'certificado.mimes' => 'El certificado debe ser PDF, JPG, JPEG o PNG',
            'certificado.max' => 'El certificado no puede superar 5MB',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();

        try {
            $tipo = $request->tipo;
            $animalId = $request->animal_id;
            $registroId = $request->registro_id;
            $notas = $request->notas;

            $animal = Animal::findOrFail($animalId);
            
            // Subir archivo
            $file = $request->file('certificado');
            $tipoFolder = $tipo === 'esterilizacion' ? 'esterilizacion' : ($tipo === 'cirugia' ? 'cirugias' : 'vacunas');
            $filename = "certificado_{$tipoFolder}_{$animal->codigo_unico}_" . time() . '.' . $file->getClientOriginalExtension();

            // Guardar en S3
            $path = $file->storeAs("documentos/certificados/{$tipoFolder}", $filename, 's3');

            $result = null;

            // Procesar según el tipo
            switch ($tipo) {
                case 'esterilizacion':
                    $result = $this->adjuntarCertificadoEsterilizacion($animal, $path, $notas);
                    break;

                case 'cirugia':
                    if (!$registroId) {
                        DB::rollBack();
                        return $this->validationErrorResponse(['registro_id' => 'El ID de la cirugía es requerido']);
                    }
                    $result = $this->adjuntarCertificadoCirugia($registroId, $path, $notas);
                    break;

                case 'vacunacion':
                    if (!$registroId) {
                        DB::rollBack();
                        return $this->validationErrorResponse(['registro_id' => 'El ID de la vacuna es requerido']);
                    }
                    $result = $this->adjuntarCertificadoVacuna($registroId, $path, $notas);
                    break;
            }

            DB::commit();

            Log::info("✅ Certificado de {$tipo} adjuntado", [
                'animal_id' => $animal->id,
                'codigo_unico' => $animal->codigo_unico,
                'tipo' => $tipo,
                'certificado_path' => $path,
            ]);

            return $this->successResponse([
                'tipo' => $tipo,
                'animal' => $animal->fresh(),
                'registro' => $result['registro'] ?? null,
                'certificado_url' => FileService::privateUrl($path),
                'total_certificados' => $result['total_certificados'] ?? 1,
            ], "Certificado de {$tipo} adjuntado exitosamente");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->notFoundResponse('Registro no encontrado');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Error al adjuntar certificado: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Error al adjuntar certificado: ' . $e->getMessage());
        }
    }

    /**
     * Adjuntar certificado de esterilización al animal.
     * (Solo 1 certificado - sobrescribe el anterior)
     */
    private function adjuntarCertificadoEsterilizacion(Animal $animal, string $path, ?string $notas)
    {
        // Eliminar certificado anterior si existe
        if ($animal->certificado_esterilizacion) {
            Storage::disk('s3')->delete($animal->certificado_esterilizacion);
        }

        $animal->certificado_esterilizacion = $path;
        $animal->fecha_adjuncion_certificado = now();
        $animal->notas_certificado = $notas;
        $animal->esterilizacion = true;

        // Si no tiene fecha de esterilización, usar la fecha actual
        if (!$animal->fecha_esterilizacion) {
            $animal->fecha_esterilizacion = now();
        }

        $animal->save();

        return ['registro' => $animal, 'total_certificados' => 1];
    }

    /**
     * Adjuntar certificado a una cirugía específica.
     * SOPORTA MÚLTIPLES CERTIFICADOS
     */
    private function adjuntarCertificadoCirugia(string $cirugiaId, string $path, ?string $notas)
    {
        $cirugia = Cirugia::findOrFail($cirugiaId);

        // Obtener certificados existentes
        $certificados = $cirugia->certificados ? json_decode($cirugia->certificados, true) : [];
        
        // Si no es array válido, inicializar
        if (!is_array($certificados)) {
            $certificados = [];
        }

        // Agregar nuevo certificado al array
        $nuevoCertificado = [
            'path' => $path,
            'fecha_adjuncion' => now()->toIso8601String(),
            'notas' => $notas,
            'nombre_archivo' => basename($path)
        ];

        $certificados[] = $nuevoCertificado;

        // Guardar array actualizado
        $cirugia->certificados = json_encode($certificados);
        $cirugia->save();

        // Si es esterilización o castración, actualizar también el animal
        if (in_array($cirugia->tipo_cirugia, ['esterilizacion', 'castracion'])) {
            $animal = $cirugia->historialClinico->animal;
            $animal->esterilizacion = true;
            $animal->fecha_esterilizacion = $cirugia->fecha_realizacion ?? $cirugia->fecha_programada;
            $animal->veterinario_esterilizacion = $cirugia->cirujano->usuario->nombre_completo ?? null;
            $animal->save();
        }

        $cirugia->load(['cirujano.usuario', 'anestesiologo.usuario', 'historialClinico.animal']);

        return [
            'registro' => $cirugia,
            'total_certificados' => count($certificados)
        ];
    }

    /**
     * Adjuntar certificado a una vacuna específica.
     * SOPORTA MÚLTIPLES CERTIFICADOS
     */
    private function adjuntarCertificadoVacuna(string $vacunaId, string $path, ?string $notas)
    {
        $vacuna = Vacuna::findOrFail($vacunaId);

        // Obtener certificados existentes
        $certificados = $vacuna->certificados ? json_decode($vacuna->certificados, true) : [];
        
        // Si no es array válido, inicializar
        if (!is_array($certificados)) {
            $certificados = [];
        }

        // Agregar nuevo certificado al array
        $nuevoCertificado = [
            'path' => $path,
            'fecha_adjuncion' => now()->toIso8601String(),
            'notas' => $notas,
            'nombre_archivo' => basename($path)
        ];

        $certificados[] = $nuevoCertificado;

        // Guardar array actualizado
        $vacuna->certificados = json_encode($certificados);
        $vacuna->save();

        $vacuna->load(['veterinario.usuario', 'historialClinico.animal']);

        return [
            'registro' => $vacuna,
            'total_certificados' => count($certificados)
        ];
    }

    /**
     * Obtener información de certificados de un animal.
     * GET /api/v1/certificados-veterinarios/{animalId}
     */
    public function obtenerPorAnimal(string $animalId)
    {
        try {
            $animal = Animal::with([
                'historialClinico.cirugias' => function ($query) {
                    $query->whereNotNull('certificados')
                          ->orderBy('fecha_realizacion', 'desc');
                },
                'historialClinico.vacunas' => function ($query) {
                    $query->whereNotNull('certificados')
                          ->orderBy('fecha_aplicacion', 'desc');
                }
            ])->findOrFail($animalId);

            $certificados = [
                'animal' => [
                    'id' => $animal->id,
                    'codigo_unico' => $animal->codigo_unico,
                    'nombre' => $animal->nombre,
                ],
                'esterilizacion' => null,
                'cirugias' => [],
                'vacunas' => [],
            ];

            // Certificado de esterilización del animal
            if ($animal->certificado_esterilizacion) {
                $certificados['esterilizacion'] = [
                    'url' => FileService::privateUrl($animal->certificado_esterilizacion),
                    'fecha_adjuncion' => $animal->fecha_adjuncion_certificado,
                    'fecha_esterilizacion' => $animal->fecha_esterilizacion,
                    'notas' => $animal->notas_certificado,
                ];
            }

            // Certificados de cirugías (MÚLTIPLES)
            if ($animal->historialClinico && $animal->historialClinico->cirugias) {
                foreach ($animal->historialClinico->cirugias as $cirugia) {
                    $certifs = json_decode($cirugia->certificados, true) ?: [];
                    
                    $certificados['cirugias'][] = [
                        'id' => $cirugia->id,
                        'tipo' => $cirugia->tipo_cirugia,
                        'descripcion' => $cirugia->descripcion,
                        'fecha' => $cirugia->fecha_realizacion ?? $cirugia->fecha_programada,
                        'certificados' => array_map(function($cert) {
                            return [
                                'url' => FileService::privateUrl($cert['path']),
                                'fecha_adjuncion' => $cert['fecha_adjuncion'],
                                'notas' => $cert['notas'] ?? null,
                                'nombre_archivo' => $cert['nombre_archivo'] ?? basename($cert['path']),
                            ];
                        }, $certifs),
                        'total_certificados' => count($certifs),
                    ];
                }
            }

            // Certificados de vacunas (MÚLTIPLES)
            if ($animal->historialClinico && $animal->historialClinico->vacunas) {
                foreach ($animal->historialClinico->vacunas as $vacuna) {
                    $certifs = json_decode($vacuna->certificados, true) ?: [];
                    
                    $certificados['vacunas'][] = [
                        'id' => $vacuna->id,
                        'tipo_vacuna' => $vacuna->tipo_vacuna,
                        'lote' => $vacuna->lote,
                        'fecha_aplicacion' => $vacuna->fecha_aplicacion,
                        'certificados' => array_map(function($cert) {
                            return [
                                'url' => FileService::privateUrl($cert['path']),
                                'fecha_adjuncion' => $cert['fecha_adjuncion'],
                                'notas' => $cert['notas'] ?? null,
                                'nombre_archivo' => $cert['nombre_archivo'] ?? basename($cert['path']),
                            ];
                        }, $certifs),
                        'total_certificados' => count($certifs),
                    ];
                }
            }

            return $this->successResponse($certificados, 'Certificados obtenidos exitosamente');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Animal no encontrado');
        } catch (\Exception $e) {
            Log::error('Error al obtener certificados: ' . $e->getMessage());
            return $this->serverErrorResponse('Error al obtener certificados');
        }
    }

    /**
     * Eliminar un certificado específico.
     * DELETE /api/v1/certificados-veterinarios/eliminar
     */
    public function eliminarCertificado(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|in:cirugia,vacunacion',
            'registro_id' => 'required|uuid',
            'certificado_index' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();

        try {
            $tipo = $request->tipo;
            $registroId = $request->registro_id;
            $index = $request->certificado_index;

            if ($tipo === 'cirugia') {
                $registro = Cirugia::findOrFail($registroId);
            } else {
                $registro = Vacuna::findOrFail($registroId);
            }

            $certificados = json_decode($registro->certificados, true) ?: [];

            if (!isset($certificados[$index])) {
                DB::rollBack();
                return $this->notFoundResponse('Certificado no encontrado');
            }

            // Eliminar archivo del storage S3
            $path = $certificados[$index]['path'];
            if (Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
            }

            // Eliminar del array
            array_splice($certificados, $index, 1);

            // Actualizar en BD
            $registro->certificados = count($certificados) > 0 ? json_encode($certificados) : null;
            $registro->save();

            DB::commit();

            return $this->successResponse([
                'total_certificados' => count($certificados),
            ], 'Certificado eliminado exitosamente');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->notFoundResponse('Registro no encontrado');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar certificado: ' . $e->getMessage());
            return $this->serverErrorResponse('Error al eliminar certificado');
        }
    }

    /**
     * Descargar un certificado específico.
     * GET /api/v1/certificados-veterinarios/descargar
     */
    public function descargar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|in:esterilizacion,cirugia,vacunacion',
            'animal_id' => 'required|exists:animals,id',
            'registro_id' => 'nullable|uuid',
            'certificado_index' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $tipo = $request->tipo;
            $path = null;

            switch ($tipo) {
                case 'esterilizacion':
                    $animal = Animal::findOrFail($request->animal_id);
                    $path = $animal->certificado_esterilizacion;
                    break;

                case 'cirugia':
                    $cirugia = Cirugia::findOrFail($request->registro_id);
                    $certificados = json_decode($cirugia->certificados, true) ?: [];
                    $index = $request->certificado_index ?? 0;
                    $path = $certificados[$index]['path'] ?? null;
                    break;

                case 'vacunacion':
                    $vacuna = Vacuna::findOrFail($request->registro_id);
                    $certificados = json_decode($vacuna->certificados, true) ?: [];
                    $index = $request->certificado_index ?? 0;
                    $path = $certificados[$index]['path'] ?? null;
                    break;
            }

            if (!$path) {
                return $this->notFoundResponse('No hay certificado adjunto para este registro');
            }

            if (!Storage::disk('s3')->exists($path)) {
                return $this->notFoundResponse('El archivo del certificado no existe');
            }

            return Storage::disk('s3')->download($path);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Registro no encontrado');
        } catch (\Exception $e) {
            Log::error('Error al descargar certificado: ' . $e->getMessage());
            return $this->serverErrorResponse('Error al descargar certificado');
        }
    }
}