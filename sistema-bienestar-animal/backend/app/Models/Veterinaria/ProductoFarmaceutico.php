<?php

namespace App\Models\Veterinaria;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductoFarmaceutico extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'productos_farmaceuticos';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'codigo',
        'nombre_comercial',
        'nombre_generico',
        'principio_activo',
        'presentacion',
        'concentracion',
        'laboratorio',
        'registro_sanitario',
        'fecha_vencimiento',
        'stock_actual',
        'stock_minimo',
        'precio_unitario',
        'requiere_receta',
        'activo',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
        'precio_unitario' => 'decimal:2',
        'requiere_receta' => 'boolean',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacion: Medicamentos que usan este producto.
     */
    public function medicamentos(): HasMany
    {
        return $this->hasMany(Medicamento::class, 'producto_id');
    }

    /**
     * Verificar si hay stock bajo.
     */
    public function tieneStockBajo(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    /**
     * Verificar si esta vencido.
     */
    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->isPast();
    }

    /**
     * Verificar si esta proximo a vencer (30 dias).
     */
    public function proximoAVencer(): bool
    {
        return $this->fecha_vencimiento &&
               $this->fecha_vencimiento->isBetween(now(), now()->addDays(30));
    }

    /**
     * Scope: Productos activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Productos con stock bajo.
     */
    public function scopeStockBajo($query)
    {
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo');
    }

    /**
     * Scope: Productos sin stock.
     */
    public function scopeSinStock($query)
    {
        return $query->where('stock_actual', 0);
    }

    /**
     * Scope: Productos proximos a vencer.
     */
    public function scopeProximosAVencer($query, int $dias = 30)
    {
        return $query->whereBetween('fecha_vencimiento', [now(), now()->addDays($dias)]);
    }

    /**
     * Scope: Productos vencidos.
     */
    public function scopeVencidos($query)
    {
        return $query->where('fecha_vencimiento', '<', now());
    }

    /**
     * Scope: Que requieren receta.
     */
    public function scopeConReceta($query)
    {
        return $query->where('requiere_receta', true);
    }
}
