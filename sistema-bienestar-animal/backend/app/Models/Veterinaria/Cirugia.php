<?php

namespace App\Models\Veterinaria;

use App\Models\Animal\HistorialClinico;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cirugia extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'cirugias';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        // Relaciones
        'historial_clinico_id',
        'cirujano_id',
        'anestesiologo_id',
        
        // Tipo y descripción
        'tipo_cirugia',
        'descripcion',
        
        // Fechas
        'fecha_programada',
        'fecha_realizacion',
        
        // Detalles quirúrgicos
        'duracion',
        'tipo_anestesia',
        'asistentes',
        
        // Estado y resultado
        'estado',
        'resultado',
        'complicaciones',
        'postoperatorio',
        'seguimiento_requerido',
        
        // Estado del animal (opcional, para tracking)
        'estado_animal',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_realizacion' => 'datetime',
        'asistentes' => 'array',
        'duracion' => 'integer',
        'seguimiento_requerido' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'duracion_formateada',
        'es_exitosa',
        'es_urgente',
    ];

    /**
     * Relacion: Historial clinico.
     */
    public function historialClinico(): BelongsTo
    {
        return $this->belongsTo(HistorialClinico::class, 'historial_clinico_id');
    }

    /**
     * Relacion: Cirujano (veterinario principal).
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
     * Accessor: Duración formateada en horas y minutos.
     */
    public function getDuracionFormateadaAttribute(): ?string
    {
        if (!$this->duracion) {
            return null;
        }

        $horas = floor($this->duracion / 60);
        $minutos = $this->duracion % 60;

        if ($horas > 0) {
            return $minutos > 0 
                ? "{$horas}h {$minutos}min" 
                : "{$horas}h";
        }

        return "{$minutos}min";
    }

    /**
     * Accessor: Determinar si la cirugía fue exitosa.
     */
    public function getEsExitosaAttribute(): bool
    {
        return $this->estado === 'realizada' && $this->resultado === 'exitosa';
    }

    /**
     * Accessor: Determinar si es una cirugía urgente.
     */
    public function getEsUrgenteAttribute(): bool
    {
        return $this->tipo_cirugia === 'emergencia';
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
     * Scope: Cirugias canceladas.
     */
    public function scopeCanceladas($query)
    {
        return $query->where('estado', 'cancelada');
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

    /**
     * Scope: Con complicaciones.
     */
    public function scopeConComplicaciones($query)
    {
        return $query->where('estado', 'realizada')
                     ->whereNotNull('complicaciones')
                     ->where('complicaciones', '!=', '');
    }

    /**
     * Scope: Exitosas.
     */
    public function scopeExitosas($query)
    {
        return $query->where('estado', 'realizada')
                     ->where('resultado', 'exitosa');
    }

    /**
     * Scope: Por cirujano.
     */
    public function scopePorCirujano($query, string $cirujanoId)
    {
        return $query->where('cirujano_id', $cirujanoId);
    }

    /**
     * Scope: Por anestesiólogo.
     */
    public function scopePorAnestesiologo($query, string $anestesiologoId)
    {
        return $query->where('anestesiologo_id', $anestesiologoId);
    }

    /**
     * Scope: Entre fechas.
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_programada', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope: Esterilizaciones.
     */
    public function scopeEsterilizaciones($query)
    {
        return $query->whereIn('tipo_cirugia', ['esterilizacion', 'castracion']);
    }

    /**
     * Método helper: Marcar como realizada.
     */
    public function marcarComoRealizada(array $datos = []): bool
    {
        $this->estado = 'realizada';
        $this->fecha_realizacion = $datos['fecha_realizacion'] ?? now();
        
        if (isset($datos['resultado'])) {
            $this->resultado = $datos['resultado'];
        }
        
        if (isset($datos['duracion'])) {
            $this->duracion = $datos['duracion'];
        }
        
        if (isset($datos['complicaciones'])) {
            $this->complicaciones = $datos['complicaciones'];
        }
        
        if (isset($datos['postoperatorio'])) {
            $this->postoperatorio = $datos['postoperatorio'];
        }

        if (isset($datos['estado_animal'])) {
            $this->estado_animal = $datos['estado_animal'];
        }

        return $this->save();
    }

    /**
     * Método helper: Cancelar cirugía.
     */
    public function cancelar(string $motivo = null): bool
    {
        $this->estado = 'cancelada';
        
        if ($motivo) {
            $this->complicaciones = "CANCELADA: {$motivo}";
        }
        
        return $this->save();
    }

    /**
     * Método helper: Verificar si puede ser editada.
     */
    public function puedeSerEditada(): bool
    {
        return $this->estado !== 'realizada';
    }

    /**
     * Método helper: Verificar si puede ser eliminada.
     */
    public function puedeSerEliminada(): bool
    {
        return in_array($this->estado, ['programada', 'cancelada']);
    }

    /**
     * Boot method para eventos del modelo.
     */
    protected static function boot()
    {
        parent::boot();

        // Antes de crear, validar que la fecha de realización no sea anterior a la programada
        static::creating(function ($cirugia) {
            if ($cirugia->fecha_realizacion && $cirugia->fecha_programada) {
                if ($cirugia->fecha_realizacion < $cirugia->fecha_programada) {
                    throw new \InvalidArgumentException(
                        'La fecha de realización no puede ser anterior a la fecha programada'
                    );
                }
            }
        });

        // Antes de actualizar, lo mismo
        static::updating(function ($cirugia) {
            if ($cirugia->fecha_realizacion && $cirugia->fecha_programada) {
                if ($cirugia->fecha_realizacion < $cirugia->fecha_programada) {
                    throw new \InvalidArgumentException(
                        'La fecha de realización no puede ser anterior a la fecha programada'
                    );
                }
            }
        });
    }
}