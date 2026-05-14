<?php

namespace Tests\Feature\Api;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiCitasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(NotificacionService::class)->shouldIgnoreMissing();
    }

    public function test_mis_citas_devuelve_proximas_y_anteriores(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/mis-citas')
             ->assertOk()
             ->assertJsonStructure(['proximas', 'anteriores']);
    }

    public function test_mis_citas_sin_ficha_devuelve_404(): void
    {
        $user  = User::factory()->create(['role' => 'cliente']);
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/mis-citas')->assertNotFound();
    }

    public function test_gestor_puede_obtener_citas_del_mes(): void
    {
        $gestor = User::factory()->gestor()->create();
        $token  = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/citas/mes')->assertOk();
    }

    public function test_cliente_no_puede_obtener_citas_del_mes(): void
    {
        $user  = User::factory()->create(['role' => 'cliente']);
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/citas/mes')->assertForbidden();
    }

    public function test_cliente_crea_cita_como_pendiente(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson('/api/citas', [
            'fecha_hora' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'motivo'     => 'Revisión',
        ])->assertCreated();

        $this->assertDatabaseHas('citas', ['cliente_id' => $cliente->id, 'estado' => 'pendiente']);
    }

    public function test_gestor_crea_cita_como_confirmada(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $token   = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson('/api/citas', [
            'cliente_id' => $cliente->id,
            'fecha_hora' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'motivo'     => 'Revisión',
        ])->assertCreated();

        $this->assertDatabaseHas('citas', ['cliente_id' => $cliente->id, 'estado' => 'confirmada']);
    }

    public function test_fecha_pasada_falla_validacion(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson('/api/citas', [
            'fecha_hora' => now()->subDays(1)->format('Y-m-d H:i:s'),
            'motivo'     => 'Test',
        ])->assertUnprocessable();
    }

    public function test_gestor_puede_actualizar_estado_cita(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $cita    = Cita::factory()->create(['cliente_id' => $cliente->id]);
        $token   = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->patchJson("/api/citas/{$cita->id}/estado", [
            'estado' => 'confirmada',
        ])->assertOk();

        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'estado' => 'confirmada']);
    }

    public function test_cliente_no_puede_actualizar_estado_cita(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $cita    = Cita::factory()->create(['cliente_id' => $cliente->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->patchJson("/api/citas/{$cita->id}/estado", [
            'estado' => 'cancelada',
        ])->assertForbidden();
    }
}
