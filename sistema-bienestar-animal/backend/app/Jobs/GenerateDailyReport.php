<?php

namespace App\Jobs;

use App\Models\Animal;
use App\Models\Adopciones\SolicitudAdopcion;
use App\Models\Denuncias\Denuncia;
use App\Models\Veterinaria\Consulta;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateDailyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Generando reporte diario...');

        $fecha = now()->subDay()->format('Y-m-d');

        $reporte = [
            'fecha' => $fecha,
            'generado_en' => now()->toIso8601String(),
            'animales' => $this->getAnimalesStats($fecha),
            'adopciones' => $this->getAdopcionesStats($fecha),
            'denuncias' => $this->getDenunciasStats($fecha),
            'veterinaria' => $this->getVeterinariaStats($fecha),
        ];

        // Guardar reporte en storage S3
        $filename = "documentos/reportes/diario_{$fecha}.json";
        Storage::disk('s3')->put($filename, json_encode($reporte, JSON_PRETTY_PRINT));

        Log::info('Reporte diario generado', [
            'fecha' => $fecha,
            'archivo' => $filename,
            'resumen' => [
                'animales_ingresados' => $reporte['animales']['ingresados'],
                'adopciones_aprobadas' => $reporte['adopciones']['aprobadas'],
                'denuncias_recibidas' => $reporte['denuncias']['recibidas'],
                'consultas_realizadas' => $reporte['veterinaria']['consultas'],
            ],
        ]);
    }

    protected function getAnimalesStats(string $fecha): array
    {
        return [
            'total' => Animal::count(),
            'ingresados' => Animal::whereDate('created_at', $fecha)->count(),
            'en_refugio' => Animal::where('estado', 'en_refugio')->count(),
            'en_adopcion' => Animal::where('estado', 'en_adopcion')->count(),
            'adoptados' => Animal::where('estado', 'adoptado')->count(),
            'por_especie' => [
                'perros' => Animal::whereIn('especie', ['perro', 'canino'])->count(),
                'gatos' => Animal::whereIn('especie', ['gato', 'felino'])->count(),
                'otros' => Animal::whereNotIn('especie', ['perro', 'canino', 'gato', 'felino'])->count(),
            ],
        ];
    }

    protected function getAdopcionesStats(string $fecha): array
    {
        return [
            'solicitudes_nuevas' => SolicitudAdopcion::whereDate('created_at', $fecha)->count(),
            'aprobadas' => SolicitudAdopcion::whereDate('updated_at', $fecha)
                ->where('estado', 'aprobada')
                ->count(),
            'rechazadas' => SolicitudAdopcion::whereDate('updated_at', $fecha)
                ->where('estado', 'rechazada')
                ->count(),
            'pendientes_total' => SolicitudAdopcion::where('estado', 'pendiente')->count(),
        ];
    }

    protected function getDenunciasStats(string $fecha): array
    {
        return [
            'recibidas' => Denuncia::whereDate('created_at', $fecha)->count(),
            'resueltas' => Denuncia::whereDate('updated_at', $fecha)
                ->where('estado', 'resuelta')
                ->count(),
            'urgentes_pendientes' => Denuncia::where('prioridad', 'urgente')
                ->whereNotIn('estado', ['resuelta', 'cerrada'])
                ->count(),
            'por_tipo' => Denuncia::whereDate('created_at', $fecha)
                ->selectRaw('tipo_denuncia, COUNT(*) as total')
                ->groupBy('tipo_denuncia')
                ->pluck('total', 'tipo_denuncia')
                ->toArray(),
        ];
    }

    protected function getVeterinariaStats(string $fecha): array
    {
        return [
            'consultas' => Consulta::whereDate('fecha', $fecha)->count(),
            'por_tipo' => Consulta::whereDate('fecha', $fecha)
                ->selectRaw('tipo_consulta, COUNT(*) as total')
                ->groupBy('tipo_consulta')
                ->pluck('total', 'tipo_consulta')
                ->toArray(),
        ];
    }
}
