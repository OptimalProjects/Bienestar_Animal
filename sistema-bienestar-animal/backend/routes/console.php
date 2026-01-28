<?php

use App\Jobs\CheckLowInventoryStock;
use App\Jobs\CleanupOldAuditLogs;
use App\Jobs\GenerateDailyReport;
use App\Jobs\ProcessDenunciaUrgency;
use App\Jobs\ProcessVaccineReminders;
use App\Jobs\SendAdoptionReminder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Tareas programadas del sistema de bienestar animal.
| Ejecutar con: php artisan schedule:run
|
*/

// Procesar urgencia de denuncias cada 15 minutos
Schedule::job(new ProcessDenunciaUrgency)->everyFifteenMinutes()
    ->name('process-denuncia-urgency')
    ->withoutOverlapping()
    ->onOneServer();

// Recordatorios de adopciones diariamente a las 8:00 AM
Schedule::job(new SendAdoptionReminder)->dailyAt('08:00')
    ->name('send-adoption-reminder')
    ->withoutOverlapping()
    ->onOneServer();

// Recordatorios de vacunas diariamente a las 9:00 AM
Schedule::job(new ProcessVaccineReminders)->dailyAt('09:00')
    ->name('process-vaccine-reminders')
    ->withoutOverlapping()
    ->onOneServer();

// Recordatorios de visitas de seguimiento (1 día antes + día de la visita) a las 8:30 AM
Schedule::command('visitas:recordatorios --dias=1 --incluir-hoy')->dailyAt('08:30')
    ->name('send-visitas-recordatorios')
    ->withoutOverlapping()
    ->onOneServer();

// Generar reporte diario a las 2:00 AM
Schedule::job(new GenerateDailyReport)->dailyAt('02:00')
    ->name('generate-daily-report')
    ->withoutOverlapping()
    ->onOneServer();

// Verificar inventario cada 6 horas
Schedule::job(new CheckLowInventoryStock)->everySixHours()
    ->name('check-inventory-stock')
    ->withoutOverlapping()
    ->onOneServer();

// Limpiar logs de auditoria antiguos mensualmente
Schedule::job(new CleanupOldAuditLogs)->monthly()
    ->name('cleanup-audit-logs')
    ->withoutOverlapping()
    ->onOneServer();
