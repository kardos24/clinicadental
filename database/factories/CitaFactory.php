<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class CitaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id'           => Cliente::factory(),
            'gestor_id'            => null,
            'fecha_hora'           => now()->addDays(fake()->numberBetween(1, 30))->format('Y-m-d H:i:s'),
            'duracion_minutos'     => fake()->randomElement([30, 45, 60]),
            'motivo'               => fake()->sentence(4),
            'estado'               => 'pendiente',
            'notas'                => null,
            'recordatorio_enviado' => false,
        ];
    }

    public function confirmada(): static
    {
        return $this->state(['estado' => 'confirmada']);
    }

    public function pasada(): static
    {
        return $this->state([
            'fecha_hora' => now()->subDays(fake()->numberBetween(1, 30))->format('Y-m-d H:i:s'),
        ]);
    }
}
