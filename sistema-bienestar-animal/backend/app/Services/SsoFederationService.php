<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use App\Models\User\Usuario;
use App\Models\User\Rol;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class SsoFederationService
{
    private string $publicKey;
    private string $algorithm;
    private string $issuer;
    private string $audience;

    public function __construct()
    {
        $this->algorithm = config('sso.jwt.algorithm');
        $this->issuer = config('sso.jwt.issuer');
        $this->audience = config('sso.jwt.audience');
        $this->loadPublicKey();
    }

    /**
     * Carga la clave pública desde el archivo
     */
    private function loadPublicKey(): void
    {
        $keyPath = config('sso.jwt.public_key');

        if (!file_exists($keyPath)) {
            throw new Exception("Clave pública SSO no encontrada en: {$keyPath}");
        }

        $this->publicKey = file_get_contents($keyPath);

        if (empty($this->publicKey)) {
            throw new Exception("La clave pública SSO está vacía");
        }
    }

    /**
     * Decodifica y valida el token JWT del SCI
     *
     * @param string $token Token JWT recibido
     * @return object Payload decodificado
     * @throws Exception Si el token es inválido
     */
    public function decodeAndValidateToken(string $token): object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->publicKey, $this->algorithm));

            // Validar issuer (emisor)
            if (isset($decoded->iss) && $decoded->iss !== $this->issuer) {
                throw new Exception("Emisor del token no válido");
            }

            // Validar audience (audiencia)
            if (isset($decoded->aud)) {
                $audiences = is_array($decoded->aud) ? $decoded->aud : [$decoded->aud];
                if (!in_array($this->audience, $audiences)) {
                    throw new Exception("Audiencia del token no válida");
                }
            }

            Log::info('Token SSO validado correctamente', [
                'sub' => $decoded->sub ?? null,
                'email' => $decoded->email ?? null,
            ]);

            return $decoded;

        } catch (ExpiredException $e) {
            Log::warning('Token SSO expirado', ['error' => $e->getMessage()]);
            throw new Exception("El token ha expirado");
        } catch (SignatureInvalidException $e) {
            Log::warning('Firma de token SSO inválida', ['error' => $e->getMessage()]);
            throw new Exception("La firma del token no es válida");
        } catch (Exception $e) {
            Log::error('Error al validar token SSO', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Busca o crea un usuario local basado en los datos del token SSO
     *
     * @param object $payload Payload del JWT decodificado
     * @return Usuario Usuario local encontrado o creado
     */
    public function findOrCreateUser(object $payload): Usuario
    {
        $email = $payload->email ?? null;
        $sub = $payload->sub ?? null;

        if (!$email) {
            throw new Exception("El token no contiene email");
        }

        return DB::transaction(function () use ($payload, $email, $sub) {
            // Buscar usuario existente por email
            $usuario = Usuario::where('email', $email)->first();

            if ($usuario) {
                // Actualizar datos del usuario existente
                $this->updateUserFromPayload($usuario, $payload);
                return $usuario;
            }

            // Crear nuevo usuario desde SSO
            $usuario = $this->createUserFromPayload($payload);

            // Asignar rol basado en el payload
            $this->assignRoleFromPayload($usuario, $payload);

            return $usuario;
        });
    }

    /**
     * Actualiza un usuario existente con datos del payload SSO
     */
    private function updateUserFromPayload(Usuario $usuario, object $payload): void
    {
        $updates = [
            'ultimo_acceso' => now(),
            'origen_autenticacion' => 'sso',
        ];

        // Actualizar nombres si vienen en el payload
        if (isset($payload->name)) {
            $nameParts = $this->parseFullName($payload->name);
            $updates['nombres'] = $nameParts['nombres'];
            $updates['apellidos'] = $nameParts['apellidos'];
        }

        if (isset($payload->nombres)) {
            $updates['nombres'] = $payload->nombres;
        }

        if (isset($payload->apellidos)) {
            $updates['apellidos'] = $payload->apellidos;
        }

        $usuario->update($updates);

        // Sincronizar roles si vienen en el payload
        if (isset($payload->roles) || isset($payload->rol)) {
            $this->assignRoleFromPayload($usuario, $payload);
        }

        Log::info('Usuario SSO actualizado', ['usuario_id' => $usuario->id, 'email' => $usuario->email]);
    }

    /**
     * Crea un nuevo usuario desde el payload SSO
     */
    private function createUserFromPayload(object $payload): Usuario
    {
        $email = $payload->email;
        $nameParts = $this->parseFullName($payload->name ?? $payload->nombres ?? 'Usuario');

        $usuario = Usuario::create([
            'id' => Str::uuid(),
            'username' => $this->generateUsername($email),
            'email' => $email,
            'password_hash' => bcrypt(Str::random(32)), // Password aleatorio (no se usará)
            'nombres' => $payload->nombres ?? $nameParts['nombres'],
            'apellidos' => $payload->apellidos ?? $nameParts['apellidos'],
            'origen_autenticacion' => 'sso',
            'activo' => true,
            'ultimo_acceso' => now(),
        ]);

        Log::info('Usuario SSO creado', ['usuario_id' => $usuario->id, 'email' => $email]);

        return $usuario;
    }

    /**
     * Asigna rol al usuario basado en el payload del SSO
     */
    private function assignRoleFromPayload(Usuario $usuario, object $payload): void
    {
        $roleMapping = config('sso.role_mapping');
        $ssoRoles = [];

        // El rol puede venir como 'rol', 'roles', o 'role'
        if (isset($payload->roles)) {
            $ssoRoles = is_array($payload->roles) ? $payload->roles : [$payload->roles];
        } elseif (isset($payload->rol)) {
            $ssoRoles = is_array($payload->rol) ? $payload->rol : [$payload->rol];
        } elseif (isset($payload->role)) {
            $ssoRoles = is_array($payload->role) ? $payload->role : [$payload->role];
        }

        if (empty($ssoRoles)) {
            Log::warning('Token SSO sin roles definidos', ['email' => $payload->email]);
            return;
        }

        foreach ($ssoRoles as $ssoRole) {
            $localRoleCode = $roleMapping[$ssoRole] ?? $ssoRole;
            $rol = Rol::where('codigo', $localRoleCode)->where('activo', true)->first();

            if ($rol) {
                // Verificar si ya tiene el rol asignado
                $existingAssignment = $usuario->roles()->where('rol_id', $rol->id)->exists();

                if (!$existingAssignment) {
                    $usuario->roles()->attach($rol->id, [
                        'id' => Str::uuid(),
                        'fecha_asignacion' => now(),
                        'activo' => true,
                    ]);

                    Log::info('Rol asignado a usuario SSO', [
                        'usuario_id' => $usuario->id,
                        'rol' => $localRoleCode,
                    ]);
                }
            } else {
                Log::warning('Rol del SSO no encontrado localmente', [
                    'sso_role' => $ssoRole,
                    'mapped_role' => $localRoleCode,
                ]);
            }
        }
    }

    /**
     * Genera un username único basado en el email
     */
    private function generateUsername(string $email): string
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
     * Parsea un nombre completo en nombres y apellidos
     */
    private function parseFullName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName));

        if (count($parts) >= 2) {
            $midpoint = ceil(count($parts) / 2);
            return [
                'nombres' => implode(' ', array_slice($parts, 0, $midpoint)),
                'apellidos' => implode(' ', array_slice($parts, $midpoint)),
            ];
        }

        return [
            'nombres' => $fullName,
            'apellidos' => '',
        ];
    }
}
