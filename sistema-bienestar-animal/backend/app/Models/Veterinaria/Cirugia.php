<?php

namespace App\Models\Veterinaria;

use App\Models\Animal\HistorialClinico;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cirugia extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'cirugias';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'historial_clinico_id',
        'tipo_cirugia',
        'descripcion',
        'fecha_programada',
        'fecha_realizacion',
        'cirujano_id',
        'anestesiologo_id',
        'asistentes',
        'duracion',
        'tipo_anestesia',
        'complicaciones',
        'resultado',
        'postoperatorio',
        'seguimiento_requerido',
        'estado',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_realizacion' => 'datetime',
        'asistentes' => 'array',
        'duracion' => 'integer',
        'seguimiento_requerido' => 'boolean',
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
     * Relacion: Cirujano.
     */
    public function cirujano(): BelongsTo
    {
        return $this->belongsTo(Veterinario::class, 'cirujano_id');
    }

    /**
     * Relacion: Anestesiologo.
     */
    public function anestesiologo(): BelongsTo
    {
        return $this->belongsTo(Veterinario::class, 'anestesiologo_id');
    }

    /**
     * Scope: Cirugias programadas.
     */
    public function scopeProgramadas($query)
    {
        return $query->where('estado', 'programada');
    }

    /**
     * Scope: Cirugias realizadas.
     */
    public function scopeRealizadas($query)
    {
        return $query->where('estado', 'realizada');
    }

    /**
     * Scope: Cirugias de hoy.
     */
    public function scopeDeHoy($query)
    {
        return $query->whereDate('fecha_programada', today());
    }

    /**
     * Scope: Cirugias pendientes (programadas para el futuro).
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'programada')
                     ->where('fecha_programada', '>=', today());
    }

    /**
     * Scope: Que requieren seguimiento.
     */
    public function scopeRequierenSeguimiento($query)
    {
        return $query->where('seguimiento_requerido', true)
                     ->where('estado', 'realizada');
    }

    /**
     * Scope: Por tipo de cirugia.
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_cirugia', $tipo);
    }
}
