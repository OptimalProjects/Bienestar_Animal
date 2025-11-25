<?php

namespace App\Models\Animal;

use App\Models\Veterinaria\Cirugia;
use App\Models\Veterinaria\Consulta;
use App\Models\Veterinaria\Tratamiento;
use App\Models\Veterinaria\Vacuna;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistorialClinico extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'historiales_clinicos';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'animal_id',
        'fecha_apertura',
        'estado',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Animal.
     */
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    /**
     * Relación: Consultas.
     */
    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class, 'historial_clinico_id');
    }

    /**
     * Relación: Vacunas.
     */
    public function vacunas(): HasMany
    {
        return $this->hasMany(Vacuna::class, 'historial_clinico_id');
    }

    /**
     * Relación: Tratamientos.
     */
    public function tratamientos(): HasMany
    {
        return $this->hasMany(Tratamiento::class, 'historial_clinico_id');
    }

    /**
     * Relación: Cirugías.
     */
    public function cirugias(): HasMany
    {
        return $this->hasMany(Cirugia::class, 'historial_clinico_id');
    }

    /**
     * Scope: Historiales activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}