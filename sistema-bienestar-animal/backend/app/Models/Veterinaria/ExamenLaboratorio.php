<?php

namespace App\Models\Veterinaria;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamenLaboratorio extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'examenes_laboratorio';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'consulta_id',
        'tipo_examen',
        'fecha_solicitud',
        'fecha_resultado',
        'laboratorio',
        'resultados',
        'interpretacion',
        'archivos_adjuntos',
        'estado',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_resultado' => 'date',
        'resultados' => 'array',
        'archivos_adjuntos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacion: Consulta donde se solicito.
     */
    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    /**
     * Scope: Examenes solicitados.
     */
    public function scopeSolicitados($query)
    {
        return $query->where('estado', 'solicitado');
    }

    /**
     * Scope: Examenes en proceso.
     */
    public function scopeEnProceso($query)
    {
        return $query->where('estado', 'en_proceso');
    }

    /**
     * Scope: Examenes completados.
     */
    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    /**
     * Scope: Por tipo de examen.
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_examen', $tipo);
    }

    /**
     * Scope: Pendientes de resultado.
     */
    public function scopePendientesResultado($query)
    {
        return $query->whereIn('estado', ['solicitado', 'en_proceso']);
    }
}
