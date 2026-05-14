<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCitaRequest;
use App\Models\Cita;
use App\Models\Cliente;
use App\Services\CitaService;
use App\Services\NotificacionService;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function __construct(
        private NotificacionService $notificaciones,
        private CitaService $citaService
    ) {
        $this->middleware('auth');
    }

    // ─── Calendario (solo gestor) ─────────────────────────────────────────────

    public function calendario()
    {
        $clientes = Cliente::orderBy('apellidos')->get(['id', 'apellidos', 'nombre']);
        return view('citas.calendario', compact('clientes'));
    }

    // ─── Listado de citas del cliente autenticado ─────────────────────────────

    public function misCitas()
    {
        $cliente = auth()->user()->cliente;
        if (!$cliente) abort(404);

        $proximas   = $cliente->citasFuturas()->get();
        $anteriores = $cliente->citas()->where('fecha_hora', '<', now())->paginate(10);

        return view('citas.mis-citas', compact('proximas', 'anteriores'));
    }

    // ─── Crear cita ───────────────────────────────────────────────────────────

    public function store(StoreCitaRequest $request)
    {
        $data = $request->validated();

        if (auth()->user()->isCliente()) {
            $clienteRecord = auth()->user()->cliente;
            if (!$clienteRecord) abort(422, 'No tienes ficha de paciente.');
            $data['cliente_id'] = $clienteRecord->id;
            $data['estado']     = 'pendiente';
        } else {
            $data['estado']    = 'confirmada';
            $data['gestor_id'] = auth()->id();
        }

        $cita = $this->citaService->crearCita($data);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'cita' => $cita->load('cliente')]);
        }

        return back()->with('success', 'Cita registrada correctamente.');
    }

    // ─── Actualizar estado ────────────────────────────────────────────────────

    public function update(Request $request, Cita $cita)
    {
        $data = $request->validate([
            'fecha_hora'       => 'sometimes|date',
            'duracion_minutos' => 'sometimes|integer|min:15|max:240',
            'motivo'           => 'sometimes|string|max:200',
            'estado'           => 'sometimes|in:pendiente,confirmada,cancelada,realizada,no_presentado',
            'notas'            => 'nullable|string',
        ]);

        $cita->update($data);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'cita' => $cita->fresh('cliente')]);
        }

        return back()->with('success', 'Cita actualizada.');
    }

    // ─── Eliminar ─────────────────────────────────────────────────────────────

    public function destroy(Cita $cita)
    {
        $cita->delete();
        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Cita eliminada.');
    }

    // ─── API: citas del rango visible en JSON (para calendario JS) ───────────

    public function apiMes(Request $request)
    {
        $start = $request->get('start', now()->startOfMonth()->toDateString());
        $end   = $request->get('end',   now()->endOfMonth()->addDay()->toDateString());
        return response()->json($this->citaService->citasPorRango($start, $end));
    }
}
