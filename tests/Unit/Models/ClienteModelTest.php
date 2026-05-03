<?php

namespace Tests\Unit\Models;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_generar_num_filiacion_con_id_1(): void
    {
        $cliente     = Cliente::factory()->create();
        $numGenerado = $cliente->generarNumFiliacion();

        $this->assertSame('MUL-' . str_pad($cliente->id, 5, '0', STR_PAD_LEFT), $numGenerado);
    }

    public function test_generar_num_filiacion_con_id_grande(): void
    {
        $cliente     = new Cliente();
        $cliente->id = 99999;

        $this->assertSame('MUL-99999', $cliente->generarNumFiliacion());
    }

    public function test_nombre_completo_accessor(): void
    {
        $cliente = new Cliente(['apellidos' => 'García', 'nombre' => 'Juan']);
        $this->assertSame('García, Juan', $cliente->nombre_completo);
    }

    public function test_scope_buscar_por_apellidos(): void
    {
        Cliente::factory()->create(['apellidos' => 'García', 'nombre' => 'Ana']);
        Cliente::factory()->create(['apellidos' => 'López',  'nombre' => 'Luis']);

        $resultado = Cliente::buscar('García')->get();

        $this->assertCount(1, $resultado);
        $this->assertSame('García', $resultado->first()->apellidos);
    }

    public function test_scope_buscar_por_nombre(): void
    {
        Cliente::factory()->create(['nombre' => 'María']);
        Cliente::factory()->create(['nombre' => 'Carlos']);

        $resultado = Cliente::buscar('María')->get();

        $this->assertCount(1, $resultado);
    }

    public function test_scope_buscar_por_num_filiacion(): void
    {
        $cliente = Cliente::factory()->create(['num_filiacion' => 'MUL-00042']);
        Cliente::factory()->create(['num_filiacion' => 'MUL-00001']);

        $resultado = Cliente::buscar('MUL-00042')->get();

        $this->assertCount(1, $resultado);
        $this->assertEquals($cliente->id, $resultado->first()->id);
    }

    public function test_scope_buscar_por_telefono(): void
    {
        Cliente::factory()->create(['telefono' => '666123456']);
        Cliente::factory()->create(['telefono' => '999888777']);

        $resultado = Cliente::buscar('666123')->get();

        $this->assertCount(1, $resultado);
    }
}
