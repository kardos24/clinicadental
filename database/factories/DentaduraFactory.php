<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class DentaduraFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id'          => Cliente::factory(),
            'num_diente'          => '16',
            'estado_pieza'        => 'presente',
            'cara_vestibular'     => null,
            'cara_lingual'        => null,
            'cara_mesial'         => null,
            'cara_distal'         => null,
            'cara_oclusal'        => null,
            'notas'               => null,
            'fecha_actualizacion' => now()->toDateString(),
        ];
    }
}
