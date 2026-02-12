<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SciService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SciController extends Controller
{
    public function __construct(
        protected SciService $sciService
    ) {}

    /**
     * Retorna los KPIs en tiempo real para el SCI.
     *
     * GET /api/sci/kpis
     */
    public function kpis(): JsonResponse
    {
        try {
            $kpis = $this->sciService->getKpis();

            return response()->json([
                'app_id' => config('sci.app_id'),
                'timestamp' => now()->toIso8601String(),
                'kpis' => $kpis,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'app_id' => config('sci.app_id'),
                'error' => 'Error al obtener KPIs: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Retorna datos y estadisticas para el SCI.
     *
     * GET /api/sci/data
     * Query params: tipo (resumen|detalle), fecha_inicio, fecha_fin
     */
    public function data(Request $request): JsonResponse
    {
        try {
            $tipo = $request->query('tipo', 'resumen');
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin = $request->query('fecha_fin');

            $data = [
                'resumen' => $this->sciService->getResumen($fechaInicio, $fechaFin),
                'tendencias' => $this->sciService->getTendencias($fechaInicio, $fechaFin),
            ];

            if ($tipo === 'detalle') {
                $data['detalle'] = $this->sciService->getDetalle($fechaInicio, $fechaFin);
            }

            return response()->json([
                'app_id' => config('sci.app_id'),
                'timestamp' => now()->toIso8601String(),
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'app_id' => config('sci.app_id'),
                'error' => 'Error al obtener datos: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }
}
