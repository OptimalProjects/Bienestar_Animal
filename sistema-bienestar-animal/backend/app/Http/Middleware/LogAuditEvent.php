<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User\EventoAuditoria;

class LogAuditEvent
{
    /**
     * Acciones que siempre deben auditarse.
     */
    protected array $auditableActions = [
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ];

    /**
     * Rutas sensibles que siempre deben auditarse (incluyendo GET).
     */
    protected array $sensitiveRoutes = [
        'auth/login',
        'auth/logout',
        'usuarios',
        'denuncias',
        'adopciones',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // FIX: Excluir rutas SSO de auditoría (falta campo 'modulo')
        if (str_contains($request->path(), '/sso/')) {
            return $next($request);
        }

        // Generar trace ID si no existe
        $traceId = $request->header('X-Trace-ID') ?? $this->generateTraceId();
        $request->headers->set('X-Trace-ID', $traceId);

        // Procesar la solicitud
        $response = $next($request);

        // Auditar si es necesario
        if ($this->shouldAudit($request)) {
            $this->logAuditEvent($request, $response, $traceId);
        }

        // Agregar trace ID a la respuesta
        $response->headers->set('X-Trace-ID', $traceId);

        return $response;
    }

    /**
     * Determinar si la solicitud debe ser auditada.
     */
    protected function shouldAudit(Request $request): bool
    {
        // Siempre auditar acciones de modificacion
        if (in_array($request->method(), $this->auditableActions)) {
            return true;
        }

        // Auditar rutas sensibles
        $path = $request->path();
        foreach ($this->sensitiveRoutes as $route) {
            if (str_contains($path, $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Registrar evento de auditoria.
     */
    protected function logAuditEvent(Request $request, Response $response, string $traceId): void
    {
        try {
            $user = $request->user();

            $evento = [
                'trace_id' => $traceId,
                'usuario_id' => $user?->id,
                'accion' => $this->determineAction($request),
                'recurso' => $this->determineResource($request),
                'recurso_id' => $this->extractResourceId($request),
                'metodo_http' => $request->method(),
                'ruta' => $request->path(),
                'ip_origen' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'datos_request' => $this->sanitizeRequestData($request),
                'codigo_respuesta' => $response->getStatusCode(),
                'exitoso' => $response->isSuccessful(),
            ];

            // Intentar guardar en BD
            EventoAuditoria::create($evento);

        } catch (\Exception $e) {
            // Si falla la BD, al menos loguear
            Log::channel('audit')->info('Evento de auditoria', [
                'trace_id' => $traceId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Determinar la accion realizada.
     */
    protected function determineAction(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        // Acciones especiales basadas en la ruta
        if (str_contains($path, 'login')) {
            return 'LOGIN';
        }
        if (str_contains($path, 'logout')) {
            return 'LOGOUT';
        }
        if (str_contains($path, 'evaluar')) {
            return 'EVALUAR';
        }
        if (str_contains($path, 'asignar')) {
            return 'ASIGNAR';
        }

        // Mapeo por metodo HTTP
        return match ($method) {
            'POST' => 'CREAR',
            'PUT', 'PATCH' => 'ACTUALIZAR',
            'DELETE' => 'ELIMINAR',
            default => 'CONSULTAR',
        };
    }

    /**
     * Determinar el recurso afectado.
     */
    protected function determineResource(Request $request): string
    {
        $path = $request->path();
        $segments = explode('/', $path);

        // Buscar el segmento relevante (despues de 'v1')
        $v1Index = array_search('v1', $segments);
        if ($v1Index !== false && isset($segments[$v1Index + 1])) {
            return strtoupper($segments[$v1Index + 1]);
        }

        return strtoupper($segments[0] ?? 'DESCONOCIDO');
    }

    /**
     * Extraer ID del recurso de la URL.
     */
    protected function extractResourceId(Request $request): ?string
    {
        $segments = explode('/', $request->path());

        // Buscar el primer segmento que parezca un UUID o ID
        foreach ($segments as $segment) {
            // UUID format
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $segment)) {
                return $segment;
            }
            // Numeric ID
            if (preg_match('/^\d+$/', $segment) && strlen($segment) > 0) {
                return $segment;
            }
        }

        return null;
    }

    /**
     * Sanitizar datos de la solicitud (eliminar datos sensibles).
     */
    protected function sanitizeRequestData(Request $request): ?array
    {
        $data = $request->except([
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_key',
            'secret',
        ]);

        // Limitar tamano
        $json = json_encode($data);
        if (strlen($json) > 5000) {
            return ['_truncated' => true, '_size' => strlen($json)];
        }

        return $data;
    }

    /**
     * Generar trace ID unico.
     */
    protected function generateTraceId(): string
    {
        return sprintf(
            '%s-%s-%s',
            date('Ymd'),
            substr(md5(uniqid('', true)), 0, 8),
            substr(md5(random_bytes(8)), 0, 8)
        );
    }
}
