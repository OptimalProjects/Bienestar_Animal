<?php

namespace App\Models\Veterinaria;

use App\Models\User\Usuario;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Veterinario extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'veterinarios';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'usuario_id',
        'nombres',
        'apellidos',
        'numero_tarjeta_profesional',
        'especialidad',
        'telefono',
        'email',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'nombre_completo',
    ];

    /**
     * Accessor: Nombre completo del veterinario.
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    /**
     * Relacion: Usuario asociado.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relacion: Consultas realizadas.
     */
    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class, 'veterinario_id');
    }

    /**
     * Relacion: Vacunas aplicadas.
     */
    public function vacunas(): HasMany
    {
        return $this->hasMany(Vacuna::class, 'veterinario_id');
    }

    /**
     * Relacion: Cirugias realizadas como cirujano.
     */
    public function cirugias(): HasMany
    {
        return $this->hasMany(Cirugia::class, 'cirujano_id');
    }

    /**
     * Relacion: Procedimientos realizados.
     */
    public function procedimientos(): HasMany
    {
        return $this->hasMany(Procedimiento::class, 'veterinario_id');
    }

    /**
     * Scope: Veterinarios activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Por especialidad.
     */
    public function scopePorEspecialidad($query, string $especialidad)
    {
        return $query->where('especialidad', $especialidad);
    }
}
