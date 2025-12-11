<?php

namespace App\Models\Adopcion;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Adoptante extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'adoptantes';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nombres',
        'apellidos',
        'tipo_documento',
        'numero_documento',
        'fecha_nacimiento',
        'telefono',
        'email',
        'direccion',
        'tipo_vivienda',
        'tiene_patio',
        'experiencia_animales',
        'num_personas_hogar',
        'estado',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'tiene_patio' => 'boolean',
        'num_personas_hogar' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'nombre_completo',
        'edad',
    ];

    /**
     * Accessor: Nombre completo.
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    /**
     * Accessor: Edad calculada.
     */
    public function getEdadAttribute(): ?int
    {
        if (!$this->fecha_nacimiento) {
            return null;
        }
        return $this->fecha_nacimiento->age;
    }

    /**
     * Relacion: Adopciones del adoptante.
     */
    public function adopciones(): HasMany
    {
        return $this->hasMany(Adopcion::class, 'adoptante_id');
    }

    /**
     * Scope: Adoptantes activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope: Por tipo de documento.
     */
    public function scopePorTipoDocumento($query, string $tipo)
    {
        return $query->where('tipo_documento', $tipo);
    }

    /**
     * Scope: Con patio.
     */
    public function scopeConPatio($query)
    {
        return $query->where('tiene_patio', true);
    }

    /**
     * Scope: Por tipo de vivienda.
     */
    public function scopePorTipoVivienda($query, string $tipo)
    {
        return $query->where('tipo_vivienda', $tipo);
    }

    /**
     * Verificar si puede adoptar (no bloqueado y activo).
     */
    public function puedeAdoptar(): bool
    {
        return $this->estado === 'activo';
    }
}
