<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHistorialRequest;
use App\Http\Requests\UpdateHistorialRequest;
use App\Models\Cliente;
use App\Models\HistorialClinico;
use Illuminate\Http\JsonResponse;

class HistorialApiController extends Controller
{
    public function store(StoreHistorialRequest $request, Cliente $cliente): JsonResponse
    {
        $data = $request->validated();
        $data['cliente_id'] = $cliente->id;
        $data['gestor_id']  = $request->user()->id;
        $historial = HistorialClinico::create($data);
        return response()->json($historial, 201);
    }

    public function update(UpdateHistorialRequest $request, HistorialClinico $historial): JsonResponse
    {
        $historial->update($request->validated());
        return response()->json($historial->fresh());
    }

    public function destroy(HistorialClinico $historial): JsonResponse
    {
        $historial->delete();
        return response()->json(['ok' => true]);
    }
}
