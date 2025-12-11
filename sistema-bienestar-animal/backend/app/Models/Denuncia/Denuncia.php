<?php

namespace App\Models\Denuncia;

use App\Models\User\Usuario;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Denuncia extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'denuncias';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'numero_ticket',
        'denunciante_id',
        'fecha_denuncia',
        'canal_recepcion',
        'tipo_denuncia',
        'descripcion',
        'ubicacion',
        'latitud',
        'longitud',
        'evidencias',
        'prioridad',
        'estado',
        'responsable_id',
        'fecha_asignacion',
        'fecha_resolucion',
        'observaciones_resolucion',
        'es_anonima',
    ];

    protected $casts = [
        'fecha_denuncia' => 'datetime',
        'fecha_asignacion' => 'datetime',
        'fecha_resolucion' => 'datetime',
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'evidencias' => 'array',
        'es_anonima' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Palabras clave para clasificacion automatica de urgencia.
     */
    const PALABRAS_URGENTE = [
        'sangre', 'sangrado', 'atropellado', 'atropello', 'moribundo',
        'agonizando', 'grave', 'muerto', 'muriendo', 'urgente', 'emergencia'
    ];

    /**
     * Boot function para generar numero de ticket.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($denuncia) {
            if (empty($denuncia->numero_ticket)) {
                $denuncia->numero_ticket = self::generarNumeroTicket();
            }

            // Clasificar prioridad automaticamente
            if (empty($denuncia->prioridad)) {
                $denuncia->prioridad = self::clasificarPrioridad(
                    $denuncia->tipo_denuncia,
                    $denuncia->descripcion
                );
            }
        });
    }

    /**
     * Generar numero de ticket unico.
     */
    public static function generarNumeroTicket(): string
    {
        do {
            $ticket = 'DN-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('numero_ticket', $ticket)->exists());

        return $ticket;
    }

    /**
     * Clasificar prioridad automaticamente.
     */
    public static function clasificarPrioridad(string $tipo, string $descripcion): string
    {
        $descripcionLower = strtolower($descripcion);

        // Verificar palabras clave urgentes
        foreach (self::PALABRAS_URGENTE as $palabra) {
            if (str_contains($descripcionLower, $palabra)) {
                return 'urgente';
            }
        }

        // Clasificar por tipo
        return match($tipo) {
            'animal_herido' => 'alta',
            'maltrato' => 'alta',
            'animal_peligroso' => 'alta',
            'abandono' => 'media',
            default => 'baja',
        };
    }

    /**
     * Relacion: Denunciante.
     */
    public function denunciante(): BelongsTo
    {
        return $this->belongsTo(Denunciante::class, 'denunciante_id');
    }

    /**
     * Relacion: Responsable asignado.
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsable_id');
    }

    /**
     * Relacion: Rescates asociados.
     */
    public function rescates(): HasMany
    {
        return $this->hasMany(Rescate::class, 'denuncia_id');
    }

    /**
     * Scope: Denuncias recibidas.
     */
    public function scopeRecibidas($query)
    {
        return $query->where('estado', 'recibida');
    }

    /**
     * Scope: En revision.
     */
    public function scopeEnRevision($query)
    {
        return $query->where('estado', 'en_revision');
    }

    /**
     * Scope: Asignadas.
     */
    public function scopeAsignadas($query)
    {
        return $query->where('estado', 'asignada');
    }

    /**
     * Scope: En atencion.
     */
    public function scopeEnAtencion($query)
    {
        return $query->where('estado', 'en_atencion');
    }

    /**
     * Scope: Resueltas.
     */
    public function scopeResueltas($query)
    {
        return $query->where('estado', 'resuelta');
    }

    /**
     * Scope: Por prioridad.
     */
    public function scopePorPrioridad($query, string $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    /**
     * Scope: Urgentes.
     */
    public function scopeUrgentes($query)
    {
        return $query->where('prioridad', 'urgente');
    }

    /**
     * Scope: Criticas (urgentes sin asignar).
     */
    public function scopeCriticas($query)
    {
        return $query->where('prioridad', 'urgente')
                     ->whereIn('estado', ['recibida', 'en_revision']);
    }

    /**
     * Scope: Por tipo de denuncia.
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_denuncia', $tipo);
    }

    /**
     * Scope: Anonimas.
     */
    public function scopeAnonimas($query)
    {
        return $query->where('es_anonima', true);
    }

    /**
     * Scope: Por canal de recepcion.
     */
    public function scopePorCanal($query, string $canal)
    {
        return $query->where('canal_recepcion', $canal);
    }

    /**
     * Scope: De hoy.
     */
    public function scopeDeHoy($query)
    {
        return $query->whereDate('fecha_denuncia', today());
    }

    /**
     * Scope: Sin asignar.
     */
    public function scopeSinAsignar($query)
    {
        return $query->whereNull('responsable_id');
    }

    /**
     * Scope: Pendientes de resolucion.
     */
    public function scopePendientes($query)
    {
        return $query->whereNotIn('estado', ['resuelta', 'cerrada', 'desestimada']);
    }

    /**
     * Verificar si esta dentro del SLA.
     */
    public function estaEnSla(): bool
    {
        if (in_array($this->estado, ['resuelta', 'cerrada', 'desestimada'])) {
            return true;
        }

        $horasTranscurridas = $this->fecha_denuncia->diffInHours(now());

        return match($this->prioridad) {
            'urgente' => $horasTranscurridas <= config('app.sla_denuncia_critica', 4),
            'alta' => $horasTranscurridas <= config('app.sla_denuncia_alta', 24),
            'media' => $horasTranscurridas <= config('app.sla_denuncia_media', 72),
            default => true,
        };
    }

    /**
     * Verificar si requiere atencion inmediata.
     */
    public function requiereAtencionInmediata(): bool
    {
        return $this->prioridad === 'urgente' &&
               in_array($this->estado, ['recibida', 'en_revision']);
    }
}
