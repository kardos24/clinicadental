<?php

use App\Models\Cita;
use App\Services\NotificacionService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Tarea programada: Enviar recordatorios de citas 24h antes
| Configura en Webempresa un cron job:
|   * * * * * php /ruta/a/tu/proyecto/artisan schedule:run >> /dev/null 2>&1
|--------------------------------------------------------------------------
*/
Schedule::call(function () {
    $manana = now()->addDay();

    $citas = Cita::with('cliente.user')
        ->whereBetween('fecha_hora', [
            $manana->copy()->startOfDay(),
            $manana->copy()->endOfDay(),
        ])
        ->whereIn('estado', ['confirmada', 'pendiente'])
        ->where('recordatorio_enviado', false)
        ->get();

    $servicio = app(NotificacionService::class);

    foreach ($citas as $cita) {
        $servicio->recordatorioCita($cita);
    }

    logger("Recordatorios enviados: {$citas->count()} citas para mañana.");
})->dailyAt('09:00')->name('recordatorios-citas')->withoutOverlapping();
