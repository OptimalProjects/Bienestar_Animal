<?php

namespace App\Models\User;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permiso extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'permisos';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'recurso',
        'accion',
        'descripcion',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Roles que tienen este permiso.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_permiso', 'permiso_id', 'rol_id')
            ->withPivot('asignado_por', 'fecha_asignacion')
            ->withTimestamps();
    }

    /**
     * Scope: Permisos por recurso.
     */
    public function scopePorRecurso($query, string $recurso)
    {
        return $query->where('recurso', $recurso);
    }

    /**
     * Scope: Permisos por acción.
     */
    public function scopePorAccion($query, string $accion)
    {
        return $query->where('accion', $accion);
    }
}