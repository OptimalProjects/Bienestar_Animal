<?php

namespace App\Services;

use App\Models\Animal\Animal;
use App\Models\Adopcion\Adopcion;
use App\Models\Denuncia\Denuncia;
use App\Models\Veterinaria\Consulta;
use Carbon\Carbon;

class SciService
{
    /**
     * Obtener KPIs en tiempo real para el SCI.
     */
    public function getKpis(): array
    {
        return [
            ['label' => 'total_animales', 'value' => Animal::count()],
            ['label' => 'adopciones_mes', 'value' => Adopcion::completadas()->delMes()->count()],
            ['label' => 'denuncias_activas', 'value' => Denuncia::pendientes()->count()],
            ['label' => 'consultas_hoy', 'value' => Consulta::deHoy()->count()],
        ];
    }

    /**
     * Obtener resumen de datos para el SCI.
     */
    public function getResumen(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $query = Animal::query();
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('created_at', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay(),
            ]);
        }

        $adopcionesCompletadas = Adopcion::completadas();
        if ($fechaInicio && $fechaFin) {
            $adopcionesCompletadas->whereBetween('created_at', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay(),
            ]);
        }

        $denunciasResueltas = Denuncia::resueltas();
        if ($fechaInicio && $fechaFin) {
            $denunciasResueltas->whereBetween('fecha_resolucion', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay(),
            ]);
        }

        return [
            'total_animales' => Animal::count(),
            'animales_en_refugio' => Animal::enRefugio()->count(),
            'adopciones_completadas' => $adopcionesCompletadas->count(),
            'denuncias_resueltas' => $denunciasResueltas->count(),
        ];
    }

    /**
     * Obtener tendencias comparando con el mes anterior.
     */
    public function getTendencias(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $inicioMesActual = now()->startOfMonth();
        $finMesActual = now()->endOfDay();
        $inicioMesAnterior = now()->subMonth()->startOfMonth();
        $finMesAnterior = now()->subMonth()->endOfMonth();

        // Adopciones
        $adopcionesMesActual = Adopcion::completadas()
            ->whereBetween('created_at', [$inicioMesActual, $finMesActual])
            ->count();

        $adopcionesMesAnterior = Adopcion::completadas()
            ->whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])
            ->count();

        $porcentajeAdopciones = $adopcionesMesAnterior > 0
            ? round((($adopcionesMesActual - $adopcionesMesAnterior) / $adopcionesMesAnterior) * 100)
            : ($adopcionesMesActual > 0 ? 100 : 0);

        // Denuncias nuevas del mes
        $denunciasNuevasMes = Denuncia::where('created_at', '>=', $inicioMesActual)->count();

        // Animales nuevos del mes
        $animalesNuevosMes = Animal::where('created_at', '>=', $inicioMesActual)->count();

        return [
            'adopciones_vs_mes_anterior' => ($porcentajeAdopciones >= 0 ? '+' : '') . $porcentajeAdopciones . '%',
            'denuncias_nuevas_mes' => $denunciasNuevasMes,
            'animales_nuevos_mes' => $animalesNuevosMes,
        ];
    }

    /**
     * Obtener datos detallados para el SCI.
     */
    public function getDetalle(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $inicio = $fechaInicio ? Carbon::parse($fechaInicio)->startOfDay() : now()->startOfMonth();
        $fin = $fechaFin ? Carbon::parse($fechaFin)->endOfDay() : now()->endOfDay();

        return [
            'animales' => [
                'total' => Animal::count(),
                'en_refugio' => Animal::enRefugio()->count(),
                'en_adopcion' => Animal::disponiblesAdopcion()->count(),
                'adoptados' => Animal::adoptados()->count(),
                'en_tratamiento' => Animal::where('estado', 'en_tratamiento')->count(),
                'ingresos_periodo' => Animal::whereBetween('created_at', [$inicio, $fin])->count(),
                'por_especie' => Animal::selectRaw('especie, count(*) as cantidad')
                    ->groupBy('especie')
                    ->pluck('cantidad', 'especie')
                    ->toArray(),
            ],
            'adopciones' => [
                'total' => Adopcion::count(),
                'completadas' => Adopcion::completadas()->count(),
                'pendientes' => Adopcion::pendientes()->count(),
                'en_evaluacion' => Adopcion::enEvaluacion()->count(),
                'del_periodo' => Adopcion::whereBetween('created_at', [$inicio, $fin])->count(),
                'por_estado' => Adopcion::selectRaw('estado, count(*) as cantidad')
                    ->groupBy('estado')
                    ->pluck('cantidad', 'estado')
                    ->toArray(),
            ],
            'denuncias' => [
                'total' => Denuncia::count(),
                'activas' => Denuncia::pendientes()->count(),
                'resueltas' => Denuncia::resueltas()->count(),
                'del_periodo' => Denuncia::whereBetween('created_at', [$inicio, $fin])->count(),
                'por_tipo' => Denuncia::selectRaw('tipo_denuncia, count(*) as cantidad')
                    ->groupBy('tipo_denuncia')
                    ->pluck('cantidad', 'tipo_denuncia')
                    ->toArray(),
                'por_prioridad' => Denuncia::selectRaw('prioridad, count(*) as cantidad')
                    ->groupBy('prioridad')
                    ->pluck('cantidad', 'prioridad')
                    ->toArray(),
            ],
            'veterinaria' => [
                'consultas_hoy' => Consulta::deHoy()->count(),
                'consultas_periodo' => Consulta::whereBetween('fecha_consulta', [$inicio, $fin])->count(),
            ],
        ];
    }
}
