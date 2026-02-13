<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\User\EventoAuditoria;
use Illuminate\Http\Request;

class AuditoriaController extends BaseController
{
    /**
     * Listar eventos de auditoría con filtros.
     * GET /api/v1/auditoria
     */
    public function index(Request $request)
    {
        try {
            $query = EventoAuditoria::with('usuario:id,nombres,apellidos');

            if ($request->filled('usuario_id')) {
                $query->porUsuario($request->usuario_id);
            }

            if ($request->filled('accion')) {
                $query->where('accion', $request->accion);
            }

            if ($request->filled('resultado')) {
                $query->where('resultado', $request->resultado);
            }

            if ($request->filled('fecha_inicio')) {
                $query->where('timestamp', '>=', $request->fecha_inicio);
            }

            if ($request->filled('fecha_fin')) {
                $query->where('timestamp', '<=', $request->fecha_fin . ' 23:59:59');
            }

            if ($request->filled('busqueda')) {
                $busqueda = $request->busqueda;
                $query->where(function ($q) use ($busqueda) {
                    $q->where('accion', 'like', "%{$busqueda}%")
                      ->orWhere('recurso', 'like', "%{$busqueda}%")
                      ->orWhereHas('usuario', function ($uq) use ($busqueda) {
                          $uq->where('nombres', 'like', "%{$busqueda}%")
                            ->orWhere('apellidos', 'like', "%{$busqueda}%");
                      });
                });
            }

            $eventos = $query->orderBy('timestamp', 'desc')
                ->paginate($request->get('per_page', 20));

            return $this->successResponse($eventos);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar eventos de auditoría');
        }
    }

    /**
     * Obtener acciones únicas para filtro.
     * GET /api/v1/auditoria/acciones
     */
    public function acciones()
    {
        try {
            $acciones = EventoAuditoria::select('accion')
                ->distinct()
                ->orderBy('accion')
                ->pluck('accion');

            return $this->successResponse($acciones);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener acciones');
        }
    }

    /**
     * Obtener usuarios únicos que tienen eventos para filtro.
     * GET /api/v1/auditoria/usuarios
     */
    public function usuarios()
    {
        try {
            $usuarios = EventoAuditoria::with('usuario:id,nombres,apellidos')
                ->select('usuario_id')
                ->whereNotNull('usuario_id')
                ->distinct()
                ->get()
                ->pluck('usuario')
                ->filter()
                ->values();

            return $this->successResponse($usuarios);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener usuarios');
        }
    }
}
