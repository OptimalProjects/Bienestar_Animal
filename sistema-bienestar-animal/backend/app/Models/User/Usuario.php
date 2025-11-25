<?php

namespace App\Models\User;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasFactory, HasUuid, SoftDeletes, HasApiTokens, Notifiable;

    protected $table = 'usuarios';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'nombres',
        'apellidos',
        'origen_autenticacion',
        'mfa_enabled',
        'mfa_secret',
        'activo',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password_hash',
        'mfa_secret',
    ];

    protected $casts = [
        'mfa_enabled' => 'boolean',
        'activo' => 'boolean',
        'ultimo_acceso' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'nombre_completo',
    ];

    /**
     * Get password attribute for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Accessor: Nombre completo.
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    /**
     * Relación: Roles del usuario.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'usuario_id', 'rol_id')
            ->withPivot('asignado_por', 'fecha_asignacion', 'fecha_expiracion', 'activo')
            ->withTimestamps();
    }

    /**
     * Relación: Eventos de auditoría.
     */
    public function eventosAuditoria(): HasMany
    {
        return $this->hasMany(EventoAuditoria::class, 'usuario_id');
    }

    /**
     * Scope: Usuarios activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Usuarios con MFA habilitado.
     */
    public function scopeConMfa($query)
    {
        return $query->where('mfa_enabled', true);
    }

    /**
     * Verificar si el usuario tiene un rol específico.
     */
    public function hasRole(string $roleCodigo): bool
    {
        return $this->roles()->where('codigo', $roleCodigo)->exists();
    }

    /**
     * Verificar si el usuario tiene un permiso específico.
     */
    public function hasPermission(string $recurso, string $accion): bool
    {
        return $this->roles()
            ->whereHas('permisos', function ($query) use ($recurso, $accion) {
                $query->where('recurso', $recurso)
                      ->where('accion', $accion);
            })
            ->exists();
    }
}