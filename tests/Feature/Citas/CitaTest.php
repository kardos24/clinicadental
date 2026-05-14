<?php

namespace Tests\Feature\Citas;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(NotificacionService::class)->shouldIgnoreMissing();
    }

    public function test_gestor_puede_ver_calendario(): void
    {
        $gestor = User::factory()->gestor()->create();

        $this->actingAs($gestor)->get(route('citas.calendario'))->assertOk();
    }

    public function test_cliente_puede_ver_mis_citas(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('citas.mis-citas'))->assertOk();
    }

    public function test_cliente_sin_ficha_recibe_404(): void
    {
        $user = User::factory()->create(['role' => 'cliente']);

        $this->actingAs($user)->get(route('citas.mis-citas'))->assertNotFound();
    }

    public function test_gestor_crea_cita_con_estado_confirmada(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->post(route('citas.store'), [
            'cliente_id' => $cliente->id,
            'fecha_hora' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'motivo'     => 'Revisión',
        ])->assertRedirect();

        $this->assertDatabaseHas('citas', ['cliente_id' => $cliente->id, 'estado' => 'confirmada']);
    }

    public function test_cliente_crea_cita_con_estado_pendiente(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('citas.store'), [
            'cliente_id' => $cliente->id,
            'fecha_hora' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'motivo'     => 'Dolor muela',
        ]);

        $this->assertDatabaseHas('citas', ['cliente_id' => $cliente->id, 'estado' => 'pendiente']);
    }

    public function test_fecha_pasada_falla_validacion(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->post(route('citas.store'), [
            'cliente_id' => $cliente->id,
            'fecha_hora' => now()->subDays(1)->format('Y-m-d H:i:s'),
            'motivo'     => 'Test',
        ])->assertSessionHasErrors('fecha_hora');
    }

    public function test_gestor_puede_actualizar_estado_cita(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $cita    = Cita::factory()->create(['cliente_id' => $cliente->id]);

        $this->actingAs($gestor)->put(route('citas.update', $cita), ['estado' => 'confirmada']);

        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'estado' => 'confirmada']);
    }

    public function test_gestor_puede_eliminar_cita(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $cita    = Cita::factory()->create(['cliente_id' => $cliente->id]);

        $this->actingAs($gestor)->delete(route('citas.destroy', $cita));

        $this->assertDatabaseMissing('citas', ['id' => $cita->id]);
    }

    public function test_api_mes_devuelve_json_fullcalendar(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        Cita::factory()->create([
            'cliente_id' => $cliente->id,
            'fecha_hora' => now()->startOfMonth()->addDays(5)->format('Y-m-d H:i:s'),
            'estado'     => 'confirmada',
        ]);

        $this->actingAs($gestor)
             ->getJson(route('citas.eventos', ['year' => now()->year, 'month' => now()->month]))
             ->assertOk()
             ->assertJsonStructure([['id', 'title', 'start', 'end', 'color']]);
    }
}
