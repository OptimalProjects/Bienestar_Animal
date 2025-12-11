<?php

namespace App\Models\Veterinaria;

use App\Models\Animal\Animal;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordatorioVacuna extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'recordatorios_vacunas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'vacuna_id',
        'animal_id',
        'fecha_recordatorio',
        'tipo',
        'estado',
        'fecha_envio',
        'canal',
        'destinatario',
    ];

    protected $casts = [
        'fecha_recordatorio' => 'date',
        'fecha_envio' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacion: Vacuna asociada.
     */
    public function vacuna(): BelongsTo
    {
        return $this->belongsTo(Vacuna::class, 'vacuna_id');
    }

    /**
     * Relacion: Animal.
     */
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    /**
     * Scope: Recordatorios pendientes.
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope: Recordatorios para hoy.
     */
    public function scopeParaHoy($query)
    {
        return $query->whereDate('fecha_recordatorio', today());
    }

    /**
     * Scope: Recordatorios proximos (siguiente semana).
     */
    public function scopeProximos($query)
    {
        return $query->whereBetween('fecha_recordatorio', [now(), now()->addWeek()]);
    }

    /**
     * Scope: Por canal de envio.
     */
    public function scopePorCanal($query, string $canal)
    {
        return $query->where('canal', $canal);
    }
}
