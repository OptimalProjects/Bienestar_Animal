<?php

namespace App\Models\User;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'roles';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'modulo',
        'requiere_mfa',
        'activo',
    ];

    protected $casts = [
        'requiere_mfa' => 'boolean',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Usuarios con este rol.
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'usuario_rol', 'rol_id', 'usuario_id')
            ->withPivot('asignado_por', 'fecha_asignacion', 'fecha_expiracion', 'activo')
            ->withTimestamps();
    }

    /**
     * Relación: Permisos del rol.
     */
    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso', 'rol_id', 'permiso_id')
            ->withPivot('asignado_por', 'fecha_asignacion')
            ->withTimestamps();
    }

    /**
     * Scope: Roles activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Roles por módulo.
     */
    public function scopePorModulo($query, string $modulo)
    {
        return $query->where('modulo', $modulo);
    }
}