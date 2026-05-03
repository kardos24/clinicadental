<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ─── Página pública de la clínica ─────────────────────────────────────────────
Route::get('/', fn() => view('public.home'))->name('home');
Route::get('/servicios', fn() => view('public.servicios'))->name('servicios');
Route::get('/contacto',  [ContactoController::class, 'show'])->name('contacto');
Route::post('/contacto', [ContactoController::class, 'send'])->name('contacto.send');

// ─── Autenticación ─────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro',[AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Área privada común ────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard gestor
    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('dashboard')
         ->middleware('gestor');

    // ── Clientes (CRUD completo para gestor, solo ver para cliente) ──────────
    Route::resource('clientes', ClienteController::class);

    // Actualización del esquema dental (POST JSON desde JS)
    Route::post('clientes/{cliente}/dentadura', [ClienteController::class, 'actualizarDentadura'])
         ->name('clientes.dentadura')
         ->middleware('gestor');

    // ── Historial clínico ────────────────────────────────────────────────────
    Route::post('clientes/{cliente}/historial',        [HistorialController::class, 'store'])->name('historial.store')->middleware('gestor');
    Route::put('historial/{historial}',                [HistorialController::class, 'update'])->name('historial.update')->middleware('gestor');
    Route::delete('historial/{historial}',             [HistorialController::class, 'destroy'])->name('historial.destroy')->middleware('gestor');

    // ── Citas ────────────────────────────────────────────────────────────────
    Route::get('/calendario',      [CitaController::class, 'calendario'])->name('citas.calendario')->middleware('gestor');
    Route::get('/mis-citas',       [CitaController::class, 'misCitas'])->name('citas.mis-citas');
    Route::post('/citas',          [CitaController::class, 'store'])->name('citas.store');
    Route::put('/citas/{cita}',    [CitaController::class, 'update'])->name('citas.update')->middleware('gestor');
    Route::delete('/citas/{cita}', [CitaController::class, 'destroy'])->name('citas.destroy')->middleware('gestor');

    // JSON para el calendario FullCalendar (sesión web — no conflicta con /api/citas/mes)
    Route::get('/citas/eventos', [CitaController::class, 'apiMes'])->name('citas.eventos')->middleware('gestor');
});
