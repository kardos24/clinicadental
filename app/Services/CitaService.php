<?php

namespace App\Services;

use App\Models\Cita;
use Illuminate\Support\Collection;

class CitaService
{
    public function __construct(private NotificacionService $notificaciones) {}

    public function crearCita(array $datos): Cita
    {
        $cita = Cita::create($datos);
        $this->notificaciones->citaCreada($cita);
        return $cita;
    }

    public function actualizarEstado(Cita $cita, string $estado, ?string $notas = null): Cita
    {
        $campos = ['estado' => $estado];
        if ($notas !== null) {
            $campos['notas'] = $notas;
        }
        $cita->update($campos);
        return $cita->fresh();
    }

    public function citasPorRango(string $start, string $end): Collection
    {
        return Cita::with('cliente:id,apellidos,nombre')
            ->where('fecha_hora', '>=', $start)
            ->where('fecha_hora', '<',  $end)
            ->orderBy('fecha_hora')
            ->get()
            ->map(fn($c) => [
                'id'             => $c->id,
                'title'          => $c->cliente->nombre_completo . ' — ' . $c->motivo,
                'start'          => $c->fecha_hora->toIso8601String(),
                'end'            => $c->fecha_hora->copy()->addMinutes($c->duracion_minutos)->toIso8601String(),
                'color'          => $c->estado_color,
                'estado'         => $c->estado,
                'motivo'         => $c->motivo,
                'notas'          => $c->notas,
                'cliente_id'     => $c->cliente_id,
                'cliente_nombre' => $c->cliente->nombre_completo,
            ]);
    }
}
