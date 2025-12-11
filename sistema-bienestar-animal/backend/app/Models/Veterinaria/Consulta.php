<?php

namespace App\Models\Veterinaria;

use App\Models\Animal\HistorialClinico;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consulta extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'consultas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'historial_clinico_id',
        'veterinario_id',
        'fecha_consulta',
        'motivo',
        'sintomas',
        'signos_vitales',
        'temperatura',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'peso',
        'diagnostico',
        'diagnostico_diferencial',
        'plan_diagnostico',
        'observaciones',
        'recomendaciones',
        'proxima_cita',
        'tipo_consulta',
    ];

    protected $casts = [
        'fecha_consulta' => 'datetime',
        'signos_vitales' => 'array',
        'temperatura' => 'decimal:2',
        'frecuencia_cardiaca' => 'integer',
        'frecuencia_respiratoria' => 'integer',
        'peso' => 'decimal:2',
        'proxima_cita' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacion: Historial clinico.
     */
    public function historialClinico(): BelongsTo
    {
        return $this->belongsTo(HistorialClinico::class, 'historial_clinico_id');
    }

    /**
     * Relacion: Veterinario que realizo la consulta.
     */
    public function veterinario(): BelongsTo
    {
        return $this->belongsTo(Veterinario::class, 'veterinario_id');
    }

    /**
     * Relacion: Tratamientos prescritos en esta consulta.
     */
    public function tratamientos(): HasMany
    {
        return $this->hasMany(Tratamiento::class, 'consulta_id');
    }

    /**
     * Relacion: Examenes de laboratorio solicitados.
     */
    public function examenesLaboratorio(): HasMany
    {
        return $this->hasMany(ExamenLaboratorio::class, 'consulta_id');
    }

    /**
     * Scope: Por tipo de consulta.
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_consulta', $tipo);
    }

    /**
     * Scope: Consultas de hoy.
     */
    public function scopeDeHoy($query)
    {
        return $query->whereDate('fecha_consulta', today());
    }

    /**
     * Scope: Consultas con proxima cita.
     */
    public function scopeConProximaCita($query)
    {
        return $query->whereNotNull('proxima_cita');
    }

    /**
     * Scope: Emergencias.
     */
    public function scopeEmergencias($query)
    {
        return $query->where('tipo_consulta', 'emergencia');
    }
}
