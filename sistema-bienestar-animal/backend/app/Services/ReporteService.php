<?php

namespace App\Services;

use App\Models\Animal\Animal;
use App\Models\Adopcion\Adopcion;
use App\Models\Denuncia\Denuncia;
use App\Models\Veterinaria\Consulta;
use App\Models\Veterinaria\Vacuna;
use App\Models\Veterinaria\Cirugia;
use App\Models\Veterinaria\TipoVacuna;
use App\Models\Administracion\Indicador;
use App\Models\Administracion\PuntoIndicador;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteService
{
    /**
     * Obtener resumen para dashboard con filtros opcionales.
     */
    public function getDashboard(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        // Determinar el rango de fechas
        $rangoFechas = $this->calcularRangoFechas($fechaInicio, $fechaFin);

        return [
            'animales' => $this->getResumenAnimales($rangoFechas),
            'adopciones' => $this->getResumenAdopciones($rangoFechas),
            'denuncias' => $this->getResumenDenuncias($rangoFechas),
            'veterinaria' => $this->getResumenVeterinaria($rangoFechas),
            'tendencias' => $this->getTendencias($rangoFechas),
            'cobertura_vacunacion' => $this->getCoberturaVacunacion(),
            'periodo' => [
                'inicio' => $rangoFechas['inicio']->toDateString(),
                'fin' => $rangoFechas['fin']->toDateString(),
                'label' => $rangoFechas['label'],
            ],
        ];
    }

    /**
     * Calcular rango de fechas basado en parámetros o usar mes actual.
     */
    protected function calcularRangoFechas(?string $fechaInicio, ?string $fechaFin): array
    {
        if ($fechaInicio && $fechaFin) {
            return [
                'inicio' => Carbon::parse($fechaInicio)->startOfDay(),
                'fin' => Carbon::parse($fechaFin)->endOfDay(),
                'label' => 'Personalizado',
                'meses_tendencia' => 6,
            ];
        }

        // Por defecto: mes actual
        return [
            'inicio' => now()->startOfMonth(),
            'fin' => now()->endOfDay(),
            'label' => now()->translatedFormat('F Y'),
            'meses_tendencia' => 6,
        ];
    }

    /**
     * Resumen de animales.
     */
    protected function getResumenAnimales(array $rangoFechas): array
    {
        $inicio = $rangoFechas['inicio'];
        $fin = $rangoFechas['fin'];

        // Animales registrados en el periodo seleccionado
        $animalesPeriodo = Animal::whereBetween('created_at', [$inicio, $fin]);

        return [
            'total' => Animal::count(), // Total histórico (no cambia con filtro)
            'total_periodo' => (clone $animalesPeriodo)->count(),
            'en_refugio' => Animal::enRefugio()->count(),
            'en_adopcion' => Animal::disponiblesAdopcion()->count(),
            'adoptados' => Animal::adoptados()->count(),
            'en_tratamiento' => Animal::where('estado', 'en_tratamiento')->count(),
            'ingresos_periodo' => (clone $animalesPeriodo)->count(),
            'ingresos_mes' => Animal::where('created_at', '>=', now()->startOfMonth())->count(),
            'por_especie' => Animal::selectRaw('especie, count(*) as cantidad')
                ->whereBetween('created_at', [$inicio, $fin])
                ->groupBy('especie')
                ->pluck('cantidad', 'especie')
                ->toArray(),
            'por_especie_total' => Animal::selectRaw('especie, count(*) as cantidad')
                ->groupBy('especie')
                ->pluck('cantidad', 'especie')
                ->toArray(),
            'por_estado' => Animal::selectRaw('estado, count(*) as cantidad')
                ->groupBy('estado')
                ->pluck('cantidad', 'estado')
                ->toArray(),
            'por_estado_salud' => Animal::selectRaw('estado_salud, count(*) as cantidad')
                ->groupBy('estado_salud')
                ->pluck('cantidad', 'estado_salud')
                ->toArray(),
            'por_tamanio' => Animal::selectRaw('tamanio, count(*) as cantidad')
                ->groupBy('tamanio')
                ->pluck('cantidad', 'tamanio')
                ->toArray(),
        ];
    }

    /**
     * Resumen de adopciones.
     */
    protected function getResumenAdopciones(array $rangoFechas): array
    {
        $inicio = $rangoFechas['inicio'];
        $fin = $rangoFechas['fin'];

        // Adopciones creadas en el periodo (solicitudes registradas)
        $adopcionesPeriodo = Adopcion::whereBetween('created_at', [$inicio, $fin]);

        // Adopciones completadas en el periodo (por fecha de creación para consistencia)
        $completadasPeriodo = Adopcion::where('estado', 'completada')
            ->whereBetween('created_at', [$inicio, $fin])
            ->count();

        // Adopciones aprobadas en el periodo (por fecha de creación para consistencia)
        $aprobadasPeriodo = Adopcion::whereIn('estado', ['aprobada', 'completada'])
            ->whereBetween('created_at', [$inicio, $fin])
            ->count();

        return [
            'total' => Adopcion::count(), // Total histórico
            'total_periodo' => (clone $adopcionesPeriodo)->count(), // Total en el periodo
            'pendientes' => (clone $adopcionesPeriodo)->whereIn('estado', ['pendiente', 'en_evaluacion'])->count(),
            'pendientes_total' => Adopcion::pendientes()->count(),
            'en_evaluacion' => Adopcion::where('estado', 'en_evaluacion')->count(),
            'aprobadas_periodo' => $aprobadasPeriodo,
            'aprobadas_mes' => Adopcion::whereIn('estado', ['aprobada', 'completada'])
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'completadas_periodo' => $completadasPeriodo,
            'completadas_mes' => Adopcion::where('estado', 'completada')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'rechazadas_periodo' => (clone $adopcionesPeriodo)->where('estado', 'rechazada')->count(),
            'rechazadas_mes' => Adopcion::where('estado', 'rechazada')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'tasa_aprobacion' => $this->calcularTasaAprobacion(),
            'por_estado' => Adopcion::selectRaw('estado, count(*) as cantidad')
                ->groupBy('estado')
                ->pluck('cantidad', 'estado')
                ->toArray(),
        ];
    }

    /**
     * Resumen de denuncias.
     */
    protected function getResumenDenuncias(array $rangoFechas): array
    {
        $inicio = $rangoFechas['inicio'];
        $fin = $rangoFechas['fin'];

        // Denuncias en el periodo seleccionado
        $denunciasPeriodo = Denuncia::whereBetween('created_at', [$inicio, $fin]);

        return [
            'total' => Denuncia::count(), // Total histórico
            'total_periodo' => (clone $denunciasPeriodo)->count(),
            'pendientes' => (clone $denunciasPeriodo)->whereIn('estado', ['recibida', 'en_revision', 'en_atencion'])->count(),
            'pendientes_total' => Denuncia::whereIn('estado', ['recibida', 'en_revision', 'en_atencion'])->count(),
            'urgentes' => (clone $denunciasPeriodo)->where('prioridad', 'urgente')->whereIn('estado', ['recibida', 'en_revision', 'en_atencion'])->count(),
            'urgentes_total' => Denuncia::urgentes()->whereIn('estado', ['recibida', 'en_revision', 'en_atencion'])->count(),
            'sin_asignar' => Denuncia::sinAsignar()->whereIn('estado', ['recibida', 'en_revision'])->count(),
            'recibidas_periodo' => (clone $denunciasPeriodo)->count(),
            'recibidas_mes' => Denuncia::where('created_at', '>=', now()->startOfMonth())->count(),
            'resueltas_periodo' => Denuncia::where('estado', 'resuelta')
                ->whereBetween('updated_at', [$inicio, $fin])
                ->count(),
            'resueltas_mes' => Denuncia::where('estado', 'resuelta')
                ->where('updated_at', '>=', now()->startOfMonth())
                ->count(),
            'por_tipo' => Denuncia::selectRaw('tipo_denuncia, count(*) as cantidad')
                ->whereBetween('created_at', [$inicio, $fin])
                ->groupBy('tipo_denuncia')
                ->pluck('cantidad', 'tipo_denuncia')
                ->toArray(),
            'por_tipo_total' => Denuncia::selectRaw('tipo_denuncia, count(*) as cantidad')
                ->groupBy('tipo_denuncia')
                ->pluck('cantidad', 'tipo_denuncia')
                ->toArray(),
            'por_estado' => Denuncia::selectRaw('estado, count(*) as cantidad')
                ->groupBy('estado')
                ->pluck('cantidad', 'estado')
                ->toArray(),
            'por_prioridad' => Denuncia::selectRaw('prioridad, count(*) as cantidad')
                ->groupBy('prioridad')
                ->pluck('cantidad', 'prioridad')
                ->toArray(),
        ];
    }

    /**
     * Resumen de veterinaria.
     */
    protected function getResumenVeterinaria(array $rangoFechas): array
    {
        $inicio = $rangoFechas['inicio'];
        $fin = $rangoFechas['fin'];

        return [
            'consultas_hoy' => Consulta::deHoy()->count(),
            'consultas_periodo' => Consulta::whereBetween('fecha_consulta', [$inicio, $fin])->count(),
            'consultas_mes' => Consulta::where('fecha_consulta', '>=', now()->startOfMonth())->count(),
            'vacunas_periodo' => Vacuna::whereBetween('fecha_aplicacion', [$inicio, $fin])->count(),
            'vacunas_mes' => Vacuna::where('fecha_aplicacion', '>=', now()->startOfMonth())->count(),
            'cirugias_periodo' => Cirugia::whereBetween('fecha_realizacion', [$inicio, $fin])->count(),
            'cirugias_mes' => Cirugia::where('fecha_realizacion', '>=', now()->startOfMonth())->count(),
            'esterilizaciones_periodo' => Cirugia::whereIn('tipo_cirugia', ['esterilizacion', 'castracion'])
                ->whereBetween('fecha_realizacion', [$inicio, $fin])
                ->count(),
            'esterilizaciones_mes' => Cirugia::whereIn('tipo_cirugia', ['esterilizacion', 'castracion'])
                ->where('fecha_realizacion', '>=', now()->startOfMonth())
                ->count(),
            'por_tipo_consulta' => Consulta::selectRaw('tipo_consulta, count(*) as cantidad')
                ->groupBy('tipo_consulta')
                ->pluck('cantidad', 'tipo_consulta')
                ->toArray(),
            'por_tipo_cirugia' => Cirugia::selectRaw('tipo_cirugia, count(*) as cantidad')
                ->groupBy('tipo_cirugia')
                ->pluck('cantidad', 'tipo_cirugia')
                ->toArray(),
        ];
    }

    /**
     * Obtener tendencias (ultimos 6 meses).
     */
    protected function getTendencias(array $rangoFechas): array
    {
        $cantidadMeses = $rangoFechas['meses_tendencia'] ?? 6;
        $meses = collect();
        for ($i = $cantidadMeses - 1; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $meses->push([
                'mes' => $fecha->format('Y-m'),
                'label' => $fecha->translatedFormat('M Y'),
            ]);
        }

        return [
            'ingresos_animales' => $this->getTendenciaPorMes(Animal::class, 'created_at', $meses),
            'adopciones' => $this->getTendenciaPorMes(Adopcion::class, 'created_at', $meses),
            'adopciones_aprobadas' => $this->getTendenciaPorMes(Adopcion::class, 'fecha_aprobacion', $meses, ['estado' => 'aprobada']),
            'adopciones_completadas' => $this->getTendenciaPorMes(Adopcion::class, 'fecha_entrega', $meses, ['estado' => 'completada']),
            'denuncias' => $this->getTendenciaPorMes(Denuncia::class, 'created_at', $meses),
            'denuncias_resueltas' => $this->getTendenciaPorMes(Denuncia::class, 'updated_at', $meses, ['estado' => 'resuelta']),
            'consultas' => $this->getTendenciaPorMes(Consulta::class, 'fecha_consulta', $meses),
            'vacunas' => $this->getTendenciaPorMes(Vacuna::class, 'fecha_aplicacion', $meses),
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
     * Obtener cobertura de vacunación.
     */
    protected function getCoberturaVacunacion(): array
    {
        $totalAnimales = Animal::whereIn('estado', ['en_refugio', 'en_adopcion', 'adoptado'])->count();

        if ($totalAnimales === 0) {
            return [
                'total_animales' => 0,
                'vacunados' => 0,
                'porcentaje' => 0,
                'por_tipo_vacuna' => [],
            ];
        }

        // Animales con al menos una vacuna (a través del historial clínico)
        $animalesVacunados = Animal::whereIn('estado', ['en_refugio', 'en_adopcion', 'adoptado'])
            ->whereHas('historialClinico.vacunas')
            ->count();

        // Cobertura por tipo de vacuna
        $tiposVacuna = TipoVacuna::withCount(['vacunas' => function ($query) {
            $query->whereHas('historialClinico.animal', function ($q) {
                $q->whereIn('estado', ['en_refugio', 'en_adopcion', 'adoptado']);
            });
        }])->get();

        $porTipoVacuna = $tiposVacuna->map(function ($tipo) use ($totalAnimales) {
            return [
                'nombre' => $tipo->nombre,
                'cantidad' => $tipo->vacunas_count,
                'porcentaje' => round(($tipo->vacunas_count / $totalAnimales) * 100, 1),
            ];
        })->filter(fn($t) => $t['cantidad'] > 0)->values()->toArray();

        return [
            'total_animales' => $totalAnimales,
            'vacunados' => $animalesVacunados,
            'porcentaje' => round(($animalesVacunados / $totalAnimales) * 100, 1),
            'por_tipo_vacuna' => $porTipoVacuna,
        ];
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
