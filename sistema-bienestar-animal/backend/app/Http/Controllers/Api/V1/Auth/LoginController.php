<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\User\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends BaseController
{
    /**
     * Roles que requieren MFA.
     */
    protected array $rolesMfa = ['administrador', 'director'];

    /**
     * Login de usuario.
     * POST /api/v1/auth/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $usuario = Usuario::where('email', $request->email)->first();

            if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
                return $this->unauthorizedResponse('Credenciales incorrectas');
            }

            if (!$usuario->activo) {
                return $this->forbiddenResponse('Usuario inactivo. Contacte al administrador.');
            }

            // Verificar si requiere MFA
            $requiereMfa = $this->verificarRequiereMfa($usuario);

            if ($requiereMfa) {
                // Generar codigo MFA y enviarlo
                $codigoMfa = $this->generarCodigoMfa($usuario);

                return $this->successResponse([
                    'requiere_mfa' => true,
                    'usuario_id' => $usuario->id,
                    'mfa_enviado_a' => $this->ocultarEmail($usuario->email),
                ], 'Se requiere verificacion MFA');
            }

            // Login exitoso sin MFA
            return $this->completarLogin($usuario);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error en el login: ' . $e->getMessage());
        }
    }

    /**
     * Verificar codigo MFA.
     * POST /api/v1/auth/mfa/verify
     */
    public function verificarMfa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|string',
            'codigo' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $usuario = Usuario::findOrFail($request->usuario_id);

            // Verificar codigo MFA
            if (!$this->validarCodigoMfa($usuario, $request->codigo)) {
                return $this->unauthorizedResponse('Codigo MFA invalido o expirado');
            }

            // Limpiar codigo MFA
            $this->limpiarCodigoMfa($usuario);

            return $this->completarLogin($usuario);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al verificar MFA: ' . $e->getMessage());
        }
    }

    /**
     * Logout de usuario.
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'Sesion cerrada exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al cerrar sesion');
        }
    }

    /**
     * Refrescar token.
     * POST /api/v1/auth/refresh
     */
    public function refresh(Request $request)
    {
        try {
            $usuario = $request->user();

            // Revocar token actual
            $request->user()->currentAccessToken()->delete();

            // Crear nuevo token
            $token = $usuario->createToken('auth_token', ['*'], now()->addMinutes(60));

            return $this->successResponse([
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ], 'Token refrescado exitosamente');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al refrescar token');
        }
    }

    /**
     * Obtener usuario autenticado.
     * GET /api/v1/auth/me
     */
    public function me(Request $request)
    {
        try {
            $usuario = $request->user()->load(['roles.permisos']);

            // Obtener permisos de todos los roles
            $permisos = $usuario->roles->flatMap(function ($rol) {
                return $rol->permisos->pluck('recurso')->map(function ($recurso) use ($rol) {
                    return $rol->permisos->where('recurso', $recurso)->pluck('accion')->map(fn($a) => "{$recurso}.{$a}");
                })->flatten();
            })->unique()->values()->toArray();

            return $this->successResponse([
                'usuario' => $usuario,
                'permisos' => $permisos,
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener usuario');
        }
    }

    /**
     * Completar login y generar token.
     */
    protected function completarLogin(Usuario $usuario)
    {
        // Actualizar ultimo acceso
        $usuario->update([
            'ultimo_acceso' => now(),
        ]);

        // Crear token con expiracion de 60 minutos
        $token = $usuario->createToken('auth_token', ['*'], now()->addMinutes(60));

        // Cargar relaciones
        $usuario->load(['roles.permisos']);

        // Obtener rol principal (primer rol activo)
        $rolPrincipal = $usuario->roles->first();

        // Obtener permisos de todos los roles
        $permisos = $usuario->roles->flatMap(function ($rol) {
            return $rol->permisos->map(fn($p) => "{$p->recurso}.{$p->accion}");
        })->unique()->values()->toArray();

        return $this->successResponse([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'usuario' => [
                'id' => $usuario->id,
                'username' => $usuario->username,
                'nombres' => $usuario->nombres,
                'apellidos' => $usuario->apellidos,
                'email' => $usuario->email,
                'rol' => $rolPrincipal?->nombre,
                'rol_codigo' => $rolPrincipal?->codigo,
            ],
            'permisos' => $permisos,
        ], 'Login exitoso');
    }

    /**
     * Verificar si usuario requiere MFA.
     */
    protected function verificarRequiereMfa(Usuario $usuario): bool
    {
        $usuario->load('roles');

        if ($usuario->roles->isEmpty()) {
            return false;
        }

        // Verificar si alguno de sus roles requiere MFA
        foreach ($usuario->roles as $rol) {
            if ($rol->requiere_mfa || in_array(strtolower($rol->codigo), ['ADMIN', 'DIRECTOR'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generar y enviar codigo MFA.
     */
    protected function generarCodigoMfa(Usuario $usuario): string
    {
        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Guardar codigo en cache o DB (expira en 5 minutos)
        cache()->put("mfa_{$usuario->id}", [
            'codigo' => Hash::make($codigo),
            'intentos' => 0,
            'expira' => now()->addMinutes(5),
        ], now()->addMinutes(5));

        // Aqui se enviaria el codigo por email/SMS
        // Mail::to($usuario->email)->send(new MfaCodeMail($codigo));

        return $codigo;
    }

    /**
     * Validar codigo MFA.
     */
    protected function validarCodigoMfa(Usuario $usuario, string $codigo): bool
    {
        $mfaData = cache()->get("mfa_{$usuario->id}");

        if (!$mfaData) {
            return false;
        }

        if ($mfaData['intentos'] >= 3) {
            cache()->forget("mfa_{$usuario->id}");
            return false;
        }

        if (!Hash::check($codigo, $mfaData['codigo'])) {
            $mfaData['intentos']++;
            cache()->put("mfa_{$usuario->id}", $mfaData, now()->addMinutes(5));
            return false;
        }

        return true;
    }

    /**
     * Limpiar codigo MFA.
     */
    protected function limpiarCodigoMfa(Usuario $usuario): void
    {
        cache()->forget("mfa_{$usuario->id}");
    }

    /**
     * Ocultar email parcialmente.
     */
    protected function ocultarEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1];

        $visibleChars = min(3, strlen($name));
        $hidden = str_repeat('*', max(0, strlen($name) - $visibleChars));

        return substr($name, 0, $visibleChars) . $hidden . '@' . $domain;
    }
}
