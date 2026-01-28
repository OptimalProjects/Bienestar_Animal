<?php

namespace App\Services;

use App\Models\Administracion\Inventario;
use App\Models\Administracion\Insumo;
use App\Models\Veterinaria\MovimientoInventario;
use App\Models\Veterinaria\ProductoFarmaceutico;
use App\Models\Veterinaria\Medicamento;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventarioService
{
    /**
     * Listar inventario con paginacion.
     */
    public function listarInventario(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Inventario::query();

        if (!empty($filters['categoria'])) {
            $query->porCategoria($filters['categoria']);
        }

        if (!empty($filters['tipo'])) {
            $query->porTipo($filters['tipo']);
        }

        if (!empty($filters['cantidad_baja'])) {
            $query->cantidadBaja();
        }

        if (!empty($filters['busqueda'])) {
            $busqueda = $filters['busqueda'];
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('codigo', 'like', "%{$busqueda}%");
            });
        }

        return $query->orderBy('nombre')->paginate($perPage);
    }

    /**
     * Listar insumos con paginacion.
     * Devuelve inventarios.
     */
    public function listarInsumos(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Inventario::query();

        if (!empty($filters['categoria'])) {
            $query->porCategoria($filters['categoria']);
        }

        if (!empty($filters['tipo'])) {
            $query->porTipo($filters['tipo']);
        }

        if (!empty($filters['stock_bajo'])) {
            $query->cantidadBaja();
        }

        if (!empty($filters['proximos_vencer'])) {
            $dias = $filters['dias_vencimiento'] ?? 30;
            $query->whereNotNull('fecha_vencimiento')
                  ->whereBetween('fecha_vencimiento', [now(), now()->addDays($dias)]);
        }

        return $query->orderBy('nombre')->paginate($perPage);
    }

    /**
     * Registrar entrada de inventario con historial.
     * 
     * @param string $tipo Tipo de inventario (inventario, insumo, medicamento)
     * @param string $id ID del item
     * @param float $cantidad Cantidad a ingresar
     * @param array $data Datos adicionales (motivo, descripcion)
     * @return void
     * @throws \Exception
     */
    public function registrarEntrada(string $tipo, string $id, float $cantidad, array $data = []): void
    {
        DB::beginTransaction();
        
        try {
            $item = $this->obtenerItem($tipo, $id);
            
            if (!$item) {
                throw new \Exception('Item no encontrado');
            }
            
            // Actualizar cantidad
            $campoStock = $this->getCampoStock($tipo);
            $item->increment($campoStock, $cantidad);
            
            // Registrar movimiento en historial
            MovimientoInventario::create([
                'medicamento_id' => $tipo === 'inventario' || $tipo === 'insumo' || $tipo === 'medicamento' ? $id : null,
                'tipo_movimiento' => 'entrada',
                'cantidad' => $cantidad,
                'motivo' => $data['motivo'] ?? 'Ingreso al inventario',
                'descripcion' => $data['descripcion'] ?? null,
                'usuario_id' => auth()->id(),
            ]);
            
            Log::info('Entrada de inventario registrada', [
                'tipo' => $tipo,
                'item_id' => $id,
                'cantidad' => $cantidad,
            ]);
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al registrar entrada de inventario', [
                'tipo' => $tipo,
                'item_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Registrar salida de inventario con historial.
     * 
     * @param string $tipo Tipo de inventario (inventario, insumo, medicamento)
     * @param string $id ID del item
     * @param float $cantidad Cantidad a sacar
     * @param array $data Datos adicionales (motivo, descripcion, consulta_id)
     * @return void
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function registrarSalida(string $tipo, string $id, float $cantidad, array $data = []): void
    {
        DB::beginTransaction();
        
        try {
            $item = $this->obtenerItem($tipo, $id);
            
            if (!$item) {
                throw new \InvalidArgumentException('Item no encontrado');
            }
            
            $campoStock = $this->getCampoStock($tipo);
            $stockActual = $item->$campoStock;
            
            // Verificar stock disponible
            if ($stockActual < $cantidad) {
                throw new \InvalidArgumentException(
                    "Stock insuficiente. Disponible: {$stockActual}, Requerido: {$cantidad}"
                );
            }
            
            // Actualizar cantidad
            $item->decrement($campoStock, $cantidad);
            
            // Registrar movimiento en historial
            MovimientoInventario::create([
                'medicamento_id' => $tipo === 'inventario' || $tipo === 'insumo' || $tipo === 'medicamento' ? $id : null,
                'consulta_id' => $data['consulta_id'] ?? null,
                'tipo_movimiento' => 'salida',
                'cantidad' => $cantidad,
                'motivo' => $data['motivo'] ?? 'Uso en consulta veterinaria',
                'descripcion' => $data['descripcion'] ?? null,
                'usuario_id' => auth()->id(),
            ]);
            
            Log::info('Salida de inventario registrada', [
                'tipo' => $tipo,
                'item_id' => $id,
                'cantidad' => $cantidad,
                'consulta_id' => $data['consulta_id'] ?? null,
            ]);
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al registrar salida de inventario', [
                'tipo' => $tipo,
                'item_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Obtener item de inventario según tipo.
     * 
     * @param string $tipo
     * @param string $id
     * @return mixed
     */
    private function obtenerItem(string $tipo, string $id)
    {
        return match($tipo) {
            'inventario' => Inventario::find($id),
            'insumo' => Insumo::find($id),
            'medicamento' => Medicamento::find($id),
            default => null,
        };
    }

    /**
     * Obtener nombre del campo de stock según tipo.
     * 
     * @param string $tipo
     * @return string
     */
    private function getCampoStock(string $tipo): string
    {
        return match($tipo) {
            'inventario' => 'cantidad_actual',
            'insumo' => 'stock_actual',
            'medicamento' => 'stock_actual',
            default => 'cantidad_actual',
        };
    }

    /**
     * Verificar disponibilidad de stock.
     */
    public function verificarStock(string $tipo, string $id, float $cantidad): bool
    {
        $item = $this->obtenerItem($tipo, $id);
        
        if (!$item) {
            return false;
        }
        
        $campoStock = $this->getCampoStock($tipo);
        
        return $item->$campoStock >= $cantidad;
    }

    /**
     * Obtener items con stock bajo.
     */
    public function getStockBajo(): array
    {
        return [
            'inventario' => Inventario::cantidadBaja()->get(),
            'insumos' => Insumo::activos()->stockBajo()->get(),
            'productos_farmaceuticos' => ProductoFarmaceutico::activos()->stockBajo()->get(),
        ];
    }

    /**
     * Obtener items proximos a vencer.
     */
    public function getProximosVencer(int $dias = 30): array
    {
        return [
            'insumos' => Insumo::activos()->proximosAVencer($dias)->get(),
            'productos_farmaceuticos' => ProductoFarmaceutico::activos()->proximosAVencer($dias)->get(),
        ];
    }

    /**
     * Obtener items vencidos.
     */
    public function getVencidos(): array
    {
        return [
            'insumos' => Insumo::vencidos()->get(),
            'productos_farmaceuticos' => ProductoFarmaceutico::vencidos()->get(),
        ];
    }

    /**
     * Obtener estadisticas de inventario.
     */
    public function getEstadisticas(): array
    {
        return [
            'inventario' => [
                'total_items' => Inventario::count(),
                'items_stock_bajo' => Inventario::cantidadBaja()->count(),
                'items_sin_stock' => Inventario::sinExistencias()->count(),
            ],
            'insumos' => [
                'total_items' => Insumo::activos()->count(),
                'items_stock_bajo' => Insumo::activos()->stockBajo()->count(),
                'items_sin_stock' => Insumo::activos()->sinStock()->count(),
                'proximos_vencer' => Insumo::activos()->proximosAVencer(30)->count(),
                'vencidos' => Insumo::vencidos()->count(),
            ],
            'medicamentos' => [
                'total_items' => Medicamento::activos()->count(),
                'items_stock_bajo' => Medicamento::activos()->stockBajo()->count(),
            ],
        ];
    }

    /**
     * Crear item de inventario.
     */
    public function crearInventario(array $data): Inventario
    {
        return Inventario::create($data);
    }

    /**
     * Actualizar item de inventario.
     */
    public function actualizarInventario(string $id, array $data): Inventario
    {
        $item = Inventario::findOrFail($id);
        $item->update($data);
        return $item->fresh();
    }

    /**
     * Crear insumo.
     */
    public function crearInsumo(array $data): Insumo
    {
        return Insumo::create($data);
    }

    /**
     * Actualizar insumo.
     */
    public function actualizarInsumo(string $id, array $data): Insumo
    {
        $item = Insumo::findOrFail($id);
        $item->update($data);
        return $item->fresh();
    }

    /**
     * Eliminar inventario.
     */
    public function eliminarInventario(string $id): void
    {
        $item = Inventario::findOrFail($id);
        $item->delete();
    }
}