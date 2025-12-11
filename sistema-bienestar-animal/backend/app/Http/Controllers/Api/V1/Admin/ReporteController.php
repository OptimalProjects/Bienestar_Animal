<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReporteController extends BaseController
{
    public function __construct(
        protected ReporteService $reporteService
    ) {}

    /**
     * Obtener dashboard principal.
     * GET /api/v1/reportes/dashboard
     */
    public function dashboard()
    {
        try {
            $data = $this->reporteService->getDashboard();
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Obtener indicadores KPI.
     * GET /api/v1/reportes/indicadores
     */
    public function indicadores()
    {
        try {
            $indicadores = $this->reporteService->getIndicadores();
            return $this->successResponse($indicadores);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener indicadores');
        }
    }

    /**
     * Registrar valor de indicador.
     * POST /api/v1/reportes/indicadores/{indicadorId}/punto
     */
    public function registrarIndicador(Request $request, string $indicadorId)
    {
        $validator = Validator::make($request->all(), [
            'valor' => 'required|numeric',
            'dimensiones' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $punto = $this->reporteService->registrarIndicador(
                $indicadorId,
                $request->valor,
                $request->dimensiones
            );

            return $this->createdResponse($punto, 'Indicador registrado exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al registrar indicador');
        }
    }

    /**
     * Generar reporte de periodo.
     * GET /api/v1/reportes/periodo
     */
    public function reportePeriodo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $reporte = $this->reporteService->generarReportePeriodo(
                $request->fecha_inicio,
                $request->fecha_fin
            );

            return $this->successResponse($reporte);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al generar reporte');
        }
    }

    /**
     * Exportar reporte en formato especifico.
     * GET /api/v1/reportes/exportar
     */
    public function exportar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|in:animales,adopciones,denuncias,veterinaria,general',
            'formato' => 'required|in:pdf,excel,csv',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Por ahora retornamos los datos en JSON
            // La generacion de PDF/Excel se implementaria con paquetes como DomPDF o Maatwebsite\Excel
            $reporte = $this->reporteService->generarReportePeriodo(
                $request->fecha_inicio ?? now()->startOfMonth()->toDateString(),
                $request->fecha_fin ?? now()->toDateString()
            );

            return $this->successResponse([
                'tipo' => $request->tipo,
                'formato' => $request->formato,
                'datos' => $reporte,
                'mensaje' => 'Exportacion de ' . $request->formato . ' pendiente de implementacion',
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al exportar reporte');
        }
    }
}
