<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CitaController extends Controller
{
    public function __construct(private NotificacionService $notificaciones)
    {
        $this->middleware('auth');
    }

    // ─── Calendario (solo gestor) ─────────────────────────────────────────────

    public function calendario(Request $request)
    {

        $year  = $request->get('year',  now()->year);
        $month = $request->get('month', now()->month);

        $citas = Cita::with('cliente')
                     ->delMes($year, $month)
                     ->get()
                     ->groupBy(fn($c) => $c->fecha_hora->format('Y-m-d'));

        $clientes = Cliente::orderBy('apellidos')->get(['id','apellidos','nombre']);

        return view('citas.calendario', compact('citas', 'year', 'month', 'clientes'));
    }

    // ─── Listado de citas del cliente autenticado ─────────────────────────────

    public function misCitas()
    {
        $cliente = auth()->user()->cliente;
        if (!$cliente) abort(404);

        $proximas  = $cliente->citasFuturas()->get();
        $anteriores= $cliente->citas()->where('fecha_hora', '<', now())->paginate(10);

        return view('citas.mis-citas', compact('proximas', 'anteriores'));
    }

    // ─── Crear cita ───────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'       => 'required|exists:clientes,id',
            'fecha_hora'       => 'required|date|after:now',
            'duracion_minutos' => 'nullable|integer|min:15|max:240',
            'motivo'           => 'required|string|max:200',
            'notas'            => 'nullable|string',
            // 'estado' is intentionally excluded: clients cannot control it;
            // gestors set it explicitly below.
        ]);

        // El cliente solo puede crear citas para sí mismo
        if (auth()->user()->isCliente()) {
            $clienteRecord = auth()->user()->cliente;
            if (!$clienteRecord) abort(422, 'No tienes ficha de paciente.');
            $data['cliente_id'] = $clienteRecord->id;
            $data['estado']     = 'pendiente';
        }

        $data['gestor_id'] = auth()->user()->isGestor() ? auth()->id() : null;

        $cita = Cita::create($data);

        // Enviar notificación push
        $this->notificaciones->citaCreada($cita);

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

        return back()->with('success', 'Cita eliminada.');
    }

    // ─── API: citas del mes en JSON (para calendario JS) ──────────────────────

    public function apiMes(Request $request)
    {

        $year  = $request->get('year',  now()->year);
        $month = $request->get('month', now()->month);

        $citas = Cita::with('cliente:id,apellidos,nombre')
                     ->delMes($year, $month)
                     ->get()
                     ->map(fn($c) => [
                         'id'             => $c->id,
                         'title'          => $c->cliente->nombre_completo . ' — ' . $c->motivo,
                         'start'          => $c->fecha_hora->toIso8601String(),
                         'end'            => $c->fecha_hora->addMinutes($c->duracion_minutos)->toIso8601String(),
                         'color'          => $c->estado_color,
                         'estado'         => $c->estado,
                         'cliente_id'     => $c->cliente_id,
                         'cliente_nombre' => $c->cliente->nombre_completo,
                     ]);

        return response()->json($citas);
    }
}
