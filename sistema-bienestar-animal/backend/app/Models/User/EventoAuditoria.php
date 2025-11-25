<?php

namespace App\Models\User;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoAuditoria extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'eventos_auditoria';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'trace_id',
        'timestamp',
        'usuario_id',
        'accion',
        'recurso',
        'modulo',
        'detalles',
        'ip_address',
        'user_agent',
        'resultado',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'detalles' => 'array',
    ];

    /**
     * Relación: Usuario que generó el evento.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Scope: Eventos por módulo.
     */
    public function scopePorModulo($query, string $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    /**
     * Scope: Eventos por usuario.
     */
    public function scopePorUsuario($query, string $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Scope: Eventos exitosos.
     */
    public function scopeExitosos($query)
    {
        return $query->where('resultado', 'exitoso');
    }

    /**
     * Scope: Eventos fallidos.
     */
    public function scopeFallidos($query)
    {
        return $query->where('resultado', 'fallido');
    }
}