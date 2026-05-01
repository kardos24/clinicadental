<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\DispositivoPush;
use App\Models\HistorialClinico;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ApiController extends Controller
{
    public function __construct(private NotificacionService $notificaciones) {}

    // ─── Autenticación ────────────────────────────────────────────────────────

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($data)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        if (!auth()->user()->activo) {
            Auth::logout();
            return response()->json(['message' => 'Esta cuenta está desactivada.'], 403);
        }

        $user  = Auth::user();
        $token = $user->createToken('app-movil')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['ok' => true]);
    }

    // ─── Registrar token FCM ──────────────────────────────────────────────────

    public function registrarToken(Request $request)
    {
        $data = $request->validate([
            'token_fcm'  => 'required|string',
            'plataforma' => 'nullable|in:android,ios',
        ]);

        DispositivoPush::updateOrCreate(
            ['user_id' => auth()->id(), 'token_fcm' => $data['token_fcm']],
            ['plataforma' => $data['plataforma'] ?? 'android']
        );

        return response()->json(['ok' => true]);
    }

    // ─── Clientes ─────────────────────────────────────────────────────────────

    public function clientes(Request $request)
    {
        $this->requireGestor($request);

        $clientes = Cliente::query()
            ->when($request->buscar, fn($q) => $q->buscar($request->buscar))
            ->orderBy('apellidos')
            ->paginate(50);

        return response()->json($clientes);
    }

    public function cliente(Request $request, Cliente $cliente)
    {
        $this->requireGestorOrOwner($request, $cliente);

        return response()->json($cliente->load(['historialClinico', 'dentadura', 'citasFuturas']));
    }

    // ─── Citas ────────────────────────────────────────────────────────────────

    public function misCitas(Request $request)
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

    public function citasGestor(Request $request)
    {
        $this->requireGestor($request);

        $year  = $request->get('year',  now()->year);
        $month = $request->get('month', now()->month);

        $citas = Cita::with('cliente:id,apellidos,nombre,telefono')
                     ->delMes($year, $month)
                     ->get();

        return response()->json($citas);
    }

    public function crearCita(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'cliente_id'       => 'required_if:role,gestor|exists:clientes,id',
            'fecha_hora'       => 'required|date|after:now',
            'duracion_minutos' => 'nullable|integer|min:15|max:240',
            'motivo'           => 'required|string|max:200',
            'notas'            => 'nullable|string',
        ]);

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

        $cita = Cita::create($data);
        $this->notificaciones->citaCreada($cita);

        return response()->json($cita->load('cliente'), 201);
    }

    public function actualizarEstadoCita(Request $request, Cita $cita)
    {
        $this->requireGestor($request);

        $data = $request->validate([
            'estado' => 'required|in:pendiente,confirmada,cancelada,realizada,no_presentado',
            'notas'  => 'nullable|string',
        ]);

        $cita->update($data);

        return response()->json($cita->fresh('cliente'));
    }

    // ─── Historial clínico ────────────────────────────────────────────────────

    public function historial(Request $request, Cliente $cliente)
    {
        $this->requireGestorOrOwner($request, $cliente);

        $historial = $cliente->historialClinico()->paginate(20);

        return response()->json($historial);
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    private function requireGestor(Request $request): void
    {
        if (!$request->user()->isGestor()) {
            abort(403, 'Requiere rol gestor.');
        }
    }

    private function requireGestorOrOwner(Request $request, Cliente $cliente): void
    {
        $user = $request->user();
        if ($user->isGestor()) return;
        if ($user->cliente?->id === $cliente->id) return;
        abort(403);
    }
}
