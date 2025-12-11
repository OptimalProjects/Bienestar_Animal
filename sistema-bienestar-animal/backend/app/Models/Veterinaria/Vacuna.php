<?php

namespace App\Models\Veterinaria;

use App\Models\Animal\HistorialClinico;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected $casts = [
        'fecha_aplicacion' => 'date',
        'fecha_proxima_dosis' => 'date',
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
     * Relacion: Tipo de vacuna.
     */
    public function tipoVacuna(): BelongsTo
    {
        return $this->belongsTo(TipoVacuna::class, 'tipo_vacuna_id');
    }

    /**
     * Relacion: Veterinario que aplico la vacuna.
     */
    public function veterinario(): BelongsTo
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
