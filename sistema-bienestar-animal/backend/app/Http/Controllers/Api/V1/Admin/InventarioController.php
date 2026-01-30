<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class InventarioController extends BaseController
{
    public function __construct(
        protected InventarioService $inventarioService
    ) {}

    /**
     * Listar inventario.
     * GET /api/v1/inventario
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'categoria',
                'tipo',
                'cantidad_baja',
                'busqueda',
            ]);

            $inventario = $this->inventarioService->listarInventario(
                $request->get('per_page', 15),
                $filters
            );

            return $this->successResponse($inventario);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar inventario');
        }
    }

    /**
     * Listar insumos.
     * GET /api/v1/inventario/insumos
     */
    public function insumos(Request $request)
    {
        try {
            $filters = $request->only([
                'categoria',
                'stock_bajo',
                'proximos_vencer',
                'dias_vencimiento',
            ]);

            $insumos = $this->inventarioService->listarInsumos(
                $request->get('per_page', 15),
                $filters
            );

            return $this->successResponse($insumos);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar insumos');
        }
    }

    /**
     * Crear item de inventario.
     * POST /api/v1/inventario
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:50|unique:inventarios,codigo',
            'nombre' => 'required|string|max:200',
            'categoria' => 'required|string|max:100',
            'tipo' => 'nullable|string|max:100',
            'unidad_medida' => 'required|string|max:50',
            'cantidad_actual' => 'required|numeric|min:0',
            'cantidad_minima' => 'nullable|numeric|min:0',
            'ubicacion' => 'nullable|string|max:200',
            'fecha_vencimiento' => 'nullable|date',
            'proveedor' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $item = $this->inventarioService->crearInventario($request->all());
            return $this->createdResponse($item, 'Item de inventario creado exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al crear item: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar item de inventario.
     * PUT /api/v1/inventario/{id}
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|string|max:200',
            'categoria' => 'nullable|string|max:100',
            'tipo' => 'nullable|string|max:100',
            'unidad_medida' => 'nullable|string|max:50',
            'cantidad_minima' => 'nullable|numeric|min:0',
            'ubicacion' => 'nullable|string|max:200',
            'fecha_vencimiento' => 'nullable|date',
            'proveedor' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $item = $this->inventarioService->actualizarInventario($id, $request->all());
            return $this->successResponse($item, 'Item actualizado exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al actualizar item');
        }
    }

    /**
     * Eliminar item de inventario.
     * DELETE /api/v1/inventario/{id}
     */
    public function destroy(string $id)
    {
        try {
            $this->inventarioService->eliminarInventario($id);
            return $this->successResponse(null, 'Item eliminado exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al eliminar item');
        }
    }

    /**
     * Registrar entrada de inventario.
     * POST /api/v1/inventario/{id}/entrada
     */
    public function registrarEntrada(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|in:inventario,insumo,medicamento',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $this->inventarioService->registrarEntrada(
                $request->tipo,
                $id,
                $request->cantidad,
                ['motivo' => $request->motivo]
            );

            return $this->successResponse(null, 'Entrada registrada exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al registrar entrada: ' . $e->getMessage());
        }
    }

    /**
     * Registrar salida de inventario.
     * POST /api/v1/inventario/{id}/salida
     */
    public function registrarSalida(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|in:inventario,insumo,medicamento',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $this->inventarioService->registrarSalida(
                $request->tipo,
                $id,
                $request->cantidad,
                ['motivo' => $request->motivo]
            );

            return $this->successResponse(null, 'Salida registrada exitosamente');
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al registrar salida');
        }
    }

    /**
     * Verificar disponibilidad de stock.
     * GET /api/v1/inventario/verificar-stock
     */
    public function verificarStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|in:inventario,insumo,medicamento',
            'id' => 'required|string',
            'cantidad' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $disponible = $this->inventarioService->verificarStock(
                $request->tipo,
                $request->id,
                $request->cantidad
            );

            return $this->successResponse([
                'disponible' => $disponible,
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al verificar stock');
        }
    }

    /**
     * Obtener items con stock bajo.
     * GET /api/v1/inventario/stock-bajo
     */
    public function stockBajo()
    {
        try {
            $items = $this->inventarioService->getStockBajo();
            return $this->successResponse($items);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener items con stock bajo');
        }
    }

    /**
     * Obtener items proximos a vencer.
     * GET /api/v1/inventario/proximos-vencer
     */
    public function proximosVencer(Request $request)
    {
        try {
            $dias = $request->get('dias', 30);
            $items = $this->inventarioService->getProximosVencer($dias);

            return $this->successResponse($items);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener items proximos a vencer');
        }
    }

    /**
     * Obtener items vencidos.
     * GET /api/v1/inventario/vencidos
     */
    public function vencidos()
    {
        try {
            $items = $this->inventarioService->getVencidos();
            return $this->successResponse($items);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener items vencidos');
        }
    }

    /**
     * Estadisticas de inventario.
     * GET /api/v1/inventario/estadisticas
     */
    public function estadisticas()
    {
        try {
            $stats = $this->inventarioService->getEstadisticas();
            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener estadisticas');
        }
    }

    /**
     * Obtener historial de movimientos de inventario.
     * GET /api/v1/inventario/movimientos
     */
    public function movimientos(Request $request)
    {
        try {
            Log::info('📊 Solicitando movimientos de inventario', [
                'params' => $request->all()
            ]);

            $query = \App\Models\Veterinaria\MovimientoInventario::with([
                'medicamento',
                'consulta.historialClinico.animal',
                'consulta.veterinario'
            ])
            ->orderBy('created_at', 'desc');

            // Filtros opcionales
            if ($request->has('medicamento_id')) {
                $query->where('medicamento_id', $request->medicamento_id);
            }

            if ($request->has('tipo')) {
                $query->where('tipo_movimiento', $request->tipo);
            }

            if ($request->has('fecha_desde')) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }

            $perPage = $request->get('per_page', 50);
            
            // Si per_page es muy alto o -1, obtener todos
            if ($perPage > 100 || $perPage < 0) {
                $movimientos = $query->get();
                
                Log::info('✅ Movimientos obtenidos (sin paginar)', [
                    'total' => $movimientos->count()
                ]);

                return $this->successResponse($movimientos);
            }

            $movimientos = $query->paginate($perPage);

            Log::info('✅ Movimientos obtenidos (paginados)', [
                'total' => $movimientos->total(),
                'per_page' => $movimientos->perPage(),
                'current_page' => $movimientos->currentPage()
            ]);

            return $this->successResponse($movimientos);
        } catch (\Exception $e) {
            Log::error('❌ Error al obtener movimientos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->serverErrorResponse('Error al obtener movimientos: ' . $e->getMessage());
        }
    }
}