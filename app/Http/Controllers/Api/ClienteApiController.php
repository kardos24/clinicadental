<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Dentadura;
use App\Services\DentaduraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteApiController extends Controller
{
    public function __construct(private DentaduraService $dentaduraService) {}

    public function index(Request $request): JsonResponse
    {
        $clientes = Cliente::query()
            ->when($request->buscar, fn($q) => $q->buscar($request->buscar))
            ->orderBy('apellidos')
            ->paginate(50);

        return response()->json($clientes);
    }

    public function store(StoreClienteRequest $request): JsonResponse
    {
        $cliente = DB::transaction(function () use ($request) {
            $cliente = Cliente::create($request->validated());
            $cliente->num_filiacion = $cliente->generarNumFiliacion();
            $cliente->save();

            $dientes = array_merge(Dentadura::DIENTES_SUPERIORES, Dentadura::DIENTES_INFERIORES);
            foreach ($dientes as $num) {
                Dentadura::create([
                    'cliente_id'          => $cliente->id,
                    'num_diente'          => (string) $num,
                    'estado_pieza'        => 'presente',
                    'fecha_actualizacion' => now(),
                ]);
            }
            return $cliente;
        });

        return response()->json($cliente->fresh(), 201);
    }

    public function show(Request $request, Cliente $cliente): JsonResponse
    {
        return response()->json($cliente->load(['historialClinico', 'dentadura', 'citasFuturas']));
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente): JsonResponse
    {
        $cliente->update($request->validated());
        return response()->json($cliente->fresh());
    }

    public function destroy(Cliente $cliente): JsonResponse
    {
        $cliente->delete();
        return response()->json(['ok' => true]);
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

    public function actualizarDentadura(Request $request, Cliente $cliente): JsonResponse
    {
        $estadosPieza = implode(',', array_keys(Dentadura::ESTADOS_PIEZA));
        $estadosCara  = 'sano,' . implode(',', array_keys(Dentadura::ESTADOS_CARA));

        $data = $request->validate([
            'dientes'                   => 'required|array',
            'dientes.*.num_diente'      => 'required|string',
            'dientes.*.estado_pieza'    => "nullable|in:{$estadosPieza}",
            'dientes.*.cara_vestibular' => "nullable|in:{$estadosCara}",
            'dientes.*.cara_lingual'    => "nullable|in:{$estadosCara}",
            'dientes.*.cara_mesial'     => "nullable|in:{$estadosCara}",
            'dientes.*.cara_distal'     => "nullable|in:{$estadosCara}",
            'dientes.*.cara_oclusal'    => "nullable|in:{$estadosCara}",
            'dientes.*.notas'           => 'nullable|string',
        ]);

        foreach ($data['dientes'] as $dienteData) {
            $diente = Dentadura::firstOrCreate(
                ['cliente_id' => $cliente->id, 'num_diente' => $dienteData['num_diente']],
                ['fecha_actualizacion' => now()]
            );
            $this->dentaduraService->actualizarDiente($diente, $dienteData);
        }

        return response()->json(['ok' => true, 'message' => 'Dentadura actualizada.']);
    }
}
