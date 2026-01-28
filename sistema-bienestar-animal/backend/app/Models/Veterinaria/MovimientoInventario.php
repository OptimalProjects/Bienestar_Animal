<?php

namespace App\Models\Veterinaria;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'medicamento_id',
        'consulta_id',
        'tipo_movimiento',
        'cantidad',
        'motivo',
        'descripcion',
        'usuario_id',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el medicamento (inventario)
     */
    public function medicamento()
    {
        return $this->belongsTo(Inventario::class, 'medicamento_id');
    }

    /**
     * Relación con la consulta
     */
    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    /**
     * Relación con el usuario que realizó el movimiento
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Scope para filtrar por tipo de movimiento
     */
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo_movimiento', $tipo);
    }

    /**
     * Scope para filtrar por medicamento
     */
    public function scopeMedicamento($query, $medicamentoId)
    {
        return $query->where('medicamento_id', $medicamentoId);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('created_at', [$desde, $hasta]);
    }
}