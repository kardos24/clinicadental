<?php

namespace App\Services;

use App\Models\Dentadura;

class DentaduraService
{
    public function normalizarEstadoCara(?string $estado): ?string
    {
        return ($estado === null || $estado === 'sano') ? null : $estado;
    }

    public function normalizarEstadoPieza(?string $estado): ?string
    {
        return ($estado === null || $estado === 'presente') ? null : $estado;
    }

    public function actualizarDiente(Dentadura $diente, array $datos): void
    {
        $campos = ['fecha_actualizacion' => now()];

        if (isset($datos['estado_pieza'])) {
            $campos['estado_pieza'] = $datos['estado_pieza'];
        }

        foreach (['cara_vestibular', 'cara_lingual', 'cara_mesial', 'cara_distal', 'cara_oclusal'] as $cara) {
            if (array_key_exists($cara, $datos)) {
                $campos[$cara] = $this->normalizarEstadoCara($datos[$cara]);
            }
        }

        if (array_key_exists('notas', $datos)) {
            $campos['notas'] = $datos['notas'];
        }

        $diente->update($campos);
    }
}
