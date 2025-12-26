<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Animal\AnimalController;
use App\Http\Controllers\Api\V1\Veterinary\ConsultaController;
use App\Http\Controllers\Api\V1\Veterinary\VacunaController;
use App\Http\Controllers\Api\V1\Veterinary\CirugiaController;
use App\Http\Controllers\Api\V1\Veterinary\HistorialClinicoController;
use App\Http\Controllers\Api\V1\Adoptions\AdopcionController;
use App\Http\Controllers\Api\V1\Adoptions\VisitaSeguimientoController;
use App\Http\Controllers\Api\V1\Complaints\DenunciaController;
use App\Http\Controllers\Api\V1\Complaints\RescateController;
use App\Http\Controllers\Api\V1\Admin\ReporteController;
use App\Http\Controllers\Api\V1\Admin\UsuarioController;
use App\Http\Controllers\Api\V1\Admin\InventarioController;
use App\Http\Controllers\Api\V1\HealthController;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
| Base URL: /api/v1
| Autenticacion: Bearer Token (Sanctum)
*/

Route::prefix('v1')->group(function () {

    // ============================================
    // RUTAS PUBLICAS (Sin autenticacion)
    // ============================================

    // Health check
    Route::get('/health', [HealthController::class, 'check']);

    // Autenticacion
    Route::post('/auth/login', [LoginController::class, 'login']);
    Route::post('/auth/mfa/verify', [LoginController::class, 'verificarMfa']);

    // Catalogo de adopcion (publico) - Solo animales en estado 'en_adopcion' y saludables
    Route::get('/animals/catalogo-adopcion', [AnimalController::class, 'catalogoAdopcion']);

    // Denuncias publicas
    Route::post('/denuncias', [DenunciaController::class, 'store']);
    Route::get('/denuncias/consultar/{ticket}', [DenunciaController::class, 'consultarTicket']);

    // Solicitud de adopcion (publico)
    Route::post('/adopciones/solicitud', [AdopcionController::class, 'store']);

    // Consulta publica de estado de adopcion (sin autenticacion)
    Route::get('/adopciones/consulta-publica', [AdopcionController::class, 'consultaPublica']);
    Route::post('/adopciones/{id}/contrato/firmar-publico', [AdopcionController::class, 'firmarContratoPublico']);

    // ============================================
    // RUTAS PROTEGIDAS (Requieren autenticacion)
    // ============================================

    Route::middleware(['auth:sanctum'])->group(function () {

        // Autenticacion
        Route::post('/auth/logout', [LoginController::class, 'logout']);
        Route::post('/auth/refresh', [LoginController::class, 'refresh']);
        Route::get('/auth/me', [LoginController::class, 'me']);

        // ============================================
        // MODULO: ANIMALES
        // ============================================
        Route::prefix('animals')->group(function () {
            Route::get('/statistics', [AnimalController::class, 'statistics']);
            Route::get('/', [AnimalController::class, 'index']);
            Route::post('/', [AnimalController::class, 'store']);
            Route::get('/{id}', [AnimalController::class, 'show']);
            Route::put('/{id}', [AnimalController::class, 'update']);
            Route::delete('/{id}', [AnimalController::class, 'destroy']);

            // Historial clinico
            Route::get('/{animalId}/historial-clinico', [HistorialClinicoController::class, 'show']);
            Route::put('/{animalId}/historial-clinico', [HistorialClinicoController::class, 'update']);
            Route::post('/{animalId}/chip', [HistorialClinicoController::class, 'registrarChip']);
        });

        // Busqueda por chip
        Route::get('/historial-clinico/buscar-chip/{chip}', [HistorialClinicoController::class, 'buscarPorChip']);

        // ============================================
        // MODULO: VETERINARIA
        // ============================================
        Route::prefix('consultas')->group(function () {
            Route::get('/estadisticas', [ConsultaController::class, 'estadisticas']);
            Route::get('/hoy', [ConsultaController::class, 'consultasHoy']);
            Route::get('/pendientes', [ConsultaController::class, 'pendientes']);
            Route::get('/', [ConsultaController::class, 'index']);
            Route::post('/', [ConsultaController::class, 'store']);
            Route::get('/{id}', [ConsultaController::class, 'show']);
        });

        Route::prefix('vacunas')->group(function () {
            Route::get('/tipos', [VacunaController::class, 'tiposVacuna']);
            Route::get('/veterinarios', [VacunaController::class, 'veterinarios']);
            Route::get('/proximas', [VacunaController::class, 'proximasVacunas']);
            Route::get('/animal/{animalId}', [VacunaController::class, 'vacunasAnimal']);
            Route::get('/', [VacunaController::class, 'index']);
            Route::post('/', [VacunaController::class, 'store']);
            Route::get('/{id}', [VacunaController::class, 'show']);
        });

        Route::prefix('cirugias')->group(function () {
            Route::get('/procedimientos', [CirugiaController::class, 'procedimientos']);
            Route::get('/estadisticas', [CirugiaController::class, 'estadisticas']);
            Route::get('/animal/{animalId}', [CirugiaController::class, 'cirugiasAnimal']);
            Route::get('/', [CirugiaController::class, 'index']);
            Route::post('/', [CirugiaController::class, 'store']);
            Route::get('/{id}', [CirugiaController::class, 'show']);
            Route::put('/{id}', [CirugiaController::class, 'update']);
        });

        // ============================================
        // MODULO: ADOPCIONES
        // ============================================
        Route::prefix('adopciones')->group(function () {
            Route::get('/estadisticas', [AdopcionController::class, 'estadisticas']);
            Route::get('/pendientes', [AdopcionController::class, 'pendientes']);
            Route::get('/', [AdopcionController::class, 'index']);
            Route::post('/', [AdopcionController::class, 'store']);
            Route::get('/{id}', [AdopcionController::class, 'show']);
            Route::put('/{id}/evaluar', [AdopcionController::class, 'evaluar']);
            Route::get('/{id}/contrato', [AdopcionController::class, 'contrato']);
            Route::get('/{id}/contrato/descargar', [AdopcionController::class, 'descargarContrato']);
            Route::post('/{id}/contrato/firmar', [AdopcionController::class, 'firmarContrato']);
            Route::get('/{id}/estado-contrato', [AdopcionController::class, 'estadoContrato']);
        });

        Route::prefix('visitas-seguimiento')->group(function () {
            Route::get('/pendientes', [VisitaSeguimientoController::class, 'pendientes']);
            Route::get('/requieren-visita', [VisitaSeguimientoController::class, 'requierenVisita']);
            Route::get('/adopcion/{adopcionId}', [VisitaSeguimientoController::class, 'visitasPorAdopcion']);
            Route::get('/', [VisitaSeguimientoController::class, 'index']);
            Route::post('/', [VisitaSeguimientoController::class, 'store']);
            Route::get('/{id}', [VisitaSeguimientoController::class, 'show']);
            Route::post('/{id}/registrar', [VisitaSeguimientoController::class, 'registrar']);
            Route::put('/{id}/reprogramar', [VisitaSeguimientoController::class, 'reprogramar']);
            Route::delete('/{id}', [VisitaSeguimientoController::class, 'destroy']);
        });

        // ============================================
        // MODULO: DENUNCIAS
        // ============================================
        Route::prefix('denuncias')->group(function () {
            Route::get('/estadisticas', [DenunciaController::class, 'estadisticas']);
            Route::get('/urgentes', [DenunciaController::class, 'urgentes']);
            Route::get('/mis-asignaciones', [DenunciaController::class, 'misAsignaciones']);
            Route::get('/mapa-calor', [DenunciaController::class, 'mapaCalor']);
            Route::get('/', [DenunciaController::class, 'index']);
            Route::get('/{id}', [DenunciaController::class, 'show']);
            Route::put('/{id}/asignar', [DenunciaController::class, 'asignar']);
            Route::put('/{id}/estado', [DenunciaController::class, 'actualizarEstado']);
        });

        Route::prefix('rescates')->group(function () {
            Route::get('/estadisticas', [RescateController::class, 'estadisticas']);
            Route::get('/', [RescateController::class, 'index']);
            Route::post('/', [RescateController::class, 'store']);
            Route::get('/{id}', [RescateController::class, 'show']);
            Route::put('/{id}', [RescateController::class, 'update']);
            Route::put('/{id}/vincular-animal', [RescateController::class, 'vincularAnimal']);
        });

        // ============================================
        // MODULO: ADMINISTRACION
        // ============================================

        // Usuarios
        Route::prefix('usuarios')->group(function () {
            Route::get('/roles', [UsuarioController::class, 'roles']);
            Route::get('/', [UsuarioController::class, 'index']);
            Route::post('/', [UsuarioController::class, 'store']);
            Route::get('/{id}', [UsuarioController::class, 'show']);
            Route::put('/{id}', [UsuarioController::class, 'update']);
            Route::put('/{id}/password', [UsuarioController::class, 'cambiarPassword']);
            Route::put('/{id}/toggle-activo', [UsuarioController::class, 'toggleActivo']);
            Route::delete('/{id}', [UsuarioController::class, 'destroy']);
        });

        // Inventario
        Route::prefix('inventario')->group(function () {
            Route::get('/estadisticas', [InventarioController::class, 'estadisticas']);
            Route::get('/stock-bajo', [InventarioController::class, 'stockBajo']);
            Route::get('/proximos-vencer', [InventarioController::class, 'proximosVencer']);
            Route::get('/vencidos', [InventarioController::class, 'vencidos']);
            Route::get('/verificar-stock', [InventarioController::class, 'verificarStock']);
            Route::get('/insumos', [InventarioController::class, 'insumos']);
            Route::get('/', [InventarioController::class, 'index']);
            Route::post('/', [InventarioController::class, 'store']);
            Route::put('/{id}', [InventarioController::class, 'update']);
            Route::post('/{id}/entrada', [InventarioController::class, 'registrarEntrada']);
            Route::post('/{id}/salida', [InventarioController::class, 'registrarSalida']);
        });

        // Reportes
        Route::prefix('reportes')->group(function () {
            Route::get('/dashboard', [ReporteController::class, 'dashboard']);
            Route::get('/indicadores', [ReporteController::class, 'indicadores']);
            Route::post('/indicadores/{indicadorId}/punto', [ReporteController::class, 'registrarIndicador']);
            Route::get('/periodo', [ReporteController::class, 'reportePeriodo']);
            Route::get('/exportar', [ReporteController::class, 'exportar']);
        });

    }); // Fin middleware auth:sanctum

});
