<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\HistorialClinico;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'gestor']);
    }

    public function index()
    {
        $stats = [
            'total_clientes'    => Cliente::count(),
            'citas_hoy'         => Cita::delDia(now())->count(),
            'citas_pendientes'  => Cita::where('estado', 'pendiente')->count(),
            'ingresos_mes'      => HistorialClinico::whereYear('created_at', now()->year)
                                                   ->whereMonth('created_at', now()->month)
                                                   ->sum('haber'),
            'citas_hoy_lista'   => Cita::with('cliente')
                                       ->delDia(now())
                                       ->orderBy('fecha_hora')
                                       ->limit(8)
                                       ->get(),
            'ultimos_clientes'  => Cliente::latest()->limit(8)->get(),
        ];

        return view('dashboard.index', compact('stats'));
    }
}
