<?php

namespace App\Models\Veterinaria;

use App\Models\Animal\HistorialClinico;
use App\Models\Administracion\Inventario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tratamiento extends Model
{
    use HasUuids;

    protected $table = 'tratamientos';

    protected $fillable = [
        'historial_clinico_id',
        'consulta_id',
        'tipo_tratamiento',
        'descripcion',
        'objetivo',
        'fecha_inicio',
        'fecha_fin',
        'duracion_estimada',
        'estado',
        'efectividad',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'duracion_estimada' => 'integer',
    ];

    /**
     * Relación con HistorialClinico
     */
    public function historialClinico(): BelongsTo
    {
        return $this->belongsTo(HistorialClinico::class, 'historial_clinico_id');
    }

    /**
     * Relación con Consulta
     */
    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    /**
     * NOTA: Esta tabla NO tiene relación directa con medicamentos.
     * Los medicamentos se referencian a través de MovimientoInventario
     * usando la consulta_id como vínculo.
     */

    /**
     * Scope para tratamientos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para tratamientos completados
     */
    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    /**
     * Scope para tratamientos cancelados
     */
    public function scopeCancelados($query)
    {
        return $query->where('estado', 'cancelado');
    }

    /**
     * Scope para tratamientos de un tipo específico
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_tratamiento', $tipo);
    }

    /**
     * Scope para tratamientos de una consulta específica
     */
    public function scopeDeConsulta($query, $consultaId)
    {
        return $query->where('consulta_id', $consultaId);
    }

    /**
     * Verificar si el tratamiento está activo
     */
    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * Verificar si el tratamiento está completado
     */
    public function estaCompletado(): bool
    {
        return $this->estado === 'completado';
    }

    /**
     * Marcar tratamiento como completado
     */
    public function marcarComoCompletado(): bool
    {
        $this->estado = 'completado';
        $this->fecha_fin = now();
        return $this->save();
    }

    /**
     * Marcar tratamiento como cancelado
     */
    public function marcarComoCancelado(string $observacion = null): bool
    {
        $this->estado = 'cancelado';
        $this->fecha_fin = now();
        
        if ($observacion) {
            $this->observaciones = $this->observaciones 
                ? $this->observaciones . "\n\nCancelación: " . $observacion
                : "Cancelación: " . $observacion;
        }
        
        return $this->save();
    }

    /**
     * Obtener descripción completa del tratamiento
     */
    public function getDescripcionCompletaAttribute(): string
    {
        $partes = [];
        
        if ($this->medicamento) {
            $partes[] = $this->medicamento->nombre;
        }
        
        if ($this->dosis) {
            $partes[] = "Dosis: {$this->dosis}";
        }
        
        if ($this->frecuencia) {
            $partes[] = "Frecuencia: {$this->frecuencia}";
        }
        
        if ($this->duracion_dias) {
            $partes[] = "Duración: {$this->duracion_dias} días";
        }
        
        return implode(' | ', $partes);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Al crear un tratamiento, establecer fecha_inicio si no existe
        static::creating(function ($tratamiento) {
            if (!$tratamiento->fecha_inicio) {
                $tratamiento->fecha_inicio = now();
            }
            
            // Si no tiene estado, establecer como activo
            if (!$tratamiento->estado) {
                $tratamiento->estado = 'activo';
            }
        });
    }
}