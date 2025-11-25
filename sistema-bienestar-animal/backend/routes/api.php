<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Animal\AnimalController;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
| Base URL: /api/v1
| Autenticación: Bearer Token (Sanctum)
*/

Route::prefix('v1')->group(function () {
    
    // ============================================
    // RUTAS PÚBLICAS (Sin autenticación)
    // ⚠️ TEMPORALMENTE COMENTADAS - AuthController no existe aún
    // ============================================
    
    // Route::post('/login', [AuthController::class, 'login']);
    // Route::post('/register', [AuthController::class, 'register']);
    
    // ============================================
    // RUTAS PROTEGIDAS (Requieren autenticación)
    // ⚠️ TEMPORALMENTE DESHABILITADO PARA PRUEBAS
    // ============================================
    
    // Route::middleware(['auth:sanctum'])->group(function () {
        
        // Autenticación
        // Route::post('/logout', [AuthController::class, 'logout']);
        // Route::get('/me', [AuthController::class, 'me']);
        
        // ============================================
        // MÓDULO: ANIMALES
        // ============================================
        Route::prefix('animals')->group(function () {
            Route::get('/statistics', [AnimalController::class, 'statistics']);
            Route::get('/', [AnimalController::class, 'index']);
            Route::post('/', [AnimalController::class, 'store']);
            Route::get('/{id}', [AnimalController::class, 'show']);
            Route::put('/{id}', [AnimalController::class, 'update']);
            Route::delete('/{id}', [AnimalController::class, 'destroy']);
        });
        
        // ============================================
        // MÓDULO: VETERINARIA
        // ⚠️ TEMPORALMENTE COMENTADO - Controladores no existen aún
        // ============================================
        /*
        Route::prefix('veterinaria')->group(function () {
            // Consultas
            Route::apiResource('consultas', ConsultaController::class);
            
            // Vacunas
            Route::apiResource('vacunas', VacunaController::class);
            Route::get('tipos-vacunas', [TipoVacunaController::class, 'index']);
            
            // Tratamientos
            Route::apiResource('tratamientos', TratamientoController::class);
            
            // Cirugías
            Route::apiResource('cirugias', CirugiaController::class);
        });
        */
        
        // ============================================
        // MÓDULO: ADOPCIONES
        // ⚠️ TEMPORALMENTE COMENTADO - Controladores no existen aún
        // ============================================
        /*
        Route::prefix('adopciones')->group(function () {
            Route::apiResource('adoptantes', AdoptanteController::class);
            Route::apiResource('adopciones', AdopcionController::class);
            
            // Acciones especiales
            Route::post('adopciones/{id}/aprobar', [AdopcionController::class, 'aprobar']);
            Route::post('adopciones/{id}/rechazar', [AdopcionController::class, 'rechazar']);
            
            // Visitas domiciliarias
            Route::apiResource('visitas', VisitaDomiciliariaController::class);
        });
        */
        
        // ============================================
        // MÓDULO: DENUNCIAS
        // ⚠️ TEMPORALMENTE COMENTADO - Controladores no existen aún
        // ============================================
        /*
        Route::prefix('denuncias')->group(function () {
            Route::apiResource('denuncias', DenunciaController::class);
            Route::post('denuncias/{id}/asignar', [DenunciaController::class, 'asignar']);
            
            Route::apiResource('rescates', RescateController::class);
        });
        */
        
        // ============================================
        // MÓDULO: ADMINISTRACIÓN
        // ⚠️ TEMPORALMENTE COMENTADO - Controladores no existen aún
        // ============================================
        /*
        Route::prefix('admin')->group(function () {  // ->middleware(['role:admin']) ⚠️ Comentado temporalmente
            Route::get('/dashboard', [AdminController::class, 'dashboard']);
            Route::get('/reportes', [AdminController::class, 'reportes']);
            Route::apiResource('inventarios', InventarioController::class);
            Route::apiResource('indicadores', IndicadorController::class);
        });
        */
        
        // ============================================
        // MÓDULO: AUDITORÍA
        // ⚠️ TEMPORALMENTE COMENTADO - Controladores no existen aún
        // ============================================
        /*
        Route::get('/auditoria', [AuditoriaController::class, 'index']);
            // ->middleware(['role:admin']);  // ⚠️ Comentado temporalmente
        */
    // });  // ⚠️ Comentado temporalmente el cierre de auth:sanctum
});