<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  El codigo del permiso requerido
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
            ], 401);
        }

        // Verificar si el usuario tiene el permiso
        if (!$this->userHasPermission($user, $permission)) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para realizar esta accion',
                'permission_required' => $permission,
            ], 403);
        }

        return $next($request);
    }

    /**
     * Verificar si el usuario tiene un permiso especifico.
     */
    protected function userHasPermission($user, string $permission): bool
    {
        // Si el usuario no tiene rol, no tiene permisos
        if (!$user->rol) {
            return false;
        }

        // Administradores tienen todos los permisos
        if (strtolower($user->rol->nombre) === 'administrador') {
            return true;
        }

        // Verificar permisos especificos
        return $user->rol->permisos()
            ->where('codigo', $permission)
            ->exists();
    }
}
