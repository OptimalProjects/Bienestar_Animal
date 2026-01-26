<?php

namespace App\Services;

use App\Models\Administracion\Inventario;
use App\Models\Administracion\Insumo;
use App\Models\Veterinaria\ProductoFarmaceutico;
use App\Models\Veterinaria\Medicamento;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
     * Registrar entrada de inventario.
     */
    public function registrarEntrada(string $tipo, string $id, float $cantidad, array $data = []): void
    {
        DB::transaction(function () use ($tipo, $id, $cantidad, $data) {
            switch ($tipo) {
                case 'inventario':
                    $item = Inventario::findOrFail($id);
                    $item->increment('cantidad_actual', $cantidad);
                    break;
                case 'insumo':
                    $item = Insumo::findOrFail($id);
                    $item->increment('stock_actual', $cantidad);
                    break;
                case 'medicamento':
                    $item = Medicamento::findOrFail($id);
                    $item->increment('stock_actual', $cantidad);
                    break;
                default:
                    throw new \InvalidArgumentException("Tipo de inventario no valido: {$tipo}");
            }

            // Aqui se podria registrar movimiento en tabla de historial
        });
    }

    /**
     * Registrar salida de inventario.
     */
    public function registrarSalida(string $tipo, string $id, float $cantidad, array $data = []): void
    {
        DB::transaction(function () use ($tipo, $id, $cantidad, $data) {
            switch ($tipo) {
                case 'inventario':
                    $item = Inventario::findOrFail($id);
                    if ($item->cantidad_actual < $cantidad) {
                        throw new \InvalidArgumentException('Stock insuficiente');
                    }
                    $item->decrement('cantidad_actual', $cantidad);
                    break;
                case 'insumo':
                    $item = Insumo::findOrFail($id);
                    if ($item->stock_actual < $cantidad) {
                        throw new \InvalidArgumentException('Stock insuficiente');
                    }
                    $item->decrement('stock_actual', $cantidad);
                    break;
                case 'medicamento':
                    $item = Medicamento::findOrFail($id);
                    if ($item->stock_actual < $cantidad) {
                        throw new \InvalidArgumentException('Stock insuficiente');
                    }
                    $item->decrement('stock_actual', $cantidad);
                    break;
                default:
                    throw new \InvalidArgumentException("Tipo de inventario no valido: {$tipo}");
            }
        });
    }

    /**
     * Verificar disponibilidad de stock.
     */
    public function verificarStock(string $tipo, string $id, float $cantidad): bool
    {
        switch ($tipo) {
            case 'inventario':
                $item = Inventario::find($id);
                return $item && $item->cantidad_actual >= $cantidad;
            case 'insumo':
                $item = Insumo::find($id);
                return $item && $item->stock_actual >= $cantidad;
            case 'medicamento':
                $item = Medicamento::find($id);
                return $item && $item->stock_actual >= $cantidad;
            default:
                return false;
        }
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
