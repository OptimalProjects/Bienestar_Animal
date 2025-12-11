<?php

namespace App\Models\Administracion;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indicador extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'indicadores';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'unidad_medida',
        'formula',
        'frecuencia_actualizacion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacion: Puntos de indicadores (valores historicos).
     */
    public function puntos(): HasMany
    {
        return $this->hasMany(PuntoIndicador::class, 'indicador_id');
    }

    /**
     * Obtener el ultimo valor del indicador.
     */
    public function getUltimoValorAttribute()
    {
        $ultimo = $this->puntos()->latest('fecha')->first();
        return $ultimo ? $ultimo->valor : null;
    }

    /**
     * Obtener valores del ultimo mes.
     */
    public function valoresUltimoMes()
    {
        return $this->puntos()
                    ->whereBetween('fecha', [now()->subMonth(), now()])
                    ->orderBy('fecha')
                    ->get();
    }

    /**
     * Scope: Indicadores activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Por tipo.
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
