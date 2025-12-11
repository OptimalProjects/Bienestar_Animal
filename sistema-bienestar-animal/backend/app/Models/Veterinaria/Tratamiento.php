<?php

namespace App\Models\Veterinaria;

use App\Models\Animal\HistorialClinico;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tratamiento extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'tratamientos';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'consulta_id',
        'historial_clinico_id',
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
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'duracion_estimada' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacion: Consulta donde se prescribio.
     */
    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    /**
     * Relacion: Historial clinico.
     */
    public function historialClinico(): BelongsTo
    {
        return $this->belongsTo(HistorialClinico::class, 'historial_clinico_id');
    }

    /**
     * Relacion: Medicamentos del tratamiento.
     */
    public function medicamentos(): HasMany
    {
        return $this->hasMany(Medicamento::class, 'tratamiento_id');
    }

    /**
     * Relacion: Procedimientos del tratamiento.
     */
    public function procedimientos(): HasMany
    {
        return $this->hasMany(Procedimiento::class, 'tratamiento_id');
    }

    /**
     * Scope: Tratamientos activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope: Tratamientos completados.
     */
    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    /**
     * Scope: Por tipo de tratamiento.
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_tratamiento', $tipo);
    }

    /**
     * Scope: Tratamientos en curso (fecha actual entre inicio y fin).
     */
    public function scopeEnCurso($query)
    {
        return $query->where('estado', 'activo')
                     ->where('fecha_inicio', '<=', now())
                     ->where(function($q) {
                         $q->whereNull('fecha_fin')
                           ->orWhere('fecha_fin', '>=', now());
                     });
    }
}
