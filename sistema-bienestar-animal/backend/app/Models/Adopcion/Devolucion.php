<?php

namespace App\Models\Adopcion;

use App\Models\Animal\Animal;
use App\Models\User\Usuario;
use App\Models\Veterinaria\Consulta;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Devolucion extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'devoluciones';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'adopcion_id',
        'animal_id',
        'registrado_por',
        'fecha_devolucion',
        'motivo',
        'descripcion_motivo',
        'estado_animal_devolucion',
        'observaciones_estado',
        'consulta_revision_id',
        'revision_veterinaria_completada',
        'fecha_revision_programada',
        'estado_proceso',
    ];

    protected $casts = [
        'fecha_devolucion' => 'date',
        'fecha_revision_programada' => 'date',
        'revision_veterinaria_completada' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Motivos de devolución disponibles.
     */
    const MOTIVOS = [
        'incompatibilidad' => 'Incompatibilidad con el hogar',
        'cambio_situacion' => 'Cambio de situación personal/familiar',
        'problemas_comportamiento' => 'Problemas de comportamiento del animal',
        'enfermedad_animal' => 'Enfermedad del animal',
        'enfermedad_adoptante' => 'Enfermedad del adoptante',
        'mudanza' => 'Mudanza a lugar no apto',
        'economico' => 'Problemas económicos',
        'fallecimiento_adoptante' => 'Fallecimiento del adoptante',
        'alergias' => 'Alergias en el hogar',
        'otro' => 'Otro motivo',
    ];

    /**
     * Estados del animal al momento de devolución.
     */
    const ESTADOS_ANIMAL = [
        'bueno' => 'Bueno',
        'regular' => 'Regular',
        'malo' => 'Malo',
        'critico' => 'Crítico',
    ];

    /**
     * Estados del proceso de devolución.
     */
    const ESTADOS_PROCESO = [
        'recibido' => 'Recibido',
        'en_revision' => 'En revisión veterinaria',
        'aprobado_readopcion' => 'Aprobado para re-adopción',
        'en_tratamiento' => 'En tratamiento',
        'finalizado' => 'Finalizado',
    ];

    /**
     * Relación: Adopción original.
     */
    public function adopcion(): BelongsTo
    {
        return $this->belongsTo(Adopcion::class, 'adopcion_id');
    }

    /**
     * Relación: Animal devuelto.
     */
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    /**
     * Relación: Usuario que registró la devolución.
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'registrado_por');
    }

    /**
     * Relación: Consulta de revisión veterinaria.
     */
    public function consultaRevision(): BelongsTo
    {
        return $this->belongsTo(Consulta::class, 'consulta_revision_id');
    }

    /**
     * Scope: Devoluciones pendientes de revisión veterinaria.
     */
    public function scopePendientesRevision($query)
    {
        return $query->where('revision_veterinaria_completada', false)
                     ->whereIn('estado_proceso', ['recibido', 'en_revision']);
    }

    /**
     * Scope: Devoluciones listas para re-adopción.
     */
    public function scopeListasReadopcion($query)
    {
        return $query->where('revision_veterinaria_completada', true)
                     ->where('estado_proceso', 'aprobado_readopcion');
    }

    /**
     * Scope: Por motivo.
     */
    public function scopePorMotivo($query, string $motivo)
    {
        return $query->where('motivo', $motivo);
    }

    /**
     * Scope: Por estado del proceso.
     */
    public function scopePorEstadoProceso($query, string $estado)
    {
        return $query->where('estado_proceso', $estado);
    }

    /**
     * Scope: Del mes actual.
     */
    public function scopeDelMes($query)
    {
        return $query->whereMonth('fecha_devolucion', now()->month)
                     ->whereYear('fecha_devolucion', now()->year);
    }

    /**
     * Obtener texto legible del motivo.
     */
    public function getMotivoTextoAttribute(): string
    {
        return self::MOTIVOS[$this->motivo] ?? $this->motivo;
    }

    /**
     * Obtener texto legible del estado del animal.
     */
    public function getEstadoAnimalTextoAttribute(): string
    {
        return self::ESTADOS_ANIMAL[$this->estado_animal_devolucion] ?? $this->estado_animal_devolucion;
    }

    /**
     * Obtener texto legible del estado del proceso.
     */
    public function getEstadoProcesoTextoAttribute(): string
    {
        return self::ESTADOS_PROCESO[$this->estado_proceso] ?? $this->estado_proceso;
    }

    /**
     * Verificar si requiere revisión veterinaria.
     */
    public function requiereRevision(): bool
    {
        return !$this->revision_veterinaria_completada;
    }

    /**
     * Verificar si el animal puede ser puesto en adopción.
     */
    public function puedePonerEnAdopcion(): bool
    {
        return $this->revision_veterinaria_completada
            && $this->estado_proceso === 'aprobado_readopcion';
    }
}
