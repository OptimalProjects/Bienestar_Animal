<?php

namespace App\Models\Animal;

use App\Models\Adopcion\Adopcion;
use App\Models\Denuncia\Rescate;
use App\Models\User\Usuario;
use App\Traits\HasAuditoria;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Animal extends Model
{
    use HasFactory, HasUuid, SoftDeletes; // HasAuditoria;  ⚠️ Comentado temporalmente - falta migrar tabla eventos_auditoria

    protected $table = 'animals';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'codigo_unico',
        'nombre',
        'especie',
        'raza',
        'sexo',
        'edad_aproximada',
        'peso_actual',
        'color',
        'tamanio',
        'senias_particulares',
        'foto_principal',
        'galeria_fotos',
        'fecha_rescate',
        'ubicacion_rescate',
        'estado',
        'estado_salud',
        'observaciones',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'edad_aproximada' => 'integer',
        'peso_actual' => 'decimal:2',
        'galeria_fotos' => 'array',
        'fecha_rescate' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'edad_formato',
        'url_foto_principal',
    ];

    /**
     * Boot function.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($animal) {
            if (empty($animal->codigo_unico)) {
                $animal->codigo_unico = self::generarCodigoUnico();
            }
        });
    }

    /**
     * Generar código único para el animal.
     */
    public static function generarCodigoUnico(): string
    {
        do {
            $codigo = 'AN-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('codigo_unico', $codigo)->exists());

        return $codigo;
    }

    /**
     * Accessor: Edad en formato legible.
     */
    public function getEdadFormatoAttribute(): string
    {
        if (!$this->edad_aproximada) {
            return 'Desconocida';
        }

        $meses = $this->edad_aproximada;
        $anios = floor($meses / 12);
        $mesesRestantes = $meses % 12;

        $texto = [];
        if ($anios > 0) {
            $texto[] = $anios . ' año' . ($anios > 1 ? 's' : '');
        }
        if ($mesesRestantes > 0) {
            $texto[] = $mesesRestantes . ' mes' . ($mesesRestantes > 1 ? 'es' : '');
        }

        return implode(' y ', $texto);
    }

    /**
     * Accessor: URL completa de foto principal.
     */
    public function getUrlFotoPrincipalAttribute(): ?string
    {
        if (!$this->foto_principal) {
            return null;
        }

        return url('storage/' . $this->foto_principal);
    }

    /**
     * Relación: Historial clínico.
     */
    public function historialClinico(): HasOne
    {
        return $this->hasOne(HistorialClinico::class, 'animal_id');
    }

    /**
     * Relación: Adopciones.
     */
    public function adopciones(): HasMany
    {
        return $this->hasMany(Adopcion::class, 'animal_id');
    }

    /**
     * Relación: Rescates.
     */
    public function rescates(): HasMany
    {
        return $this->hasMany(Rescate::class, 'animal_rescatado_id');
    }

    /**
     * Relación: Usuario que creó el registro.
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'created_by');
    }

    /**
     * Relación: Usuario que actualizó el registro.
     */
    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'updated_by');
    }

    /**
     * Scopes
     */
    public function scopeEnRefugio($query)
    {
        return $query->where('estado', 'en_refugio');
    }

    public function scopeDisponiblesAdopcion($query)
    {
        return $query->where('estado', 'en_adopcion');
    }

    public function scopeAdoptados($query)
    {
        return $query->where('estado', 'adoptado');
    }

    public function scopePorEspecie($query, string $especie)
    {
        return $query->where('especie', $especie);
    }

    public function scopeSaludable($query)
    {
        return $query->whereIn('estado_salud', ['bueno', 'excelente']);
    }
}