<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreHistorialRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreHistorialRequestTest extends TestCase
{
    private function makeRequest(array $data): StoreHistorialRequest
    {
        $request = StoreHistorialRequest::create('/', 'POST', $data);
        return StoreHistorialRequest::createFrom($request);
    }

    public function test_valida_fecha_correcta(): void
    {
        $req       = $this->makeRequest(['dia' => 15, 'mes' => 6, 'anio' => 2025, 'diagnostico' => 'Test']);
        $validator = Validator::make($req->all(), $req->rules());
        $req->withValidator($validator);
        $this->assertTrue($validator->passes());
    }

    public function test_rechaza_fecha_imposible(): void
    {
        $req       = $this->makeRequest(['dia' => 31, 'mes' => 2, 'anio' => 2025, 'diagnostico' => 'Test']);
        $validator = Validator::make($req->all(), $req->rules());
        $req->withValidator($validator);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('dia'));
    }

    public function test_requiere_diagnostico(): void
    {
        $req       = $this->makeRequest(['dia' => 15, 'mes' => 6, 'anio' => 2025]);
        $validator = Validator::make($req->all(), $req->rules());
        $this->assertFalse($validator->passes());
    }
}
