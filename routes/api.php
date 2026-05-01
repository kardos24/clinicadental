<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

// ─── Rutas públicas (sin token) ───────────────────────────────────────────────
Route::post('/login',  [ApiController::class, 'login']);

// ─── Rutas protegidas con Sanctum ─────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiController::class, 'logout']);

    // Dispositivo / token FCM
    Route::post('/dispositivo/token', [ApiController::class, 'registrarToken']);

    // Clientes (gestor)
    Route::get('/clientes',           [ApiController::class, 'clientes']);
    Route::get('/clientes/{cliente}', [ApiController::class, 'cliente']);

    // Citas
    Route::get('/mis-citas',                       [ApiController::class, 'misCitas']);
    Route::get('/citas/mes',                       [ApiController::class, 'citasGestor']);
    Route::post('/citas',                          [ApiController::class, 'crearCita']);
    Route::patch('/citas/{cita}/estado',           [ApiController::class, 'actualizarEstadoCita']);

    // Historial
    Route::get('/clientes/{cliente}/historial', [ApiController::class, 'historial']);
});
