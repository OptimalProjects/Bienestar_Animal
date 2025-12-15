<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SSO Habilitado
    |--------------------------------------------------------------------------
    |
    | Determina si la autenticación SSO está habilitada. Cuando está activo,
    | los usuarios internos deben autenticarse a través del SCI.
    |
    */

    'enabled' => env('SSO_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Configuración JWT
    |--------------------------------------------------------------------------
    |
    | Configuración para la validación de tokens JWT emitidos por el SCI.
    | La clave pública se usa para verificar la firma RS256.
    |
    */

    'jwt' => [
        'public_key' => env('SSO_JWT_PUBLIC_KEY', storage_path('keys/jwt_public.pem')),
        'algorithm' => env('SSO_JWT_ALGO', 'RS256'),
        'issuer' => env('SSO_ISSUER', 'https://sci.cali.gov.co'),
        'audience' => env('SSO_AUDIENCE', 'bienestar-animal'),
    ],

    /*
    |--------------------------------------------------------------------------
    | URLs del SCI
    |--------------------------------------------------------------------------
    |
    | URLs de los servicios del Sistema Central de Interoperabilidad.
    |
    */

    'sci' => [
        'auth_url' => env('SCI_AUTH_URL', 'https://sci.cali.gov.co/auth/api/v1'),
        'login_url' => env('SCI_LOGIN_URL', 'https://sci.cali.gov.co/auth/login'),
        'client_id' => env('SCI_CLIENT_ID', 'bienestar-animal'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapeo de Roles SCI -> Sistema Local
    |--------------------------------------------------------------------------
    |
    | Mapea los códigos de rol que vienen del SCI a los roles locales
    | del sistema de Bienestar Animal.
    |
    */

    'role_mapping' => [
        'ADMIN_BIENESTAR' => 'ADMIN',
        'DIRECTOR_BIENESTAR' => 'DIRECTOR',
        'COORDINADOR_BIENESTAR' => 'COORDINADOR',
        'VETERINARIO_BIENESTAR' => 'VETERINARIO',
        'AUXILIAR_BIENESTAR' => 'AUXILIAR_VET',
        'OPERADOR_BIENESTAR' => 'OPERADOR',
        'EVALUADOR_BIENESTAR' => 'EVALUADOR',
        // Mapeo directo si los códigos coinciden
        'ADMIN' => 'ADMIN',
        'DIRECTOR' => 'DIRECTOR',
        'COORDINADOR' => 'COORDINADOR',
        'VETERINARIO' => 'VETERINARIO',
        'AUXILIAR_VET' => 'AUXILIAR_VET',
        'OPERADOR' => 'OPERADOR',
        'EVALUADOR' => 'EVALUADOR',
    ],

];
