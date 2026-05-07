<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreClienteRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreClienteRequestTest extends TestCase
{
    private function validate(array $data): bool
    {
        $request   = new StoreClienteRequest();
        $validator = Validator::make($data, $request->rules());
        return $validator->passes();
    }

    public function test_valida_datos_minimos_correctos(): void
    {
        $this->assertTrue($this->validate([
            'apellidos' => 'García López',
            'nombre'    => 'Juan',
        ]));
    }

    public function test_falla_sin_apellidos(): void
    {
        $this->assertFalse($this->validate(['nombre' => 'Juan']));
    }

    public function test_falla_sin_nombre(): void
    {
        $this->assertFalse($this->validate(['apellidos' => 'García']));
    }

    public function test_falla_con_edad_negativa(): void
    {
        $this->assertFalse($this->validate([
            'apellidos' => 'García',
            'nombre'    => 'Juan',
            'edad'      => -1,
        ]));
    }

    public function test_acepta_todos_los_campos_opcionales(): void
    {
        $this->assertTrue($this->validate([
            'apellidos'     => 'García',
            'nombre'        => 'Juan',
            'edad'          => 35,
            'profesion'     => 'Médico',
            'direccion'     => 'Calle Mayor 1',
            'cp'            => '30170',
            'telefono'      => '600000000',
            'observaciones' => 'Alérgico a penicilina',
        ]));
    }
}
