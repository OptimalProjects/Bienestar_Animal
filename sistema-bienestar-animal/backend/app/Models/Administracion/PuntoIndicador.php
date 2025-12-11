<?php

namespace App\Models\Administracion;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PuntoIndicador extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'puntos_indicadores';
    protected $keyType = 'string';
    public $incrementing = false;

    // Solo timestamp de creacion, no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'indicador_id',
        'fecha',
        'valor',
        'dimensiones',
        'calidad',
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:4',
        'dimensiones' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relacion: Indicador al que pertenece.
     */
    public function indicador(): BelongsTo
    {
        return $this->belongsTo(Indicador::class, 'indicador_id');
    }

    /**
     * Scope: Por fecha.
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Scope: Por rango de fechas.
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope: Del mes actual.
     */
    public function scopeDelMes($query)
    {
        return $query->whereMonth('fecha', now()->month)
                     ->whereYear('fecha', now()->year);
    }

    /**
     * Scope: Del anio actual.
     */
    public function scopeDelAnio($query)
    {
        return $query->whereYear('fecha', now()->year);
    }
}
