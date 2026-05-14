<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCitaRequest;
use App\Http\Requests\UpdateCitaEstadoRequest;
use App\Models\Cita;
use App\Services\CitaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CitaApiController extends Controller
{
    public function __construct(private CitaService $citaService) {}

    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $cliente = $user->cliente;

        if (!$cliente) {
            return response()->json(['message' => 'No tienes ficha de cliente.'], 404);
        }

        $proximas   = $cliente->citasFuturas()->with('gestor:id,name')->get();
        $anteriores = $cliente->citas()
                              ->where('fecha_hora', '<', now())
                              ->orderByDesc('fecha_hora')
                              ->limit(20)
                              ->get();

        return response()->json(compact('proximas', 'anteriores'));
    }

    public function store(StoreCitaRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($user->isCliente()) {
            $cliente = $user->cliente;
            if (!$cliente) {
                return response()->json(['message' => 'No tienes ficha de paciente.'], 422);
            }
            $data['cliente_id'] = $cliente->id;
            $data['estado']     = 'pendiente';
        } else {
            $data['estado']    = 'confirmada';
            $data['gestor_id'] = $user->id;
        }

        $cita = $this->citaService->crearCita($data);
        return response()->json($cita->load('cliente'), 201);
    }

    public function actualizarEstado(UpdateCitaEstadoRequest $request, Cita $cita): JsonResponse
    {
        $data = $request->validated();
        $cita = $this->citaService->actualizarEstado($cita, $data['estado'], $data['notas'] ?? null);
        return response()->json($cita->load('cliente'));
    }

    public function update(Request $request, Cita $cita): JsonResponse
    {
        $data = $request->validate([
            'fecha_hora'       => 'sometimes|date',
            'duracion_minutos' => 'sometimes|integer|min:15|max:240',
            'motivo'           => 'sometimes|string|max:200',
            'estado'           => 'sometimes|in:pendiente,confirmada,cancelada,realizada,no_presentado',
            'notas'            => 'nullable|string',
        ]);

        $cita->update($data);
        return response()->json($cita->fresh('cliente'));
    }

    public function destroy(Cita $cita): JsonResponse
    {
        $cita->delete();
        return response()->json(['ok' => true]);
    }

    public function mes(Request $request): JsonResponse
    {
        $start = $request->get('start', now()->startOfMonth()->toDateString());
        $end   = $request->get('end',   now()->endOfMonth()->addDay()->toDateString());
        return response()->json($this->citaService->citasPorRango($start, $end));
    }
}
