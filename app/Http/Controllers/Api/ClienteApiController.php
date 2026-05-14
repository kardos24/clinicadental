<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clientes = Cliente::query()
            ->when($request->buscar, fn($q) => $q->buscar($request->buscar))
            ->orderBy('apellidos')
            ->paginate(50);

        return response()->json($clientes);
    }

    public function show(Request $request, Cliente $cliente): JsonResponse
    {
        return response()->json($cliente->load(['historialClinico', 'dentadura', 'citasFuturas']));
    }

    public function historial(Request $request, Cliente $cliente): JsonResponse
    {
        $historial = $cliente->historialClinico()->paginate(20);
        return response()->json($historial);
    }

    public function dentadura(Request $request, Cliente $cliente): JsonResponse
    {
        $dentadura = $cliente->dentadura()->orderBy('num_diente')->get();
        return response()->json($dentadura);
    }
}
