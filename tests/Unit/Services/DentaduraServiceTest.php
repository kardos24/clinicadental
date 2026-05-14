<?php

namespace Tests\Unit\Services;

use App\Models\Dentadura;
use App\Services\DentaduraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DentaduraServiceTest extends TestCase
{
    use RefreshDatabase;

    private DentaduraService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DentaduraService();
    }

    public function test_normalizar_estado_cara_convierte_sano_a_null(): void
    {
        $this->assertNull($this->service->normalizarEstadoCara('sano'));
    }

    public function test_normalizar_estado_cara_convierte_null_a_null(): void
    {
        $this->assertNull($this->service->normalizarEstadoCara(null));
    }

    public function test_normalizar_estado_cara_preserva_otros_estados(): void
    {
        $this->assertSame('caries',     $this->service->normalizarEstadoCara('caries'));
        $this->assertSame('obturacion', $this->service->normalizarEstadoCara('obturacion'));
        $this->assertSame('fractura',   $this->service->normalizarEstadoCara('fractura'));
    }

    public function test_normalizar_estado_pieza_convierte_presente_a_null(): void
    {
        $this->assertNull($this->service->normalizarEstadoPieza('presente'));
        $this->assertNull($this->service->normalizarEstadoPieza(null));
    }

    public function test_normalizar_estado_pieza_preserva_otros_estados(): void
    {
        $this->assertSame('ausente',  $this->service->normalizarEstadoPieza('ausente'));
        $this->assertSame('corona',   $this->service->normalizarEstadoPieza('corona'));
        $this->assertSame('implante', $this->service->normalizarEstadoPieza('implante'));
    }

    public function test_actualizar_diente_normaliza_caras_y_guarda(): void
    {
        $cliente = \App\Models\Cliente::factory()->create();
        $diente  = Dentadura::factory()->create([
            'cliente_id'      => $cliente->id,
            'num_diente'      => '16',
            'cara_vestibular' => null,
        ]);

        $this->service->actualizarDiente($diente, [
            'cara_vestibular' => 'sano',
            'cara_lingual'    => 'caries',
        ]);

        $diente->refresh();
        $this->assertNull($diente->cara_vestibular);
        $this->assertSame('caries', $diente->cara_lingual);
    }
}
