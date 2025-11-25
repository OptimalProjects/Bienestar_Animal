<?php

namespace App\Traits;

use App\Models\User\EventoAuditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait HasAuditoria
{
    protected static function bootHasAuditoria(): void
    {
        static::created(function ($model) {
            self::registrarEvento($model, 'crear');
        });

        static::updated(function ($model) {
            self::registrarEvento($model, 'actualizar');
        });

        static::deleted(function ($model) {
            self::registrarEvento($model, 'eliminar');
        });
    }

    protected static function registrarEvento($model, string $accion): void
    {
        EventoAuditoria::create([
            'trace_id' => (string) Str::uuid(),
            'timestamp' => now(),
            'usuario_id' => Auth::id(),
            'accion' => $accion,
            'recurso' => class_basename($model),
            'modulo' => self::getModuloNombre($model),
            'detalles' => $model->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'resultado' => 'exitoso',
        ]);
    }

    protected static function getModuloNombre($model): string
    {
        $namespace = get_class($model);
        
        if (str_contains($namespace, 'Animal\\')) return 'animales';
        if (str_contains($namespace, 'Veterinaria\\')) return 'veterinaria';
        if (str_contains($namespace, 'Adopcion\\')) return 'adopciones';
        if (str_contains($namespace, 'Denuncia\\')) return 'denuncias';
        if (str_contains($namespace, 'User\\')) return 'usuarios';
        
        return 'general';
    }
}