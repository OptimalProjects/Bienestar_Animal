<?php

namespace App\Models\Administracion;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'inventarios';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'tipo',
        'unidad_medida',
        'cantidad_actual',
        'cantidad_minima',
        'ubicacion',
        'fecha_vencimiento',
        'proveedor',
    ];

    protected $casts = [
        'cantidad_actual' => 'decimal:2',
        'cantidad_minima' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Verificar si tiene cantidad baja.
     */
    public function tieneCantidadBaja(): bool
    {
        return $this->cantidad_actual <= $this->cantidad_minima;
    }

    /**
     * Scope: Con cantidad baja.
     */
    public function scopeCantidadBaja($query)
    {
        return $query->whereColumn('cantidad_actual', '<=', 'cantidad_minima');
    }

    /**
     * Scope: Sin existencias.
     */
    public function scopeSinExistencias($query)
    {
        return $query->where('cantidad_actual', 0);
    }

    /**
     * Scope: Por categoria.
     */
    public function scopePorCategoria($query, string $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope: Por tipo.
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope: Por proveedor.
     */
    public function scopePorProveedor($query, string $proveedor)
    {
        return $query->where('proveedor', $proveedor);
    }
}
