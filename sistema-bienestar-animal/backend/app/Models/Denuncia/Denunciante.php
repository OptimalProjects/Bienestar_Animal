<?php

namespace App\Models\Denuncia;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Denunciante extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'denunciantes';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nombres',
        'apellidos',
        'telefono',
        'email',
        'direccion',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'nombre_completo',
    ];

    /**
     * Accessor: Nombre completo.
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    /**
     * Relacion: Denuncias realizadas.
     */
    public function denuncias(): HasMany
    {
        return $this->hasMany(Denuncia::class, 'denunciante_id');
    }

    /**
     * Scope: Con email.
     */
    public function scopeConEmail($query)
    {
        return $query->whereNotNull('email');
    }

    /**
     * Scope: Con telefono.
     */
    public function scopeConTelefono($query)
    {
        return $query->whereNotNull('telefono');
    }
}
