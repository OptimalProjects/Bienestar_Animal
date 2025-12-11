<?php

namespace App\Jobs;

use App\Models\Veterinaria\RecordatorioVacuna;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessVaccineReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Procesando recordatorios de vacunas...');

        // Recordatorios de vacunas para los proximos 7 dias
        $recordatoriosProximos = RecordatorioVacuna::where('estado', 'pendiente')
            ->whereBetween('fecha_recordatorio', [today(), today()->addDays(7)])
            ->with(['historialClinico.animal', 'tipoVacuna'])
            ->get();

        foreach ($recordatoriosProximos as $recordatorio) {
            $diasRestantes = now()->diffInDays($recordatorio->fecha_recordatorio, false);

            Log::info('Recordatorio de vacuna proximo', [
                'recordatorio_id' => $recordatorio->id,
                'animal' => $recordatorio->historialClinico?->animal?->nombre,
                'vacuna' => $recordatorio->tipoVacuna?->nombre,
                'fecha' => $recordatorio->fecha_recordatorio,
                'dias_restantes' => $diasRestantes,
            ]);
        }

        // Recordatorios vencidos (vacunas que debieron aplicarse)
        $recordatoriosVencidos = RecordatorioVacuna::where('estado', 'pendiente')
            ->where('fecha_recordatorio', '<', today())
            ->with(['historialClinico.animal', 'tipoVacuna'])
            ->get();

        foreach ($recordatoriosVencidos as $recordatorio) {
            Log::warning('Alerta: Vacuna vencida', [
                'recordatorio_id' => $recordatorio->id,
                'animal' => $recordatorio->historialClinico?->animal?->nombre,
                'vacuna' => $recordatorio->tipoVacuna?->nombre,
                'fecha_vencimiento' => $recordatorio->fecha_recordatorio,
                'dias_vencida' => now()->diffInDays($recordatorio->fecha_recordatorio),
            ]);
        }

        Log::info('Proceso de recordatorios de vacunas completado', [
            'proximos_7_dias' => $recordatoriosProximos->count(),
            'vencidos' => $recordatoriosVencidos->count(),
        ]);
    }
}
