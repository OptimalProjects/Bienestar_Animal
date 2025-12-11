<?php

namespace App\Models\Administracion;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'insumos';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'unidad_medida',
        'stock_actual',
        'stock_minimo',
        'ubicacion',
        'fecha_vencimiento',
        'activo',
    ];

    protected $casts = [
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Verificar si tiene stock bajo.
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
     * Scope: Activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Con stock bajo.
     */
    public function scopeStockBajo($query)
    {
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo');
    }

    /**
     * Scope: Sin stock.
     */
    public function scopeSinStock($query)
    {
        return $query->where('stock_actual', 0);
    }

    /**
     * Scope: Por categoria.
     */
    public function scopePorCategoria($query, string $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope: Proximos a vencer.
     */
    public function scopeProximosAVencer($query, int $dias = 30)
    {
        return $query->whereBetween('fecha_vencimiento', [now(), now()->addDays($dias)]);
    }

    /**
     * Scope: Vencidos.
     */
    public function scopeVencidos($query)
    {
        return $query->where('fecha_vencimiento', '<', now());
    }
}
