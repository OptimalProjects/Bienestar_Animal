<?php

namespace App\Jobs;

use App\Models\Denuncias\Denuncia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDenunciaUrgency implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Procesando urgencia de denuncias...');

        // Obtener denuncias pendientes que llevan mas de 24 horas sin atender
        $denunciasUrgentes = Denuncia::where('estado', 'recibida')
            ->where('prioridad', '!=', 'urgente')
            ->where('created_at', '<=', now()->subHours(24))
            ->get();

        foreach ($denunciasUrgentes as $denuncia) {
            $denuncia->update([
                'prioridad' => 'urgente',
            ]);

            Log::info("Denuncia {$denuncia->numero_ticket} marcada como urgente por tiempo excedido");
        }

        // Obtener denuncias en proceso que llevan mas de 48 horas
        $denunciasExcedidas = Denuncia::where('estado', 'en_proceso')
            ->where('updated_at', '<=', now()->subHours(48))
            ->get();

        foreach ($denunciasExcedidas as $denuncia) {
            if ($denuncia->prioridad !== 'urgente') {
                $denuncia->update([
                    'prioridad' => 'urgente',
                ]);

                Log::warning("Denuncia {$denuncia->numero_ticket} escalada a urgente - 48h sin resolucion");
            }
        }

        Log::info('Proceso de urgencia de denuncias completado', [
            'urgentes_nuevas' => $denunciasUrgentes->count(),
            'escaladas' => $denunciasExcedidas->count(),
        ]);
    }
}
