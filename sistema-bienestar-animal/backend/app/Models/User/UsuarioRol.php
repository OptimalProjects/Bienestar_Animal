<?php

namespace App\Models\User;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UsuarioRol extends Pivot
{
    use HasUuid;

    protected $table = 'usuario_rol';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'rol_id',
        'asignado_por',
        'fecha_asignacion',
        'fecha_expiracion',
        'activo',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_expiracion' => 'datetime',
        'activo' => 'boolean',
    ];
}
