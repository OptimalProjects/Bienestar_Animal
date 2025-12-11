<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireMfa
{
    /**
     * Roles que requieren MFA.
     */
    protected array $mfaRequiredRoles = [
        'administrador',
        'director',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
            ], 401);
        }

        // Verificar si el usuario requiere MFA y si ha verificado
        if ($this->requiresMfa($user) && !$this->hasMfaVerified($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Se requiere verificacion MFA para esta accion',
                'requires_mfa' => true,
            ], 403);
        }

        return $next($request);
    }

    /**
     * Verificar si el usuario requiere MFA.
     */
    protected function requiresMfa($user): bool
    {
        if (!$user->rol) {
            return false;
        }

        return in_array(
            strtolower($user->rol->nombre),
            $this->mfaRequiredRoles
        );
    }

    /**
     * Verificar si el usuario ha completado la verificacion MFA.
     */
    protected function hasMfaVerified($user): bool
    {
        // Verificar si hay un registro de MFA verificado en la sesion actual
        // Esto se puede implementar usando claims en el token o cache
        $token = request()->bearerToken();

        if (!$token) {
            return false;
        }

        // Verificar en cache si el token tiene MFA verificado
        return cache()->has("mfa_verified_{$user->id}_{$this->hashToken($token)}");
    }

    /**
     * Marcar MFA como verificado para el usuario.
     */
    public static function markMfaVerified($user, string $token): void
    {
        $hash = substr(hash('sha256', $token), 0, 32);
        cache()->put(
            "mfa_verified_{$user->id}_{$hash}",
            true,
            now()->addHour()
        );
    }

    /**
     * Hash del token para almacenar en cache.
     */
    protected function hashToken(string $token): string
    {
        return substr(hash('sha256', $token), 0, 32);
    }
}
