<?php

namespace App\Listeners;

use App\Models\User\EventoAuditoria;
use Illuminate\Support\Str;

class LogAuditEvent
{
    /**
     * Handle animal created event
     */
    public function handleAnimalCreated($event): void
    {
        $this->logEvent(
            traceId: $event->traceId,
            userId: $event->userId,
            accion: 'crear',
            recurso: 'animal',
            modulo: 'animales',
            detalles: [
                'animal_id' => $event->animal->id,
                'nombre' => $event->animal->nombre,
                'especie' => $event->animal->especie,
                'codigo_chip' => $event->animal->codigo_chip,
            ]
        );
    }

    /**
     * Handle animal updated event
     */
    public function handleAnimalUpdated($event): void
    {
        $this->logEvent(
            traceId: $event->traceId,
            userId: $event->userId,
            accion: 'actualizar',
            recurso: 'animal',
            modulo: 'animales',
            detalles: [
                'animal_id' => $event->animal->id,
                'nombre' => $event->animal->nombre,
                'cambios' => $event->changes,
            ]
        );
    }

    /**
     * Handle adopcion aprobada event
     */
    public function handleAdopcionAprobada($event): void
    {
        $this->logEvent(
            traceId: $event->traceId,
            userId: $event->userId,
            accion: 'aprobar',
            recurso: 'adopcion',
            modulo: 'adopciones',
            detalles: [
                'solicitud_id' => $event->solicitud->id,
                'animal_id' => $event->solicitud->animal_id,
                'adoptante_id' => $event->solicitud->adoptante_id,
            ]
        );
    }

    /**
     * Handle adopcion rechazada event
     */
    public function handleAdopcionRechazada($event): void
    {
        $this->logEvent(
            traceId: $event->traceId,
            userId: $event->userId,
            accion: 'rechazar',
            recurso: 'adopcion',
            modulo: 'adopciones',
            detalles: [
                'solicitud_id' => $event->solicitud->id,
                'animal_id' => $event->solicitud->animal_id,
                'motivo' => $event->motivo,
            ]
        );
    }

    /**
     * Handle denuncia recibida event
     */
    public function handleDenunciaRecibida($event): void
    {
        $this->logEvent(
            traceId: $event->traceId,
            userId: null,
            accion: 'crear',
            recurso: 'denuncia',
            modulo: 'denuncias',
            detalles: [
                'denuncia_id' => $event->denuncia->id,
                'ticket' => $event->denuncia->numero_ticket,
                'tipo' => $event->denuncia->tipo_denuncia,
                'prioridad' => $event->denuncia->prioridad,
            ]
        );
    }

    /**
     * Handle denuncia asignada event
     */
    public function handleDenunciaAsignada($event): void
    {
        $this->logEvent(
            traceId: $event->traceId,
            userId: $event->userId,
            accion: 'asignar',
            recurso: 'denuncia',
            modulo: 'denuncias',
            detalles: [
                'denuncia_id' => $event->denuncia->id,
                'ticket' => $event->denuncia->numero_ticket,
                'funcionario_id' => $event->funcionarioId,
            ]
        );
    }

    /**
     * Handle denuncia resuelta event
     */
    public function handleDenunciaResuelta($event): void
    {
        $this->logEvent(
            traceId: $event->traceId,
            userId: $event->userId,
            accion: 'resolver',
            recurso: 'denuncia',
            modulo: 'denuncias',
            detalles: [
                'denuncia_id' => $event->denuncia->id,
                'ticket' => $event->denuncia->numero_ticket,
                'resolucion' => $event->resolucion,
            ]
        );
    }

    /**
     * Handle user logged in event
     */
    public function handleUserLoggedIn($event): void
    {
        $this->logEvent(
            traceId: $event->traceId,
            userId: $event->user->id,
            accion: 'login',
            recurso: 'sesion',
            modulo: 'autenticacion',
            detalles: [
                'usuario_id' => $event->user->id,
                'email' => $event->user->email,
            ],
            ipAddress: $event->ipAddress,
            userAgent: $event->userAgent
        );
    }

    /**
     * Handle user logged out event
     */
    public function handleUserLoggedOut($event): void
    {
        $this->logEvent(
            traceId: $event->traceId,
            userId: $event->user->id,
            accion: 'logout',
            recurso: 'sesion',
            modulo: 'autenticacion',
            detalles: [
                'usuario_id' => $event->user->id,
                'email' => $event->user->email,
            ]
        );
    }

    /**
     * Handle consulta creada event
     */
    public function handleConsultaCreada($event): void
    {
        $this->logEvent(
            traceId: $event->traceId,
            userId: $event->userId,
            accion: 'crear',
            recurso: 'consulta',
            modulo: 'veterinaria',
            detalles: [
                'consulta_id' => $event->consulta->id,
                'historial_clinico_id' => $event->consulta->historial_clinico_id,
                'tipo_consulta' => $event->consulta->tipo_consulta,
            ]
        );
    }

    /**
     * Handle vacuna aplicada event
     */
    public function handleVacunaAplicada($event): void
    {
        $this->logEvent(
            traceId: $event->traceId,
            userId: $event->userId,
            accion: 'aplicar',
            recurso: 'vacuna',
            modulo: 'veterinaria',
            detalles: [
                'vacuna_id' => $event->vacuna->id,
                'tipo_vacuna_id' => $event->vacuna->tipo_vacuna_id,
                'historial_clinico_id' => $event->vacuna->historial_clinico_id,
            ]
        );
    }

    /**
     * Handle cirugia realizada event
     */
    public function handleCirugiaRealizada($event): void
    {
        $this->logEvent(
            traceId: $event->traceId,
            userId: $event->userId,
            accion: 'realizar',
            recurso: 'cirugia',
            modulo: 'veterinaria',
            detalles: [
                'cirugia_id' => $event->cirugia->id,
                'procedimiento_id' => $event->cirugia->procedimiento_id,
                'historial_clinico_id' => $event->cirugia->historial_clinico_id,
            ]
        );
    }

    /**
     * Handle rescate registrado event
     */
    public function handleRescateRegistrado($event): void
    {
        $this->logEvent(
            traceId: $event->traceId,
            userId: $event->userId,
            accion: 'crear',
            recurso: 'rescate',
            modulo: 'denuncias',
            detalles: [
                'rescate_id' => $event->rescate->id,
                'denuncia_id' => $event->rescate->denuncia_id,
                'animal_rescatado_id' => $event->rescate->animal_rescatado_id,
            ]
        );
    }

    /**
     * Log event to local database
     */
    protected function logEvent(
        ?string $traceId,
        ?string $userId,
        string $accion,
        string $recurso,
        string $modulo,
        array $detalles,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        try {
            EventoAuditoria::create([
                'trace_id' => $traceId ?? Str::uuid()->toString(),
                'timestamp' => now(),
                'usuario_id' => $userId,
                'accion' => $accion,
                'recurso' => $recurso,
                'modulo' => $modulo,
                'detalles' => $detalles,
                'ip_address' => $ipAddress ?? request()->ip(),
                'user_agent' => $userAgent ?? request()->userAgent(),
                'resultado' => 'exitoso',
            ]);
        } catch (\Exception $e) {
            // Log to file if database fails
            \Log::error('Failed to save audit event', [
                'error' => $e->getMessage(),
                'accion' => $accion,
                'recurso' => $recurso,
                'modulo' => $modulo,
            ]);
        }
    }
}
