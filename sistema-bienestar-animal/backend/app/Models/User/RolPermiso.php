<?php

namespace App\Models\User;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RolPermiso extends Pivot
{
    use HasUuid;

    protected $table = 'rol_permiso';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'rol_id',
        'permiso_id',
        'asignado_por',
        'fecha_asignacion',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
    ];
}
