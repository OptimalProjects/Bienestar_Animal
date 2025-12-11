<?php

namespace App\Models\Veterinaria;

use App\Models\User\Usuario;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Procedimiento extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'procedimientos';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tratamiento_id',
        'tipo_procedimiento',
        'descripcion',
        'fecha_realizacion',
        'veterinario_id',
        'asistente_id',
        'duracion',
        'resultado',
        'complicaciones',
        'observaciones',
    ];

    protected $casts = [
        'fecha_realizacion' => 'datetime',
        'duracion' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacion: Tratamiento al que pertenece.
     */
    public function tratamiento(): BelongsTo
    {
        return $this->belongsTo(Tratamiento::class, 'tratamiento_id');
    }

    /**
     * Relacion: Veterinario que realizo el procedimiento.
     */
    public function veterinario(): BelongsTo
    {
        return $this->belongsTo(Veterinario::class, 'veterinario_id');
    }

    /**
     * Relacion: Asistente.
     */
    public function asistente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'asistente_id');
    }

    /**
     * Scope: Por tipo de procedimiento.
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_procedimiento', $tipo);
    }

    /**
     * Scope: Realizados hoy.
     */
    public function scopeDeHoy($query)
    {
        return $query->whereDate('fecha_realizacion', today());
    }

    /**
     * Scope: Con complicaciones.
     */
    public function scopeConComplicaciones($query)
    {
        return $query->whereNotNull('complicaciones');
    }
}
