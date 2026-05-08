<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        return [
            'apellidos'     => fake()->lastName(),
            'nombre'        => fake()->firstName(),
            'num_filiacion' => 'MUL-' . str_pad($counter, 5, '0', STR_PAD_LEFT),
            'edad'          => fake()->optional(0.7)->numberBetween(1, 90),
            'telefono'      => fake()->optional(0.7)->numerify('6########'),
            'direccion'     => fake()->optional(0.5)->streetAddress(),
            'cp'            => fake()->optional(0.5)->postcode(),
            'profesion'     => fake()->optional(0.5)->jobTitle(),
            'observaciones' => null,
            'user_id'       => null,
        ];
    }
}
