<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Los roles permitidos
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
            ], 401);
        }

        // Verificar si el usuario tiene uno de los roles permitidos
        if (!$this->userHasRole($user, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene el rol requerido para esta accion',
                'roles_required' => $roles,
            ], 403);
        }

        return $next($request);
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados.
     */
    protected function userHasRole($user, array $roles): bool
    {
        if (!$user->rol) {
            return false;
        }

        $userRole = strtolower($user->rol->nombre);

        foreach ($roles as $role) {
            if (strtolower($role) === $userRole) {
                return true;
            }
        }

        return false;
    }
}
