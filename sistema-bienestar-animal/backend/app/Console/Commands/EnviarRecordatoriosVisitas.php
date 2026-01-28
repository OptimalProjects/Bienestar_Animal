<?php

namespace App\Console\Commands;

use App\Services\VisitaSeguimientoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnviarRecordatoriosVisitas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visitas:recordatorios
                            {--dias=1 : Días antes de la visita para enviar recordatorio}
                            {--incluir-hoy : Incluir también visitas programadas para hoy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar recordatorios de visitas de seguimiento próximas a los adoptantes';

    /**
     * Execute the console command.
     */
    public function handle(VisitaSeguimientoService $visitaService): int
    {
        $diasAntes = (int) $this->option('dias');
        $incluirHoy = $this->option('incluir-hoy');

        $this->info("Buscando visitas programadas para dentro de {$diasAntes} día(s)...");

        // Recordatorios para visitas próximas
        $visitasProximas = $visitaService->getVisitasParaRecordatorio($diasAntes);
        $enviadosProximas = 0;

        foreach ($visitasProximas as $visita) {
            try {
                $visitaService->enviarRecordatorioVisita($visita, $diasAntes);
                $enviadosProximas++;

                $this->line("  ✓ Recordatorio enviado para visita {$visita->id} - Animal: {$visita->adopcion->animal->nombre}");
            } catch (\Exception $e) {
                $this->error("  ✗ Error enviando recordatorio para visita {$visita->id}: {$e->getMessage()}");
            }
        }

        $this->info("Recordatorios enviados para visitas próximas: {$enviadosProximas} de {$visitasProximas->count()}");

        // Recordatorios para visitas de hoy (si se especifica)
        if ($incluirHoy) {
            $this->info("Buscando visitas programadas para HOY...");

            $visitasHoy = $visitaService->getVisitasHoy();
            $enviadosHoy = 0;

            foreach ($visitasHoy as $visita) {
                try {
                    $visitaService->enviarRecordatorioVisita($visita, 0);
                    $enviadosHoy++;

                    $this->line("  ✓ Recordatorio HOY enviado para visita {$visita->id} - Animal: {$visita->adopcion->animal->nombre}");
                } catch (\Exception $e) {
                    $this->error("  ✗ Error enviando recordatorio para visita {$visita->id}: {$e->getMessage()}");
                }
            }

            $this->info("Recordatorios enviados para visitas de HOY: {$enviadosHoy} de {$visitasHoy->count()}");
        }

        $totalEnviados = $enviadosProximas + ($incluirHoy ? $enviadosHoy : 0);

        Log::info('Comando de recordatorios de visitas ejecutado', [
            'dias_antes' => $diasAntes,
            'incluir_hoy' => $incluirHoy,
            'visitas_proximas' => $visitasProximas->count(),
            'enviados_proximas' => $enviadosProximas,
            'visitas_hoy' => $incluirHoy ? $visitasHoy->count() : 0,
            'enviados_hoy' => $incluirHoy ? $enviadosHoy : 0,
            'total_enviados' => $totalEnviados,
        ]);

        $this->newLine();
        $this->info("Total de recordatorios enviados: {$totalEnviados}");

        return Command::SUCCESS;
    }
}
