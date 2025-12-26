<?php

namespace App\Models\Adopcion;

use App\Models\Animal\Animal;
use App\Models\User\Usuario;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Adopcion extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'adopciones';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'animal_id',
        'adoptante_id',
        'fecha_solicitud',
        'fecha_aprobacion',
        'fecha_entrega',
        'estado',
        'evaluador_id',
        'observaciones',
        'contrato_firmado',
        'contrato_url',
        'motivo_rechazo',
        'motivo_revocacion',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_aprobacion' => 'datetime',
        'fecha_entrega' => 'datetime',
        'contrato_firmado' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacion: Animal adoptado.
     */
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    /**
     * Relacion: Adoptante.
     */
    public function adoptante(): BelongsTo
    {
        return $this->belongsTo(Adoptante::class, 'adoptante_id');
    }

    /**
     * Relacion: Evaluador (usuario que evaluo la solicitud).
     */
    public function evaluador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'evaluador_id');
    }

    /**
     * Relacion: Visitas domiciliarias.
     */
    public function visitasDomiciliarias(): HasMany
    {
        return $this->hasMany(VisitaDomiciliaria::class, 'adopcion_id');
    }

    /**
     * Alias: Visitas (para compatibilidad con repositorios).
     */
    public function visitas(): HasMany
    {
        return $this->visitasDomiciliarias();
    }

    /**
     * Relacion: Devolucion (si el animal fue devuelto).
     */
    public function devolucion(): HasOne
    {
        return $this->hasOne(Devolucion::class, 'adopcion_id');
    }

    /**
     * Obtener visita pre-adopcion.
     */
    public function visitaPreAdopcion()
    {
        return $this->visitasDomiciliarias()
                    ->where('tipo_visita', 'pre_adopcion')
                    ->first();
    }

    /**
     * Obtener seguimientos post-adopcion.
     */
    public function seguimientosPostAdopcion()
    {
        return $this->visitasDomiciliarias()
                    ->whereIn('tipo_visita', [
                        'seguimiento_1mes',
                        'seguimiento_3meses',
                        'seguimiento_6meses',
                        'extraordinaria'
                    ])
                    ->orderBy('fecha_programada');
    }

    /**
     * Scope: Solicitudes pendientes.
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'solicitada');
    }

    /**
     * Scope: En evaluacion.
     */
    public function scopeEnEvaluacion($query)
    {
        return $query->where('estado', 'en_evaluacion');
    }

    /**
     * Scope: Aprobadas.
     */
    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }

    /**
     * Scope: Completadas.
     */
    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
    }

    /**
     * Scope: Rechazadas.
     */
    public function scopeRechazadas($query)
    {
        return $query->where('estado', 'rechazada');
    }

    /**
     * Scope: Revocadas.
     */
    public function scopeRevocadas($query)
    {
        return $query->where('estado', 'revocada');
    }

    /**
     * Scope: Devueltas.
     */
    public function scopeDevueltas($query)
    {
        return $query->where('estado', 'devuelta');
    }

    /**
     * Scope: Con contrato firmado.
     */
    public function scopeConContrato($query)
    {
        return $query->where('contrato_firmado', true);
    }

    /**
     * Scope: Del mes actual.
     */
    public function scopeDelMes($query)
    {
        return $query->whereMonth('fecha_solicitud', now()->month)
                     ->whereYear('fecha_solicitud', now()->year);
    }

    /**
     * Verificar si la adopcion esta activa.
     */
    public function estaActiva(): bool
    {
        return in_array($this->estado, ['solicitada', 'en_evaluacion', 'aprobada']);
    }

    /**
     * Verificar si se puede aprobar.
     */
    public function puedeAprobarse(): bool
    {
        return $this->estado === 'en_evaluacion' && $this->tieneVisitaPreAdopcionSatisfactoria();
    }

    /**
     * Verificar si tiene visita pre-adopcion satisfactoria.
     */
    public function tieneVisitaPreAdopcionSatisfactoria(): bool
    {
        $visita = $this->visitaPreAdopcion();
        return $visita && $visita->resultado === 'satisfactoria';
    }
}
