<?php

namespace App\Models\Animal;

use App\Models\Adopcion\Adopcion;
use App\Models\Denuncia\Rescate;
use App\Models\User\Usuario;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Veterinaria\Vacuna;


class Animal extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

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
        'esterilizacion',
        'fecha_esterilizacion',
        'veterinario_esterilizacion',
        'certificado_esterilizacion',
        'fecha_adjuncion_certificado',
        'notas_certificado',
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
        'esterilizacion' => 'boolean',
        'fecha_rescate' => 'date',
        'fecha_esterilizacion' => 'date',
        'fecha_adjuncion_certificado' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'edad_formateada',
        'foto_url',
        'galeria_urls',
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
        $year = date('Y');

        // Buscar el último código generado para este año
        $ultimoAnimal = self::where('codigo_unico', 'like', "AN-{$year}-%")
            ->orderBy('codigo_unico', 'desc')
            ->first();

        if ($ultimoAnimal) {
            // Tomar los últimos 5 dígitos y sumarle 1
            $ultimoNumero = (int) substr($ultimoAnimal->codigo_unico, -5);
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            // Primer código del año
            $nuevoNumero = 1;
        }

        $consecutivo = str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);

        return "AN-{$year}-{$consecutivo}";
    }


    /**
     * Accessor: Edad en formato legible.
     */
    public function getEdadFormateadaAttribute(): string
    {
        if ($this->edad_aproximada === null) {
            return 'Sin información de edad';
        }

        $meses = (int) $this->edad_aproximada;
        $anios = intdiv($meses, 12);
        $mesesRestantes = $meses % 12;

        // Caso raro: 0 meses exactos
        if ($anios === 0 && $mesesRestantes === 0) {
            return '0 meses';
        }

        $partes = [];

        if ($anios > 0) {
            $partes[] = $anios.' año'.($anios === 1 ? '' : 's');
        }

        if ($mesesRestantes > 0) {
            $partes[] = $mesesRestantes.' mes'.($mesesRestantes === 1 ? '' : 'es');
        }

        return implode(' y ', $partes);
    }


    /**
     * Accessor: URL completa de foto principal.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto_principal) {
            return null;
        }

        return url('storage/' . $this->foto_principal);
    }

    /**
     * Accessor: URLs completas de galería de fotos.
     */
    public function getGaleriaUrlsAttribute(): array
    {
        if (!$this->galeria_fotos || !is_array($this->galeria_fotos)) {
            return [];
        }

        return array_map(function ($path) {
            return url('storage/' . $path);
        }, $this->galeria_fotos);
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
     * Relación: Vacunas a través del historial clínico.
     */
    public function vacunas(): HasManyThrough
    {
        return $this->hasManyThrough(
            Vacuna::class,
            HistorialClinico::class,
            'animal_id',           // FK en historiales_clinicos
            'historial_clinico_id', // FK en vacunas
            'id',                   // PK local en animals
            'id'                    // PK local en historiales_clinicos
        );
    }

    /**
     * Scopes
     */
    public function scopeDisponiblesAdopcion($query)
    {
        return $query->where('estado', 'en_adopcion');
    }

    public function scopeEnRefugio($query)
    {
        return $query->where('estado', 'en_refugio');
    }

    public function scopeAdoptados($query)
    {
        return $query->where('estado', 'adoptado');
    }

    public function scopePorEspecie($query, string $especie)
    {
        return $query->where('especie', $especie);
    }

    public function scopePorEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorEstadoSalud($query, string $estadoSalud)
    {
        return $query->where('estado_salud', $estadoSalud);
    }

    public function scopeSaludable(Builder $query): Builder
    {
        // Se consideran "saludables" estos estados
        return $query->whereIn('estado_salud', ['estable', 'bueno', 'excelente']);
    }


    public function scopeBuscarPorNombre($query, ?string $nombre)
    {
        return $query->when($nombre, function ($q) use ($nombre) {
            // BÚSQUEDA EXACTA
            $q->where('nombre', $nombre);
        });
    }


    /**
     * Métodos de negocio (simplificados - sin campos extras)
     */
    public function cambiarEstado(string $nuevoEstado): bool
    {
        $transicionesValidas = [
            'en_calle' => ['en_refugio', 'fallecido'],
            'en_refugio' => ['en_tratamiento', 'en_adopcion', 'fallecido'],
            'en_tratamiento' => ['en_refugio', 'en_adopcion', 'fallecido'],
            'en_adopcion' => ['adoptado', 'en_refugio', 'fallecido'],
            'adoptado' => ['fallecido'],
        ];

        if (!isset($transicionesValidas[$this->estado]) || 
            !in_array($nuevoEstado, $transicionesValidas[$this->estado])) {
            throw new \InvalidArgumentException(
                "No se puede cambiar de '{$this->estado}' a '{$nuevoEstado}'"
            );
        }

        $this->estado = $nuevoEstado;
        return $this->save();
    }

    public function estaDisponibleAdopcion(): bool
    {
        return $this->estado === 'en_adopcion' && 
               in_array($this->estado_salud, ['bueno', 'excelente']);
    }
}