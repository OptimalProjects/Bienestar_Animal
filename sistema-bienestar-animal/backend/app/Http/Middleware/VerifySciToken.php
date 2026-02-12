<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySciToken
{
    /**
     * Verificar el token de autenticacion del SCI.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token || $token !== config('sci.api_token')) {
            return response()->json([
                'app_id' => config('sci.app_id'),
                'error' => 'No autorizado',
                'timestamp' => now()->toIso8601String(),
            ], 401);
        }

        return $next($request);
    }
}
