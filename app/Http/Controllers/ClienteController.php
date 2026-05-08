<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Dentadura;
use App\Services\DentaduraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    public function __construct(private DentaduraService $dentaduraService)
    {
        $this->middleware('auth');
        $this->middleware('gestor')->except(['show']);
    }

    // ─── Listado (solo gestor) ────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Cliente::query()->withCount('citas')->withSum('historialClinico', 'saldo');

        if ($buscar = $request->get('buscar')) {
            $query->buscar($buscar);
        }

        $clientes = $query->orderBy('apellidos')->paginate(20)->withQueryString();

        return view('clientes.index', compact('clientes', 'buscar'));
    }

    // ─── Formulario alta ──────────────────────────────────────────────────────

    public function create()
    {
        return view('clientes.create');
    }

    // ─── Guardar nuevo cliente ────────────────────────────────────────────────

    public function store(StoreClienteRequest $request)
    {
        DB::transaction(function () use ($request) {
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
        });

        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
    }

    // ─── Ver ficha ────────────────────────────────────────────────────────────

    public function show(Cliente $cliente)
    {
        if (auth()->user()->isCliente()) {
            if (auth()->user()->cliente?->id !== $cliente->id) {
                abort(403);
            }
        }

        $historial    = $cliente->historialClinico()->paginate(10);
        $dentadura    = $cliente->dentadura->keyBy('num_diente');
        $citasFuturas = $cliente->citasFuturas()->get();

        return view('clientes.show', compact('cliente', 'historial', 'dentadura', 'citasFuturas'));
    }

    // ─── Formulario edición ───────────────────────────────────────────────────

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    // ─── Actualizar ───────────────────────────────────────────────────────────

    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        $cliente->update($request->validated());
        return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente actualizado.');
    }

    // ─── Borrar (soft delete) ─────────────────────────────────────────────────

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado.');
    }

    // ─── Actualizar estado dental ─────────────────────────────────────────────

    public function actualizarDentadura(Request $request, Cliente $cliente)
    {
        if (!auth()->user()->isGestor()) abort(403);

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
