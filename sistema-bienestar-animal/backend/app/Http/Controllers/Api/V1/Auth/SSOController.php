<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\User\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SSOController extends BaseController
{
    /**
     * Callback SSO - Recibe el JWT token del sistema SSO externo
     * GET /api/v1/sso/callback?jwt_token=xxx
     * 
     * Esta ruta debe coincidir con la registrada en el SSO:
     * https://dev-bienestar-animal.enigmadev.co/sso/callback
     */
    public function callback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jwt_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $jwtToken = $request->input('jwt_token');

            // Validar el JWT token del SSO
            $payload = $this->validarJwtSso($jwtToken);

            if (!$payload) {
                return $this->unauthorizedResponse('Token SSO invalido o expirado');
            }

            // Buscar o crear usuario basado en el payload del SSO
            $usuario = $this->obtenerOCrearUsuario($payload);

            if (!$usuario) {
                return $this->unauthorizedResponse('No se pudo autenticar el usuario');
            }

            if (!$usuario->activo) {
                return $this->forbiddenResponse('Usuario inactivo. Contacte al administrador.');
            }

            // Completar login y generar token de Sanctum
            return $this->completarLoginSso($usuario);

        } catch (\Exception $e) {
            \Log::error('Error en SSO callback: ' . $e->getMessage());
            return $this->serverErrorResponse('Error en SSO callback: ' . $e->getMessage());
        }
    }

    /**
     * ALTERNATIVA: Validar token SSO (sin completar login)
     * POST /api/v1/sso/validate
     * 
     * Útil si prefieres un flujo en dos pasos:
     * 1. Validar el token
     * 2. Hacer login con el token validado
     */
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $jwtToken = $request->input('token');
            $payload = $this->validarJwtSso($jwtToken);

            if (!$payload) {
                return $this->unauthorizedResponse('Token SSO invalido o expirado');
            }

            return $this->successResponse([
                'valid' => true,
                'user_data' => [
                    'email' => $payload->email ?? null,
                    'nombres' => $payload->nombres ?? null,
                    'apellidos' => $payload->apellidos ?? null,
                    'rol' => $payload->rol ?? null,
                ]
            ], 'Token valido');

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Token inválido: ' . $e->getMessage()
            ], 401);
        }
    }

    /**
     * ALTERNATIVA: Login con token SSO en header
     * POST /api/v1/sso/login
     * Headers: Authorization: Bearer {jwt_token}
     * 
     * Similar al enfoque de tus compañeros
     */
    public function loginWithHeader(Request $request)
    {
        try {
            // Obtener JWT del header Authorization
            $authHeader = $request->header('Authorization');
            
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->unauthorizedResponse('Token no proporcionado');
            }

            $jwtToken = substr($authHeader, 7); // Remover "Bearer "

            // Validar el JWT token del SSO
            $payload = $this->validarJwtSso($jwtToken);

            if (!$payload) {
                return $this->unauthorizedResponse('Token SSO invalido o expirado');
            }

            // Buscar o crear usuario
            $usuario = $this->obtenerOCrearUsuario($payload);

            if (!$usuario || !$usuario->activo) {
                return $this->unauthorizedResponse('Usuario no autorizado');
            }

            // Completar login
            return $this->completarLoginSso($usuario);

        } catch (\Exception $e) {
            \Log::error('Error en SSO login: ' . $e->getMessage());
            return $this->serverErrorResponse('Error en SSO login: ' . $e->getMessage());
        }
    }

    /**
     * DESARROLLO: Generar token fake para pruebas locales
     * POST /api/v1/sso/generate-fake-token
     * Body: { "user_id": 1 }
     * 
     * Solo disponible en ambiente local/development
     */
    public function generateFakeToken(Request $request)
    {
        // Solo en desarrollo
        if (config('app.env') !== 'local' && config('app.env') !== 'development') {
            return response()->json([
                'message' => 'Tokens fake solo disponibles en desarrollo'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:usuarios,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $usuario = Usuario::findOrFail($request->user_id);
            
            // Generar JWT fake con la estructura esperada
            $payload = [
                'iss' => env('SSO_ISSUER', 'bienestar-animal-sso'),
                'aud' => env('SSO_AUDIENCE', 'bienestar-animal'),
                'iat' => time(),
                'exp' => time() + 3600, // 1 hora
                'sub' => (string) $usuario->id,
                'email' => $usuario->email,
                'nombres' => $usuario->nombres,
                'apellidos' => $usuario->apellidos,
                'rol' => $usuario->roles->first()?->codigo ?? 'CIUDADANO',
            ];

            // Usar secret de desarrollo
            $secretKey = env('SSO_JWT_SECRET', 'dev_secret_key_change_in_production');
            $jwt = JWT::encode($payload, $secretKey, 'HS256');

            return response()->json([
                'token' => $jwt,
                'message' => "Token fake generado para {$usuario->email}",
                'url' => env('FRONTEND_URL', 'http://localhost:5173') . "/sso/callback?jwt_token={$jwt}",
                'expires_in' => 3600,
            ]);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error generando token fake: ' . $e->getMessage());
        }
    }

/**
 * Validar JWT token del SSO externo
 * Soporta RS256 (producción) y HS256 (desarrollo)
 */
protected function validarJwtSso(string $jwtToken)
{
    try {
        // PRIORIDAD 1: Intentar con RS256 (producción - clave pública)
        $publicKeyPath = storage_path('keys/jwt_public.pem');
        
        if (file_exists($publicKeyPath)) {
            try {
                $publicKey = file_get_contents($publicKeyPath);
                \Log::info('🔐 Validando JWT SSO con RS256 (producción)');
                
                $decoded = JWT::decode($jwtToken, new Key($publicKey, 'RS256'));
                
                if (isset($decoded->email)) {
                    \Log::info('✅ JWT SSO validado con RS256', [
                        'email' => $decoded->email
                    ]);
                    return $decoded;
                }
            } catch (\Exception $e) {
                \Log::warning('⚠️ Fallo RS256, intentando HS256: ' . $e->getMessage());
            }
        }
        
        // PRIORIDAD 2: Fallback a HS256 (desarrollo - clave simétrica)
        $secretKey = env('SSO_JWT_SECRET');
        if ($secretKey) {
            \Log::info('🔐 Validando JWT SSO con HS256 (desarrollo)');
            
            $decoded = JWT::decode($jwtToken, new Key($secretKey, 'HS256'));
            
            if (!isset($decoded->email)) {
                \Log::error('❌ JWT SSO sin email');
                return null;
            }
            
            \Log::info('✅ JWT SSO validado con HS256', [
                'email' => $decoded->email
            ]);
            return $decoded;
        }
        
        throw new \Exception('No hay método de validación JWT disponible (ni RS256 ni HS256)');

    } catch (\Firebase\JWT\ExpiredException $e) {
        \Log::error('❌ JWT SSO expirado: ' . $e->getMessage());
        return null;
    } catch (\Firebase\JWT\SignatureInvalidException $e) {
        \Log::error('❌ JWT SSO firma inválida: ' . $e->getMessage());
        return null;
    } catch (\Exception $e) {
        \Log::error('❌ Error validando JWT SSO: ' . $e->getMessage());
        return null;
    }
}

    /**
     * Obtener o crear usuario basado en el payload del SSO
     */
    protected function obtenerOCrearUsuario($payload): ?Usuario
    {
        try {
            // Buscar usuario por email
            $usuario = Usuario::where('email', $payload->email)->first();

            if ($usuario) {
                // Usuario existe, actualizar último acceso
                $usuario->update(['ultimo_acceso' => now()]);
                return $usuario;
            }

            // Usuario no existe, crear nuevo
            return $this->crearUsuarioDesdeSSO($payload);

        } catch (\Exception $e) {
            \Log::error('Error obteniendo/creando usuario: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Crear nuevo usuario desde el SSO
     */
    protected function crearUsuarioDesdeSSO($payload): ?Usuario
    {
        try {
            \Log::info('Creando nuevo usuario desde SSO', [
                'email' => $payload->email,
                'nombres' => $payload->nombres ?? null,
            ]);

            // Generar username único
            $username = $this->generarUsername($payload->email);

            // Crear usuario
            $usuario = Usuario::create([
                'username' => $username,
                'nombres' => $payload->nombres ?? explode('@', $payload->email)[0],
                'apellidos' => $payload->apellidos ?? '',
                'email' => $payload->email,
                'password_hash' => bcrypt(uniqid()), // Password aleatorio (no se usa en SSO)
                'activo' => true,
                'ultimo_acceso' => now(),
            ]);

            // Asignar rol
            $rol = $this->obtenerRolPorDefecto($payload);
            if ($rol) {
                $usuario->roles()->attach($rol->id);
            }

            \Log::info('Usuario creado desde SSO', ['usuario_id' => $usuario->id]);

            return $usuario;

        } catch (\Exception $e) {
            \Log::error('Error creando usuario desde SSO: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generar username único
     */
    protected function generarUsername(string $email): string
    {
        $baseUsername = explode('@', $email)[0];
        $username = $baseUsername;
        $counter = 1;

        while (Usuario::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Obtener rol por defecto basado en el payload del SSO
     */
    protected function obtenerRolPorDefecto($payload)
    {
        // Si el SSO envía el rol
        if (isset($payload->rol)) {
            $rol = \App\Models\User\Rol::where('codigo', strtoupper($payload->rol))->first();
            if ($rol) {
                return $rol;
            }
        }
    
        // TODO: Ajustar cuando se conozca la estructura real del SSO
        // Rol por defecto: ADMINISTRADOR (temporal para desarrollo)
        return \App\Models\User\Rol::where('codigo', 'ADMINISTRADOR')->first();
        
        // PRODUCCIÓN: Descomentar cuando el SSO esté listo
        // return \App\Models\User\Rol::where('codigo', 'CIUDADANO')->first();
    }

    /**
     * Completar login SSO y generar token de Sanctum
     */
    protected function completarLoginSso(Usuario $usuario)
    {
        // Actualizar ultimo acceso
        $usuario->update(['ultimo_acceso' => now()]);

        // Crear token de Sanctum con expiración de 60 minutos
        $token = $usuario->createToken('auth_token', ['*'], now()->addMinutes(60));

        // Cargar relaciones
        $usuario->load(['roles.permisos']);

        // Obtener rol principal
        $rolPrincipal = $usuario->roles->first();

        // Obtener permisos
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
            'via_sso' => true,
        ], 'Login SSO exitoso');
    }
}