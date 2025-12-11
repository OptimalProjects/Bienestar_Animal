<?php

namespace App\Models\Veterinaria;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoVacuna extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'tipos_vacunas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'especie_aplicable',
        'edad_minima',
        'intervalo_dosis',
        'numero_dosis',
        'es_obligatoria',
        'activo',
    ];

    protected $casts = [
        'edad_minima' => 'integer',
        'intervalo_dosis' => 'integer',
        'numero_dosis' => 'integer',
        'es_obligatoria' => 'boolean',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacion: Vacunas de este tipo.
     */
    public function vacunas(): HasMany
    {
        return $this->hasMany(Vacuna::class, 'tipo_vacuna_id');
    }

    /**
     * Scope: Tipos activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Por especie.
     */
    public function scopePorEspecie($query, string $especie)
    {
        return $query->where('especie_aplicable', $especie)
                     ->orWhere('especie_aplicable', 'ambos');
    }

    /**
     * Scope: Obligatorias.
     */
    public function scopeObligatorias($query)
    {
        return $query->where('es_obligatoria', true);
    }
}
