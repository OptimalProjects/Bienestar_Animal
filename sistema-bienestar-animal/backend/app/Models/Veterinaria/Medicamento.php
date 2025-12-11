<?php

namespace App\Models\Veterinaria;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Medicamento extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'medicamentos';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tratamiento_id',
        'producto_id',
        'dosis',
        'frecuencia',
        'duracion_dias',
        'via_administracion',
        'instrucciones_especiales',
        'observaciones',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'duracion_dias' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacion: Tratamiento al que pertenece.
     */
    public function tratamiento(): BelongsTo
    {
        return $this->belongsTo(Tratamiento::class, 'tratamiento_id');
    }

    /**
     * Relacion: Producto farmaceutico.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoFarmaceutico::class, 'producto_id');
    }

    /**
     * Scope: Medicamentos activos (en curso).
     */
    public function scopeEnCurso($query)
    {
        return $query->where('fecha_inicio', '<=', now())
                     ->where('fecha_fin', '>=', now());
    }

    /**
     * Scope: Por via de administracion.
     */
    public function scopePorVia($query, string $via)
    {
        return $query->where('via_administracion', $via);
    }
}
