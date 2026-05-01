<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Dentadura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    public function __construct()
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'apellidos'    => 'required|string|max:100',
            'nombre'       => 'required|string|max:100',
            'edad'         => 'nullable|integer|min:0|max:150',
            'profesion'    => 'nullable|string|max:100',
            'direccion'    => 'nullable|string|max:200',
            'cp'           => 'nullable|string|max:10',
            'telefono'     => 'nullable|string|max:20',
            'observaciones'=> 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $cliente = Cliente::create($data);
            $cliente->num_filiacion = $cliente->generarNumFiliacion();
            $cliente->save();

            // Inicializar todos los dientes como sanos
            $dientes = array_merge(
                Dentadura::DIENTES_SUPERIORES,
                Dentadura::DIENTES_INFERIORES
            );
            foreach ($dientes as $num) {
                Dentadura::create([
                    'cliente_id'          => $cliente->id,
                    'num_diente'          => (string)$num,
                    'estado'              => 'sano',
                    'fecha_actualizacion' => now(),
                ]);
            }
        });

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente creado correctamente.');
    }

    // ─── Ver ficha ────────────────────────────────────────────────────────────

    public function show(Cliente $cliente)
    {
        // El cliente solo puede ver su propia ficha
        if (auth()->user()->isCliente()) {
            if (auth()->user()->cliente?->id !== $cliente->id) {
                abort(403);
            }
        }

        $historial = $cliente->historialClinico()->paginate(10);
        $dentadura = $cliente->dentadura->keyBy('num_diente');
        $citasFuturas = $cliente->citasFuturas()->get();

        return view('clientes.show', compact('cliente', 'historial', 'dentadura', 'citasFuturas'));
    }

    // ─── Formulario edición ───────────────────────────────────────────────────

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    // ─── Actualizar ───────────────────────────────────────────────────────────

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'apellidos'    => 'required|string|max:100',
            'nombre'       => 'required|string|max:100',
            'edad'         => 'nullable|integer|min:0|max:150',
            'profesion'    => 'nullable|string|max:100',
            'direccion'    => 'nullable|string|max:200',
            'cp'           => 'nullable|string|max:10',
            'telefono'     => 'nullable|string|max:20',
            'observaciones'=> 'nullable|string',
        ]);

        $cliente->update($data);

        return redirect()->route('clientes.show', $cliente)
                         ->with('success', 'Cliente actualizado.');
    }

    // ─── Borrar (soft delete) ─────────────────────────────────────────────────

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente eliminado.');
    }

    // ─── Actualizar estado dental ─────────────────────────────────────────────

    public function actualizarDentadura(Request $request, Cliente $cliente)
    {
        if (!auth()->user()->isGestor()) abort(403);

        $data = $request->validate([
            'dientes'                  => 'required|array',
            'dientes.*.num_diente'     => 'required|string',
            'dientes.*.estado'         => 'required|in:sano,picado,caries,partido,caido,puente,sustituido',
            'dientes.*.notas'          => 'nullable|string',
        ]);

        foreach ($data['dientes'] as $diente) {
            Dentadura::updateOrCreate(
                ['cliente_id' => $cliente->id, 'num_diente' => $diente['num_diente']],
                ['estado' => $diente['estado'], 'notas' => $diente['notas'] ?? null, 'fecha_actualizacion' => now()]
            );
        }

        return response()->json(['ok' => true, 'message' => 'Dentadura actualizada.']);
    }
}
