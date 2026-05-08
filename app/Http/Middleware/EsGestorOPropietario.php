<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EsGestorOPropietario
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        if ($user->isGestor()) {
            return $next($request);
        }

        $clienteId = $request->route('id') ?? $request->route('cliente')?->id ?? $request->input('cliente_id');

        if ($clienteId && $user->cliente?->id === (int) $clienteId) {
            return $next($request);
        }

        return response()->json(['error' => 'No autorizado.'], 403);
    }
}
