<?php

namespace App\Jobs;

use App\Models\Adopciones\SolicitudAdopcion;
use App\Models\Adopciones\VisitaSeguimiento;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAdoptionReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Procesando recordatorios de adopciones...');

        // Solicitudes pendientes de evaluacion por mas de 5 dias
        $solicitudesPendientes = SolicitudAdopcion::where('estado', 'pendiente')
            ->where('created_at', '<=', now()->subDays(5))
            ->with(['adoptante', 'animal'])
            ->get();

        foreach ($solicitudesPendientes as $solicitud) {
            // Registrar recordatorio en log (en futuro se puede enviar email/notificacion)
            Log::warning('Recordatorio: Solicitud de adopcion pendiente', [
                'solicitud_id' => $solicitud->id,
                'animal' => $solicitud->animal?->nombre,
                'adoptante' => $solicitud->adoptante?->nombre_completo,
                'dias_pendiente' => now()->diffInDays($solicitud->created_at),
            ]);
        }

        // Visitas de seguimiento programadas para hoy
        $visitasHoy = VisitaSeguimiento::whereDate('fecha_programada', today())
            ->where('estado', 'programada')
            ->with(['adopcion.adoptante', 'adopcion.animal'])
            ->get();

        foreach ($visitasHoy as $visita) {
            Log::info('Recordatorio: Visita de seguimiento programada para hoy', [
                'visita_id' => $visita->id,
                'adopcion_id' => $visita->adopcion_id,
                'animal' => $visita->adopcion?->animal?->nombre,
                'adoptante' => $visita->adopcion?->adoptante?->nombre_completo,
            ]);
        }

        // Visitas de seguimiento vencidas
        $visitasVencidas = VisitaSeguimiento::where('fecha_programada', '<', today())
            ->where('estado', 'programada')
            ->with(['adopcion.adoptante', 'adopcion.animal'])
            ->get();

        foreach ($visitasVencidas as $visita) {
            Log::warning('Alerta: Visita de seguimiento vencida', [
                'visita_id' => $visita->id,
                'fecha_programada' => $visita->fecha_programada,
                'dias_vencida' => now()->diffInDays($visita->fecha_programada),
            ]);
        }

        Log::info('Proceso de recordatorios de adopciones completado', [
            'solicitudes_pendientes' => $solicitudesPendientes->count(),
            'visitas_hoy' => $visitasHoy->count(),
            'visitas_vencidas' => $visitasVencidas->count(),
        ]);
    }
}
