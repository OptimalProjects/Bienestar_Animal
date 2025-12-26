<?php

namespace App\Models\Adopcion;

use App\Models\User\Usuario;
use App\Models\Denuncia\Denuncia;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitaDomiciliaria extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'visitas_domiciliarias';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'adopcion_id',
        'fecha_programada',
        'fecha_realizada',
        'visitador_id',
        'tipo_visita',
        'observaciones',
        'condiciones_hogar',
        'estado_animal',
        'resultado',
        'recomendaciones',
        'fotos_respaldo',
        'denuncia_id',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_realizada' => 'datetime',
        'condiciones_hogar' => 'array',
        'estado_animal' => 'array',
        'fotos_respaldo' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Tipos de visita disponibles.
     */
    const TIPOS_VISITA = [
        'pre_adopcion' => 'Pre-adopcion',
        'seguimiento_1mes' => 'Seguimiento 1 mes',
        'seguimiento_3meses' => 'Seguimiento 3 meses',
        'seguimiento_6meses' => 'Seguimiento 6 meses',
        'extraordinaria' => 'Extraordinaria',
    ];

    /**
     * Relacion: Adopcion.
     */
    public function adopcion(): BelongsTo
    {
        return $this->belongsTo(Adopcion::class, 'adopcion_id');
    }

    /**
     * Relacion: Visitador (usuario que realiza la visita).
     */
    public function visitador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'visitador_id');
    }

    /**
     * Alias: Funcionario (para compatibilidad con repositorios).
     */
    public function funcionario(): BelongsTo
    {
        return $this->visitador();
    }

    /**
     * Relacion: Denuncia (si se inicio rescate desde esta visita critica).
     */
    public function denuncia(): BelongsTo
    {
        return $this->belongsTo(Denuncia::class, 'denuncia_id');
    }

    /**
     * Scope: Visitas pre-adopcion.
     */
    public function scopePreAdopcion($query)
    {
        return $query->where('tipo_visita', 'pre_adopcion');
    }

    /**
     * Scope: Seguimientos post-adopcion.
     */
    public function scopeSeguimientos($query)
    {
        return $query->whereIn('tipo_visita', [
            'seguimiento_1mes',
            'seguimiento_3meses',
            'seguimiento_6meses',
            'extraordinaria'
        ]);
    }

    /**
     * Scope: Visitas pendientes (programadas pero no realizadas).
     */
    public function scopePendientes($query)
    {
        return $query->whereNull('fecha_realizada')
                     ->where('fecha_programada', '>=', today());
    }

    /**
     * Scope: Visitas realizadas.
     */
    public function scopeRealizadas($query)
    {
        return $query->whereNotNull('fecha_realizada');
    }

    /**
     * Scope: Visitas de hoy.
     */
    public function scopeDeHoy($query)
    {
        return $query->whereDate('fecha_programada', today());
    }

    /**
     * Scope: Visitas satisfactorias.
     */
    public function scopeSatisfactorias($query)
    {
        return $query->where('resultado', 'satisfactoria');
    }

    /**
     * Scope: Visitas criticas.
     */
    public function scopeCriticas($query)
    {
        return $query->where('resultado', 'critica');
    }

    /**
     * Scope: Por tipo de visita.
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_visita', $tipo);
    }

    /**
     * Verificar si la visita fue satisfactoria.
     */
    public function esSatisfactoria(): bool
    {
        return $this->resultado === 'satisfactoria';
    }

    /**
     * Verificar si la visita esta pendiente.
     */
    public function estaPendiente(): bool
    {
        return is_null($this->fecha_realizada);
    }
}
