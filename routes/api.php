<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CitaApiController;
use App\Http\Controllers\Api\ClienteApiController;
use App\Http\Controllers\Api\DispositivoApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::post('/dispositivo/token', [DispositivoApiController::class, 'registrarToken']);

    Route::get('/mis-citas', [CitaApiController::class, 'index']);
    Route::post('/citas',    [CitaApiController::class, 'store']);

    // Gestor only
    Route::middleware('gestor')->group(function () {
        Route::get('/clientes', [ClienteApiController::class, 'index']);
        Route::get('/citas/mes', [CitaApiController::class, 'mes']);
        Route::patch('/citas/{cita}/estado', [CitaApiController::class, 'actualizarEstado']);
    });

    // Gestor o propietario del cliente
    Route::middleware('gestor.o.propietario')->group(function () {
        Route::get('/clientes/{cliente}',           [ClienteApiController::class, 'show']);
        Route::get('/clientes/{cliente}/historial', [ClienteApiController::class, 'historial']);
    });
});
