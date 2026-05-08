<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistorialClinicoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id'             => Cliente::factory(),
            'gestor_id'              => null,
            'dia'                    => fake()->numberBetween(1, 28),
            'mes'                    => fake()->numberBetween(1, 12),
            'anio'                   => now()->year,
            'signo'                  => null,
            'diagnostico'            => fake()->sentence(),
            'num_sesiones'           => 1,
            'importe'                => fake()->randomFloat(2, 10, 500),
            'tratamiento_realizado'  => null,
            'recibo'                 => null,
            'debe'                   => 100.00,
            'haber'                  => 0.00,
            // 'saldo' se calcula automáticamente en el boot hook del modelo
        ];
    }
}
