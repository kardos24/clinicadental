<?php

namespace Tests\Unit\Services;

use App\Models\Cita;
use App\Models\Cliente;
use App\Services\CitaService;
use App\Services\NotificacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CitaServiceTest extends TestCase
{
    use RefreshDatabase;

    private CitaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $notificaciones = Mockery::mock(NotificacionService::class);
        $notificaciones->shouldReceive('citaCreada')->andReturnNull();
        $this->service = new CitaService($notificaciones);
    }

    public function test_crear_cita_persiste_en_base_de_datos(): void
    {
        $cliente = Cliente::factory()->create();

        $cita = $this->service->crearCita([
            'cliente_id'       => $cliente->id,
            'fecha_hora'       => now()->addDay()->toDateTimeString(),
            'duracion_minutos' => 30,
            'motivo'           => 'Revisión',
            'estado'           => 'confirmada',
        ]);

        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'motivo' => 'Revisión']);
    }

    public function test_actualizar_estado_cambia_estado(): void
    {
        $cita = Cita::factory()->create(['estado' => 'pendiente']);

        $actualizada = $this->service->actualizarEstado($cita, 'confirmada');

        $this->assertSame('confirmada', $actualizada->estado);
        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'estado' => 'confirmada']);
    }

    public function test_citas_por_rango_retorna_formato_fullcalendar(): void
    {
        $cliente = Cliente::factory()->create();
        Cita::factory()->create([
            'cliente_id' => $cliente->id,
            'fecha_hora' => now()->startOfMonth()->addDays(5),
            'motivo'     => 'Test',
            'estado'     => 'confirmada',
        ]);

        $start    = now()->startOfMonth()->toDateString();
        $end      = now()->endOfMonth()->addDay()->toDateString();
        $resultado = $this->service->citasPorRango($start, $end);

        $this->assertCount(1, $resultado);
        $this->assertArrayHasKey('title', $resultado->first());
        $this->assertArrayHasKey('start', $resultado->first());
        $this->assertArrayHasKey('color', $resultado->first());
    }
}
