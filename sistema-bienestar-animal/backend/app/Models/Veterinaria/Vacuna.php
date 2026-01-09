<?php

namespace App\Models\Veterinaria;

use App\Models\Animal\Animal;
use App\Models\Animal\HistorialClinico;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Vacuna extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'vacunas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'historial_clinico_id',
        'tipo_vacuna_id',
        'fecha_aplicacion',
        'fecha_proxima_dosis',
        'lote_vacuna',
        'fabricante',
        'veterinario_id',
        'observaciones',
        'reacciones_adversas',
        'estado',
        'nombre_vacuna',
        'fecha_vencimiento',
        'dosis',
        'via_administracion',
        'sitio_aplicacion',
        'numero_dosis',
    ];

    protected $casts = [
        'fecha_aplicacion' => 'date',
        'fecha_proxima_dosis' => 'date',
        'fecha_vencimiento' => 'date',
        'dosis' => 'decimal:2',
    ];

    /**
     * Relacion: Historial clinico.
     */
    public function historialClinico(): BelongsTo
    {
        return $this->belongsTo(HistorialClinico::class, 'historial_clinico_id');
    }

    /**
     * Relacion: Animal a través del historial clínico.
     */
    public function animal(): HasOneThrough
    {
        return $this->hasOneThrough(
            Animal::class,
            HistorialClinico::class,
            'id',                   // PK local en historiales_clinicos
            'id',                   // PK local en animals
            'historial_clinico_id', // FK en vacunas
            'animal_id'             // FK en historiales_clinicos
        );
    }

    /**
     * Relacion: Tipo de vacuna.
     */
    public function tipoVacuna()
    {
        return $this->belongsTo(TipoVacuna::class, 'tipo_vacuna_id');
    }

    /**
     * Relacion: Veterinario que aplico la vacuna.
     */
    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class, 'veterinario_id');
    }

    /**
     * Relacion: Recordatorios de esta vacuna.
     */
    public function recordatorios(): HasMany
    {
        return $this->hasMany(RecordatorioVacuna::class, 'vacuna_id');
    }

    /**
     * Scope: Vacunas aplicadas.
     */
    public function scopeAplicadas($query)
    {
        return $query->where('estado', 'aplicada');
    }

    /**
     * Scope: Vacunas programadas.
     */
    public function scopeProgramadas($query)
    {
        return $query->where('estado', 'programada');
    }

    /**
     * Scope: Vacunas vencidas.
     */
    public function scopeVencidas($query)
    {
        return $query->where('estado', 'vencida');
    }

    /**
     * Scope: Con proxima dosis pendiente.
     */
    public function scopeConProximaDosis($query)
    {
        return $query->whereNotNull('fecha_proxima_dosis')
                     ->where('fecha_proxima_dosis', '>=', now());
    }
}
