<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\SsoFederationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class SsoController extends Controller
{
    private SsoFederationService $ssoService;

    public function __construct(SsoFederationService $ssoService)
    {
        $this->ssoService = $ssoService;
    }

    /**
     * Callback SSO - Recibe el token JWT del SCI
     *
     * El SCI redirige aquí después de autenticar al usuario contra Active Directory.
     * El token viene en el header Authorization: Bearer <token>
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function callback(Request $request): JsonResponse
    {
        try {
            // 1. Extraer token del header Authorization
            $token = $request->bearerToken();

            if (!$token) {
                // Intentar obtener de query parameter como fallback
                $token = $request->query('token');
            }

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de autenticación no proporcionado',
                    'error' => 'missing_token'
                ], 401);
            }

            // 2. Validar y decodificar el token JWT
            $payload = $this->ssoService->decodeAndValidateToken($token);

            // 3. Buscar o crear usuario local
            $usuario = $this->ssoService->findOrCreateUser($payload);

            // 4. Verificar que el usuario esté activo
            if (!$usuario->activo) {
                Log::warning('Intento de SSO con usuario inactivo', ['email' => $usuario->email]);
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario desactivado en el sistema',
                    'error' => 'user_inactive'
                ], 403);
            }

            // 5. Generar token Sanctum local
            $sanctumToken = $usuario->createToken('sso-session', ['*'])->plainTextToken;

            // 6. Cargar relaciones necesarias
            $usuario->load(['roles.permisos']);

            // 7. Obtener rol principal y permisos
            $rolPrincipal = $usuario->roles->first();
            $permisos = $usuario->roles->flatMap(function ($rol) {
                return $rol->permisos->map(function ($permiso) {
                    return $permiso->recurso . '.' . $permiso->accion;
                });
            })->unique()->values()->toArray();

            // 8. Registrar evento de auditoría
            Log::info('Login SSO exitoso', [
                'usuario_id' => $usuario->id,
                'email' => $usuario->email,
                'ip' => $request->ip(),
            ]);

            // 9. Responder con los datos de sesión
            return response()->json([
                'success' => true,
                'message' => 'Autenticación SSO exitosa',
                'access_token' => $sanctumToken,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration', 60) * 60,
                'usuario' => [
                    'id' => $usuario->id,
                    'username' => $usuario->username,
                    'nombres' => $usuario->nombres,
                    'apellidos' => $usuario->apellidos,
                    'email' => $usuario->email,
                    'rol' => $rolPrincipal?->nombre ?? 'Sin rol',
                    'rol_codigo' => $rolPrincipal?->codigo ?? null,
                ],
                'permisos' => $permisos,
            ]);

        } catch (Exception $e) {
            Log::error('Error en callback SSO', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'sso_error'
            ], 401);
        }
    }

    /**
     * Retorna la URL de login del SCI para redirección
     *
     * El frontend usa este endpoint para saber a dónde redirigir al usuario.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLoginUrl(Request $request): JsonResponse
    {
        $redirectUri = $request->query('redirect_uri', url('/api/v1/sso/callback'));

        $loginUrl = config('sso.sci.login_url');
        $clientId = config('sso.sci.client_id');

        $fullUrl = $loginUrl . '?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'token',
        ]);

        return response()->json([
            'success' => true,
            'login_url' => $fullUrl,
            'sci_login_url' => $loginUrl,
            'client_id' => $clientId,
        ]);
    }

    /**
     * Verifica si SSO está habilitado
     *
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'sso_enabled' => config('sso.enabled', false),
            'issuer' => config('sso.jwt.issuer'),
        ]);
    }
}
