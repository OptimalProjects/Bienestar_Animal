<?php

namespace App\Jobs;

use App\Models\User\EventoAuditoria;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanupOldAuditLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Dias de retencion de logs de auditoria
     */
    protected int $retentionDays = 365;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Limpiando logs de auditoria antiguos...');

        $fechaLimite = now()->subDays($this->retentionDays);

        // Contar registros a eliminar
        $countToDelete = EventoAuditoria::where('timestamp', '<', $fechaLimite)->count();

        if ($countToDelete === 0) {
            Log::info('No hay logs de auditoria antiguos para eliminar');
            return;
        }

        // Eliminar en lotes para no sobrecargar la base de datos
        $deleted = 0;
        $batchSize = 1000;

        while ($deleted < $countToDelete) {
            $batch = EventoAuditoria::where('timestamp', '<', $fechaLimite)
                ->limit($batchSize)
                ->delete();

            $deleted += $batch;

            // Pausa breve entre lotes
            if ($deleted < $countToDelete) {
                usleep(100000); // 100ms
            }
        }

        Log::info('Limpieza de logs de auditoria completada', [
            'registros_eliminados' => $deleted,
            'fecha_limite' => $fechaLimite->toDateString(),
            'dias_retencion' => $this->retentionDays,
        ]);
    }
}
