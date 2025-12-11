<?php

namespace App\Services;

use App\Models\Animal\Animal;
use App\Models\Adopcion\Adopcion;
use App\Models\Denuncia\Denuncia;
use App\Models\Veterinaria\Consulta;
use App\Models\Veterinaria\Vacuna;
use App\Models\Veterinaria\Cirugia;
use App\Models\Administracion\Indicador;
use App\Models\Administracion\PuntoIndicador;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    /**
     * Obtener resumen para dashboard.
     */
    public function getDashboard(): array
    {
        return [
            'animales' => $this->getResumenAnimales(),
            'adopciones' => $this->getResumenAdopciones(),
            'denuncias' => $this->getResumenDenuncias(),
            'veterinaria' => $this->getResumenVeterinaria(),
            'tendencias' => $this->getTendencias(),
        ];
    }

    /**
     * Resumen de animales.
     */
    protected function getResumenAnimales(): array
    {
        return [
            'total' => Animal::count(),
            'en_refugio' => Animal::enRefugio()->count(),
            'en_adopcion' => Animal::disponiblesAdopcion()->count(),
            'adoptados' => Animal::adoptados()->count(),
            'ingresos_mes' => Animal::where('created_at', '>=', now()->startOfMonth())->count(),
            'por_especie' => Animal::selectRaw('especie, count(*) as cantidad')
                ->groupBy('especie')
                ->pluck('cantidad', 'especie')
                ->toArray(),
            'por_estado_salud' => Animal::selectRaw('estado_salud, count(*) as cantidad')
                ->groupBy('estado_salud')
                ->pluck('cantidad', 'estado_salud')
                ->toArray(),
        ];
    }

    /**
     * Resumen de adopciones.
     */
    protected function getResumenAdopciones(): array
    {
        $inicioMes = now()->startOfMonth();

        return [
            'pendientes' => Adopcion::pendientes()->count(),
            'aprobadas_mes' => Adopcion::where('estado', 'aprobada')
                ->where('fecha_aprobacion', '>=', $inicioMes)
                ->count(),
            'rechazadas_mes' => Adopcion::where('estado', 'rechazada')
                ->where('updated_at', '>=', $inicioMes)
                ->count(),
            'tasa_aprobacion' => $this->calcularTasaAprobacion(),
        ];
    }

    /**
     * Resumen de denuncias.
     */
    protected function getResumenDenuncias(): array
    {
        return [
            'pendientes' => Denuncia::whereIn('estado', ['recibida', 'en_proceso'])->count(),
            'urgentes' => Denuncia::urgentes()->whereIn('estado', ['recibida', 'en_proceso'])->count(),
            'sin_asignar' => Denuncia::sinAsignar()->whereIn('estado', ['recibida', 'en_proceso'])->count(),
            'recibidas_mes' => Denuncia::where('created_at', '>=', now()->startOfMonth())->count(),
            'resueltas_mes' => Denuncia::where('estado', 'resuelta')
                ->where('fecha_resolucion', '>=', now()->startOfMonth())
                ->count(),
            'por_tipo' => Denuncia::selectRaw('tipo_denuncia, count(*) as cantidad')
                ->groupBy('tipo_denuncia')
                ->pluck('cantidad', 'tipo_denuncia')
                ->toArray(),
        ];
    }

    /**
     * Resumen de veterinaria.
     */
    protected function getResumenVeterinaria(): array
    {
        $hoy = now()->toDateString();

        return [
            'consultas_hoy' => Consulta::deHoy()->count(),
            'consultas_mes' => Consulta::where('fecha_consulta', '>=', now()->startOfMonth())->count(),
            'vacunas_mes' => Vacuna::where('fecha_aplicacion', '>=', now()->startOfMonth())->count(),
            'cirugias_mes' => Cirugia::where('fecha_realizacion', '>=', now()->startOfMonth())->count(),
            'esterilizaciones_mes' => Cirugia::where('tipo_cirugia', 'esterilizacion')
                ->where('fecha_realizacion', '>=', now()->startOfMonth())
                ->count(),
        ];
    }

    /**
     * Obtener tendencias (ultimos 6 meses).
     */
    protected function getTendencias(): array
    {
        $meses = collect();
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $meses->push([
                'mes' => $fecha->format('Y-m'),
                'label' => $fecha->translatedFormat('M Y'),
            ]);
        }

        return [
            'ingresos_animales' => $this->getTendenciaPorMes(Animal::class, 'created_at', $meses),
            'adopciones' => $this->getTendenciaPorMes(Adopcion::class, 'fecha_aprobacion', $meses, ['estado' => 'aprobada']),
            'denuncias' => $this->getTendenciaPorMes(Denuncia::class, 'created_at', $meses),
            'consultas' => $this->getTendenciaPorMes(Consulta::class, 'fecha_consulta', $meses),
        ];
    }

    /**
     * Calcular tendencia por mes.
     */
    protected function getTendenciaPorMes(string $model, string $dateField, $meses, array $where = []): array
    {
        $query = $model::selectRaw("DATE_FORMAT({$dateField}, '%Y-%m') as mes, count(*) as cantidad")
            ->where($dateField, '>=', now()->subMonths(6)->startOfMonth());

        foreach ($where as $field => $value) {
            $query->where($field, $value);
        }

        $datos = $query->groupBy('mes')->pluck('cantidad', 'mes');

        return $meses->map(fn($m) => [
            'mes' => $m['label'],
            'cantidad' => $datos[$m['mes']] ?? 0,
        ])->toArray();
    }

    /**
     * Calcular tasa de aprobacion.
     */
    protected function calcularTasaAprobacion(): float
    {
        $total = Adopcion::whereIn('estado', ['aprobada', 'rechazada'])
            ->where('updated_at', '>=', now()->subMonths(3))
            ->count();

        if ($total === 0) {
            return 0;
        }

        $aprobadas = Adopcion::where('estado', 'aprobada')
            ->where('fecha_aprobacion', '>=', now()->subMonths(3))
            ->count();

        return round(($aprobadas / $total) * 100, 1);
    }

    /**
     * Obtener indicadores KPI.
     */
    public function getIndicadores(): array
    {
        $indicadores = Indicador::activos()->with(['puntos' => function ($q) {
            $q->orderBy('fecha', 'desc')->limit(30);
        }])->get();

        return $indicadores->map(function ($indicador) {
            return [
                'codigo' => $indicador->codigo,
                'nombre' => $indicador->nombre,
                'descripcion' => $indicador->descripcion,
                'tipo' => $indicador->tipo,
                'unidad_medida' => $indicador->unidad_medida,
                'ultimo_valor' => $indicador->ultimo_valor,
                'historial' => $indicador->puntos->map(fn($p) => [
                    'fecha' => $p->fecha->toDateString(),
                    'valor' => $p->valor,
                ])->toArray(),
            ];
        })->toArray();
    }

    /**
     * Registrar punto de indicador.
     */
    public function registrarIndicador(string $indicadorId, float $valor, ?array $dimensiones = null): PuntoIndicador
    {
        return PuntoIndicador::create([
            'indicador_id' => $indicadorId,
            'fecha' => now()->toDateString(),
            'valor' => $valor,
            'dimensiones' => $dimensiones,
            'calidad' => 'validado',
        ]);
    }

    /**
     * Generar reporte de periodo.
     */
    public function generarReportePeriodo(string $fechaInicio, string $fechaFin): array
    {
        return [
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
            'animales' => [
                'ingresos' => Animal::whereBetween('created_at', [$fechaInicio, $fechaFin])->count(),
                'por_especie' => Animal::selectRaw('especie, count(*) as cantidad')
                    ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                    ->groupBy('especie')
                    ->pluck('cantidad', 'especie')
                    ->toArray(),
            ],
            'adopciones' => [
                'solicitudes' => Adopcion::whereBetween('fecha_solicitud', [$fechaInicio, $fechaFin])->count(),
                'aprobadas' => Adopcion::where('estado', 'aprobada')
                    ->whereBetween('fecha_aprobacion', [$fechaInicio, $fechaFin])
                    ->count(),
                'rechazadas' => Adopcion::where('estado', 'rechazada')
                    ->whereBetween('updated_at', [$fechaInicio, $fechaFin])
                    ->count(),
            ],
            'denuncias' => [
                'recibidas' => Denuncia::whereBetween('created_at', [$fechaInicio, $fechaFin])->count(),
                'resueltas' => Denuncia::where('estado', 'resuelta')
                    ->whereBetween('fecha_resolucion', [$fechaInicio, $fechaFin])
                    ->count(),
                'por_tipo' => Denuncia::selectRaw('tipo_denuncia, count(*) as cantidad')
                    ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                    ->groupBy('tipo_denuncia')
                    ->pluck('cantidad', 'tipo_denuncia')
                    ->toArray(),
            ],
            'veterinaria' => [
                'consultas' => Consulta::whereBetween('fecha_consulta', [$fechaInicio, $fechaFin])->count(),
                'vacunas' => Vacuna::whereBetween('fecha_aplicacion', [$fechaInicio, $fechaFin])->count(),
                'cirugias' => Cirugia::whereBetween('fecha_realizacion', [$fechaInicio, $fechaFin])->count(),
            ],
            'generado_en' => now()->toDateTimeString(),
        ];
    }
}
