<?php

namespace App\Models\Denuncia;

use App\Models\Animal\Animal;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rescate extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'rescates';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'denuncia_id',
        'fecha_programada',
        'fecha_ejecucion',
        'equipo_rescate',
        'animal_rescatado_id',
        'exitoso',
        'observaciones',
        'motivo_fallo',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_ejecucion' => 'datetime',
        'equipo_rescate' => 'array',
        'exitoso' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacion: Denuncia asociada.
     */
    public function denuncia(): BelongsTo
    {
        return $this->belongsTo(Denuncia::class, 'denuncia_id');
    }

    /**
     * Relacion: Animal rescatado.
     */
    public function animalRescatado(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'animal_rescatado_id');
    }

    /**
     * Scope: Rescates exitosos.
     */
    public function scopeExitosos($query)
    {
        return $query->where('exitoso', true);
    }

    /**
     * Scope: Rescates fallidos.
     */
    public function scopeFallidos($query)
    {
        return $query->where('exitoso', false)
                     ->whereNotNull('fecha_ejecucion');
    }

    /**
     * Scope: Rescates programados (pendientes).
     */
    public function scopeProgramados($query)
    {
        return $query->whereNull('fecha_ejecucion')
                     ->where('fecha_programada', '>=', today());
    }

    /**
     * Scope: Rescates de hoy.
     */
    public function scopeDeHoy($query)
    {
        return $query->whereDate('fecha_programada', today());
    }

    /**
     * Scope: Rescates ejecutados.
     */
    public function scopeEjecutados($query)
    {
        return $query->whereNotNull('fecha_ejecucion');
    }

    /**
     * Scope: Con animal rescatado.
     */
    public function scopeConAnimal($query)
    {
        return $query->whereNotNull('animal_rescatado_id');
    }

    /**
     * Verificar si esta pendiente de ejecucion.
     */
    public function estaPendiente(): bool
    {
        return is_null($this->fecha_ejecucion);
    }

    /**
     * Verificar si fue exitoso.
     */
    public function fueExitoso(): bool
    {
        return $this->exitoso === true;
    }
}
