<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CitaApiController;
use App\Http\Controllers\Api\ClienteApiController;
use App\Http\Controllers\Api\DispositivoApiController;
use App\Http\Controllers\Api\HistorialApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::post('/dispositivo/token', [DispositivoApiController::class, 'registrarToken']);

    Route::get('/mis-citas', [CitaApiController::class, 'index']);
    Route::post('/citas',    [CitaApiController::class, 'store']);

    // Gestor only
    Route::middleware('gestor')->group(function () {
        Route::get('/clientes',    [ClienteApiController::class, 'index']);
        Route::post('/clientes',   [ClienteApiController::class, 'store']);
        Route::put('/clientes/{cliente}',    [ClienteApiController::class, 'update']);
        Route::delete('/clientes/{cliente}', [ClienteApiController::class, 'destroy']);

        Route::post('/clientes/{cliente}/dentadura', [ClienteApiController::class, 'actualizarDentadura']);

        Route::post('/clientes/{cliente}/historial',  [HistorialApiController::class, 'store']);
        Route::put('/historial/{historial}',          [HistorialApiController::class, 'update']);
        Route::delete('/historial/{historial}',       [HistorialApiController::class, 'destroy']);

        Route::get('/citas/mes',                [CitaApiController::class, 'mes']);
        Route::put('/citas/{cita}',             [CitaApiController::class, 'update']);
        Route::delete('/citas/{cita}',          [CitaApiController::class, 'destroy']);
        Route::patch('/citas/{cita}/estado',    [CitaApiController::class, 'actualizarEstado']);
    });

    // Gestor o propietario del cliente
    Route::middleware('gestor.o.propietario')->group(function () {
        Route::get('/clientes/{cliente}',           [ClienteApiController::class, 'show']);
        Route::get('/clientes/{cliente}/historial', [ClienteApiController::class, 'historial']);
        Route::get('/clientes/{cliente}/dentadura', [ClienteApiController::class, 'dentadura']);
    });
});
