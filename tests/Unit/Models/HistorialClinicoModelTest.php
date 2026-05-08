<?php

namespace Tests\Unit\Models;

use App\Models\Cliente;
use App\Models\HistorialClinico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistorialClinicoModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_saldo_se_calcula_al_guardar(): void
    {
        $cliente   = Cliente::factory()->create();
        $historial = HistorialClinico::factory()->create([
            'cliente_id' => $cliente->id,
            'debe'       => 150,
            'haber'      => 50,
        ]);

        $this->assertEquals(100, $historial->saldo);
    }

    public function test_saldo_cero_cuando_debe_igual_haber(): void
    {
        $cliente   = Cliente::factory()->create();
        $historial = HistorialClinico::factory()->create([
            'cliente_id' => $cliente->id,
            'debe'       => 0,
            'haber'      => 0,
        ]);

        $this->assertEquals(0, $historial->saldo);
    }

    public function test_saldo_se_recalcula_al_actualizar(): void
    {
        $cliente   = Cliente::factory()->create();
        $historial = HistorialClinico::factory()->create([
            'cliente_id' => $cliente->id,
            'debe'       => 100,
            'haber'      => 0,
        ]);

        $historial->update(['debe' => 200, 'haber' => 80]);

        $this->assertEquals(120, $historial->fresh()->saldo);
    }

    public function test_fecha_formateada_attribute(): void
    {
        $historial = new HistorialClinico(['dia' => 5, 'mes' => 3, 'anio' => 2026]);
        $this->assertSame('5/3/2026', $historial->fecha_formateada);
    }
}
