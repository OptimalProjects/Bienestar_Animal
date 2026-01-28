<?php

namespace App\Services;

use App\Models\Adopcion\Adopcion;
use App\Models\Adopcion\VisitaDomiciliaria;
use App\Mail\VisitasProgramadasMail;
use App\Mail\RecordatorioVisitaMail;
use App\Mail\VisitaRealizadaMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VisitaSeguimientoService
{
    /**
     * Generar PDF de resumen de visita.
     */
    public function generarPdfResumen(VisitaDomiciliaria $visita): array
    {
        $visita->load(['adopcion.animal', 'adopcion.adoptante', 'visitador']);

        $data = $this->prepararDatosPdf($visita);

        $pdf = Pdf::loadView('pdf.resumen-visita', $data);
        $pdf->setPaper('letter', 'portrait');

        // Nombre del archivo
        $fileName = 'visita_' . $visita->id . '_' . now()->format('Ymd_His') . '.pdf';
        $path = 'visitas/pdf/' . $fileName;

        // Guardar en storage
        Storage::disk('public')->put($path, $pdf->output());

        Log::info('PDF de resumen de visita generado', [
            'visita_id' => $visita->id,
            'path' => $path,
        ]);

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'filename' => $fileName,
        ];
    }

    /**
     * Obtener PDF de visita sin guardar (para preview/descarga directa).
     */
    public function obtenerPdfResumen(VisitaDomiciliaria $visita)
    {
        $visita->load(['adopcion.animal', 'adopcion.adoptante', 'visitador']);
        $data = $this->prepararDatosPdf($visita);

        $pdf = Pdf::loadView('pdf.resumen-visita', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf;
    }

    /**
     * Preparar datos para la plantilla del PDF.
     */
    protected function prepararDatosPdf(VisitaDomiciliaria $visita): array
    {
        $adopcion = $visita->adopcion;
        $adoptante = $adopcion->adoptante;
        $animal = $adopcion->animal;
        $visitador = $visita->visitador;

        return [
            'visita' => $visita,
            'adopcion' => $adopcion,

            // Datos del adoptante
            'adoptante' => [
                'nombre_completo' => $adoptante->nombre_completo ?? $adoptante->nombres . ' ' . $adoptante->apellidos,
                'documento' => $adoptante->numero_documento,
                'direccion' => $adoptante->direccion,
                'telefono' => $adoptante->telefono,
                'email' => $adoptante->email,
            ],

            // Datos del animal
            'animal' => [
                'codigo' => $animal->codigo_unico,
                'nombre' => $animal->nombre ?? 'Sin nombre asignado',
                'especie' => ucfirst($animal->especie),
                'raza' => $animal->raza ?? 'Mestizo',
                'sexo' => $animal->sexo === 'macho' ? 'Macho' : 'Hembra',
            ],

            // Datos de la visita
            'tipo_visita_texto' => $this->getTipoVisitaTexto($visita->tipo_visita),
            'resultado_texto' => $this->getResultadoTexto($visita->resultado),
            'fecha_programada' => $visita->fecha_programada?->format('d/m/Y'),
            'fecha_realizada' => $visita->fecha_realizada?->format('d/m/Y H:i'),

            // Condiciones del hogar
            'condiciones_hogar' => $visita->condiciones_hogar ?? [],
            'estado_animal' => $visita->estado_animal ?? [],

            // Observaciones y recomendaciones
            'observaciones' => $visita->observaciones,
            'recomendaciones' => $visita->recomendaciones,

            // Funcionario
            'funcionario' => $visitador ? [
                'nombre' => $visitador->nombre_completo ?? $visitador->nombres . ' ' . $visitador->apellidos,
                'cargo' => 'Funcionario de Seguimiento',
            ] : null,

            // Metadata
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'numero_reporte' => $this->generarNumeroReporte($visita),
        ];
    }

    /**
     * Generar número de reporte único.
     */
    protected function generarNumeroReporte(VisitaDomiciliaria $visita): string
    {
        $year = now()->format('Y');
        $sequence = VisitaDomiciliaria::whereYear('created_at', $year)
            ->whereNotNull('fecha_realizada')
            ->count();
        return sprintf('VIS-%s-%04d', $year, $sequence);
    }

    /**
     * Notificar al adoptante las visitas programadas.
     */
    public function notificarVisitasProgramadas(Adopcion $adopcion, Collection $visitas): void
    {
        try {
            $adopcion->load(['animal', 'adoptante']);
            $emailAdoptante = $adopcion->adoptante->email ?? null;

            if (!$emailAdoptante || !filter_var($emailAdoptante, FILTER_VALIDATE_EMAIL)) {
                Log::warning('No se pudo enviar notificación de visitas programadas: email inválido', [
                    'adopcion_id' => $adopcion->id,
                    'email' => $emailAdoptante,
                ]);
                return;
            }

            Mail::to($emailAdoptante)->send(new VisitasProgramadasMail($adopcion, $visitas));

            Log::info('Notificación de visitas programadas enviada', [
                'adopcion_id' => $adopcion->id,
                'animal' => $adopcion->animal->nombre ?? $adopcion->animal->codigo_unico,
                'destinatario' => $emailAdoptante,
                'cantidad_visitas' => $visitas->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de visitas programadas', [
                'adopcion_id' => $adopcion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Enviar recordatorio de visita próxima.
     */
    public function enviarRecordatorioVisita(VisitaDomiciliaria $visita, int $diasRestantes = 0): void
    {
        try {
            $visita->load(['adopcion.animal', 'adopcion.adoptante']);
            $emailAdoptante = $visita->adopcion->adoptante->email ?? null;

            if (!$emailAdoptante || !filter_var($emailAdoptante, FILTER_VALIDATE_EMAIL)) {
                Log::warning('No se pudo enviar recordatorio de visita: email inválido', [
                    'visita_id' => $visita->id,
                    'email' => $emailAdoptante,
                ]);
                return;
            }

            Mail::to($emailAdoptante)->send(new RecordatorioVisitaMail($visita, $diasRestantes));

            Log::info('Recordatorio de visita enviado', [
                'visita_id' => $visita->id,
                'adopcion_id' => $visita->adopcion_id,
                'dias_restantes' => $diasRestantes,
                'destinatario' => $emailAdoptante,
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando recordatorio de visita', [
                'visita_id' => $visita->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notificar visita realizada con resumen.
     */
    public function notificarVisitaRealizada(VisitaDomiciliaria $visita): void
    {
        try {
            $visita->load(['adopcion.animal', 'adopcion.adoptante', 'visitador']);
            $emailAdoptante = $visita->adopcion->adoptante->email ?? null;

            if (!$emailAdoptante || !filter_var($emailAdoptante, FILTER_VALIDATE_EMAIL)) {
                Log::warning('No se pudo enviar notificación de visita realizada: email inválido', [
                    'visita_id' => $visita->id,
                    'email' => $emailAdoptante,
                ]);
                return;
            }

            // Generar PDF del resumen
            $pdfInfo = $this->generarPdfResumen($visita);

            // Enviar correo con PDF adjunto
            Mail::to($emailAdoptante)->send(new VisitaRealizadaMail($visita, $pdfInfo['path']));

            Log::info('Notificación de visita realizada enviada', [
                'visita_id' => $visita->id,
                'adopcion_id' => $visita->adopcion_id,
                'resultado' => $visita->resultado,
                'destinatario' => $emailAdoptante,
                'pdf_path' => $pdfInfo['path'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de visita realizada', [
                'visita_id' => $visita->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Obtener visitas que necesitan recordatorio.
     *
     * @param int $diasAntes Días antes de la visita para enviar recordatorio
     * @return Collection
     */
    public function getVisitasParaRecordatorio(int $diasAntes = 1): Collection
    {
        $fechaObjetivo = now()->addDays($diasAntes)->toDateString();

        return VisitaDomiciliaria::with(['adopcion.animal', 'adopcion.adoptante'])
            ->whereNull('fecha_realizada')
            ->whereDate('fecha_programada', $fechaObjetivo)
            ->whereHas('adopcion', function ($query) {
                $query->whereIn('estado', ['aprobada', 'completada']);
            })
            ->get();
    }

    /**
     * Obtener visitas programadas para hoy.
     */
    public function getVisitasHoy(): Collection
    {
        return VisitaDomiciliaria::with(['adopcion.animal', 'adopcion.adoptante'])
            ->whereNull('fecha_realizada')
            ->whereDate('fecha_programada', today())
            ->whereHas('adopcion', function ($query) {
                $query->whereIn('estado', ['aprobada', 'completada']);
            })
            ->get();
    }

    // Helpers para textos
    protected function getTipoVisitaTexto(string $tipo): string
    {
        return match($tipo) {
            'pre_adopcion' => 'Pre-adopción',
            'seguimiento_1mes' => 'Seguimiento 1 mes',
            'seguimiento_3meses' => 'Seguimiento 3 meses',
            'seguimiento_6meses' => 'Seguimiento 6 meses',
            'extraordinaria' => 'Extraordinaria',
            default => ucfirst(str_replace('_', ' ', $tipo)),
        };
    }

    protected function getResultadoTexto(?string $resultado): string
    {
        return match($resultado) {
            'satisfactoria' => 'Satisfactoria',
            'observaciones' => 'Con Observaciones',
            'critica' => 'Crítica',
            default => ucfirst($resultado ?? 'No especificado'),
        };
    }
}
