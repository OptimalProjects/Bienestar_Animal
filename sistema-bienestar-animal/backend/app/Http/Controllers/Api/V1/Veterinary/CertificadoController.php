<?php

namespace App\Http\Controllers\Api\V1\Veterinary;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Animal\Animal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CertificadoController extends BaseController
{
    /**
     * Generar certificado de vacunación
     * GET /api/v1/certificados/vacunacion/{animalId}
     */
    public function vacunacion(string $animalId)
    {
        try {
            Log::info("📄 Generando certificado de vacunación para animal: {$animalId}");

            $animal = Animal::with([
                'historialClinico.vacunas.tipoVacuna',
                'historialClinico.vacunas.veterinario'
            ])->findOrFail($animalId);

            if (!$animal->historialClinico) {
                Log::warning("⚠️ Animal {$animalId} no tiene historial clínico");
                return $this->errorResponse(
                    'El animal no tiene historial clínico registrado',
                    null,
                    404
                );
            }

            if ($animal->historialClinico->vacunas->isEmpty()) {
                Log::warning("⚠️ Animal {$animalId} no tiene vacunas registradas");
                return $this->errorResponse(
                    'El animal no tiene vacunas registradas',
                    null,
                    404
                );
            }

            $data = [
                'animal' => $animal,
                'vacunas' => $animal->historialClinico->vacunas,
                'fecha_emision' => now()->format('d/m/Y'),
                'tipo' => 'vacunacion'
            ];

            Log::info("✅ Datos preparados para certificado de vacunación");

            $pdf = Pdf::loadView('pdf.certificado-vacunacion', $data)
                ->setPaper('letter', 'portrait');

            $filename = "certificado_vacunacion_{$animal->codigo_unico}.pdf";

            Log::info("✅ Certificado de vacunación generado: {$filename}");

            return $pdf->download($filename);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("❌ Animal no encontrado: {$animalId}");
            return $this->notFoundResponse('Animal no encontrado');
        } catch (\Exception $e) {
            Log::error("❌ Error generando certificado de vacunación: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return $this->serverErrorResponse('Error al generar el certificado: ' . $e->getMessage());
        }
    }

    /**
     * Generar certificado de esterilización
     * GET /api/v1/certificados/esterilizacion/{animalId}
     */
    public function esterilizacion(string $animalId)
    {
        try {
            Log::info("📄 Generando certificado de esterilización para animal: {$animalId}");

            $animal = Animal::with([
                'historialClinico.cirugias' => function($query) {
                    $query->where(function($q) {
                        $q->where('tipo_cirugia', 'esterilizacion')
                          ->orWhere('tipo_cirugia', 'castracion');
                    })
                    ->where('estado', 'realizada')
                    ->where('resultado', 'exitosa');
                },
                'historialClinico.cirugias.cirujano.usuario'
            ])->findOrFail($animalId);

            if (!$animal->esterilizacion) {
                Log::warning("⚠️ Animal {$animalId} no está registrado como esterilizado");
                return $this->errorResponse(
                    'El animal no está registrado como esterilizado',
                    null,
                    400
                );
            }

            $cirugia = $animal->historialClinico?->cirugias->first();

            if (!$cirugia) {
                Log::warning("⚠️ No se encontró registro de cirugía de esterilización");
            }

            $data = [
                'animal' => $animal,
                'cirugia' => $cirugia,
                'fecha_emision' => now()->format('d/m/Y'),
                'tipo' => 'esterilizacion'
            ];

            Log::info("✅ Datos preparados para certificado de esterilización");

            $pdf = Pdf::loadView('pdf.certificado-esterilizacion', $data)
                ->setPaper('letter', 'portrait');

            $filename = "certificado_esterilizacion_{$animal->codigo_unico}.pdf";

            Log::info("✅ Certificado de esterilización generado: {$filename}");

            return $pdf->download($filename);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("❌ Animal no encontrado: {$animalId}");
            return $this->notFoundResponse('Animal no encontrado');
        } catch (\Exception $e) {
            Log::error("❌ Error generando certificado de esterilización: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return $this->serverErrorResponse('Error al generar el certificado: ' . $e->getMessage());
        }
    }

    /**
     * Generar certificado de salud general
     * GET /api/v1/certificados/salud/{animalId}
     */
    public function salud(string $animalId)
    {
        try {
            Log::info("📄 Generando certificado de salud para animal: {$animalId}");

            $animal = Animal::with([
                'historialClinico.consultas' => function($query) {
                    $query->latest()->limit(1);
                },
                'historialClinico.consultas.veterinario.usuario',
                'historialClinico.vacunas' => function($query) {
                    $query->latest()->limit(5);
                },
                'historialClinico.vacunas.tipoVacuna',
                'historialClinico.examenes' => function($query) {
                    $query->latest()->limit(3);
                }
            ])->findOrFail($animalId);

            if (!$animal->historialClinico) {
                Log::warning("⚠️ Animal {$animalId} no tiene historial clínico");
                return $this->errorResponse(
                    'El animal no tiene historial clínico',
                    null,
                    404
                );
            }

            $data = [
                'animal' => $animal,
                'historial' => $animal->historialClinico,
                'ultima_consulta' => $animal->historialClinico->consultas->first(),
                'vacunas_recientes' => $animal->historialClinico->vacunas,
                'examenes_recientes' => $animal->historialClinico->examenes,
                'fecha_emision' => now()->format('d/m/Y'),
                'tipo' => 'salud'
            ];

            Log::info("✅ Datos preparados para certificado de salud");

            $pdf = Pdf::loadView('pdf.certificado-salud', $data)
                ->setPaper('letter', 'portrait');

            $filename = "certificado_salud_{$animal->codigo_unico}.pdf";

            Log::info("✅ Certificado de salud generado: {$filename}");

            return $pdf->download($filename);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("❌ Animal no encontrado: {$animalId}");
            return $this->notFoundResponse('Animal no encontrado');
        } catch (\Exception $e) {
            Log::error("❌ Error generando certificado de salud: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return $this->serverErrorResponse('Error al generar el certificado: ' . $e->getMessage());
        }
    }

    /**
     * Generar carnet de vacunación
     * GET /api/v1/certificados/carnet/{animalId}
     */
    public function carnet(string $animalId)
    {
        try {
            Log::info("📄 Generando carnet de vacunación para animal: {$animalId}");

            $animal = Animal::with([
                'historialClinico.vacunas.tipoVacuna',
                'historialClinico.vacunas.veterinario'
            ])->findOrFail($animalId);

            if (!$animal->historialClinico || $animal->historialClinico->vacunas->isEmpty()) {
                Log::warning("⚠️ Animal {$animalId} no tiene vacunas registradas");
                return $this->errorResponse(
                    'El animal no tiene vacunas registradas',
                    null,
                    404
                );
            }

            $data = [
                'animal' => $animal,
                'vacunas' => $animal->historialClinico->vacunas,
                'fecha_emision' => now()->format('d/m/Y')
            ];

            Log::info("✅ Datos preparados para carnet de vacunación");

            $pdf = Pdf::loadView('pdf.carnet-vacunacion', $data)
                ->setPaper([0, 0, 283.46, 425.20], 'portrait'); // Tamaño carnet

            $filename = "carnet_vacunacion_{$animal->codigo_unico}.pdf";

            Log::info("✅ Carnet de vacunación generado: {$filename}");

            return $pdf->download($filename);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("❌ Animal no encontrado: {$animalId}");
            return $this->notFoundResponse('Animal no encontrado');
        } catch (\Exception $e) {
            Log::error("❌ Error generando carnet: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return $this->serverErrorResponse('Error al generar el carnet: ' . $e->getMessage());
        }
    }
}