<?php

namespace Tests\Feature\Api;

use App\Models\Cliente;
use App\Models\HistorialClinico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiHistorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_puede_crear_historial(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $token   = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson("/api/clientes/{$cliente->id}/historial", [
            'dia'         => 10,
            'mes'         => 5,
            'anio'        => 2026,
            'diagnostico' => 'Caries en diente 16',
            'debe'        => 120.00,
            'haber'       => 120.00,
        ])->assertCreated();

        $this->assertDatabaseHas('historial_clinico', [
            'cliente_id'  => $cliente->id,
            'diagnostico' => 'Caries en diente 16',
        ]);
    }

    public function test_cliente_no_puede_crear_historial(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson("/api/clientes/{$cliente->id}/historial", [
            'dia'         => 10,
            'mes'         => 5,
            'anio'        => 2026,
            'diagnostico' => 'Test',
        ])->assertForbidden();
    }

    public function test_fecha_invalida_falla_validacion(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $token   = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson("/api/clientes/{$cliente->id}/historial", [
            'dia'         => 31,
            'mes'         => 2,
            'anio'        => 2026,
            'diagnostico' => 'Test',
        ])->assertUnprocessable();
    }

    public function test_gestor_puede_actualizar_historial(): void
    {
        $gestor   = User::factory()->gestor()->create();
        $cliente  = Cliente::factory()->create();
        $historial = HistorialClinico::factory()->create(['cliente_id' => $cliente->id]);
        $token    = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->putJson("/api/historial/{$historial->id}", [
            'dia'         => $historial->dia,
            'mes'         => $historial->mes,
            'anio'        => $historial->anio,
            'diagnostico' => 'Diagnóstico actualizado',
        ])->assertOk();

        $this->assertDatabaseHas('historial_clinico', [
            'id'          => $historial->id,
            'diagnostico' => 'Diagnóstico actualizado',
        ]);
    }

    public function test_gestor_puede_eliminar_historial(): void
    {
        $gestor   = User::factory()->gestor()->create();
        $cliente  = Cliente::factory()->create();
        $historial = HistorialClinico::factory()->create(['cliente_id' => $cliente->id]);
        $token    = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->deleteJson("/api/historial/{$historial->id}")
             ->assertOk()
             ->assertJson(['ok' => true]);

        $this->assertDatabaseMissing('historial_clinico', ['id' => $historial->id]);
    }

    public function test_cliente_no_puede_eliminar_historial(): void
    {
        $user     = User::factory()->create(['role' => 'cliente']);
        $cliente  = Cliente::factory()->create(['user_id' => $user->id]);
        $historial = HistorialClinico::factory()->create(['cliente_id' => $cliente->id]);
        $token    = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->deleteJson("/api/historial/{$historial->id}")->assertForbidden();
    }
}
