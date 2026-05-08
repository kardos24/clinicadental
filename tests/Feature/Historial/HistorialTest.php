<?php

namespace Tests\Feature\Historial;

use App\Models\Cliente;
use App\Models\HistorialClinico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_crea_entrada_con_saldo_auto(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->post(route('historial.store', $cliente), [
            'dia'         => 3,
            'mes'         => 5,
            'anio'        => 2026,
            'diagnostico' => 'Caries molar',
            'debe'        => 150,
            'haber'       => 50,
        ])->assertRedirect();

        $this->assertDatabaseHas('historial_clinico', [
            'cliente_id'  => $cliente->id,
            'diagnostico' => 'Caries molar',
            'saldo'       => 100,
        ]);
    }

    public function test_fecha_invalida_devuelve_error(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->post(route('historial.store', $cliente), [
            'dia'         => 31,
            'mes'         => 2,
            'anio'        => 2026,
            'diagnostico' => 'Test',
        ])->assertSessionHasErrors('dia');
    }

    public function test_gestor_puede_actualizar_historial(): void
    {
        $gestor    = User::factory()->gestor()->create();
        $cliente   = Cliente::factory()->create();
        $historial = HistorialClinico::factory()->create([
            'cliente_id' => $cliente->id,
            'gestor_id'  => $gestor->id,
        ]);

        $this->actingAs($gestor)->put(route('historial.update', $historial), [
            'dia'         => 5,
            'mes'         => 5,
            'anio'        => 2026,
            'diagnostico' => 'Actualizado',
            'debe'        => 200,
            'haber'       => 100,
        ]);

        $this->assertDatabaseHas('historial_clinico', [
            'id'          => $historial->id,
            'diagnostico' => 'Actualizado',
            'saldo'       => 100,
        ]);
    }

    public function test_gestor_puede_eliminar_historial(): void
    {
        $gestor    = User::factory()->gestor()->create();
        $cliente   = Cliente::factory()->create();
        $historial = HistorialClinico::factory()->create(['cliente_id' => $cliente->id]);

        $this->actingAs($gestor)->delete(route('historial.destroy', $historial));

        $this->assertDatabaseMissing('historial_clinico', ['id' => $historial->id]);
    }

    public function test_cliente_no_puede_crear_historial(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('historial.store', $cliente), [
            'dia'         => 1,
            'mes'         => 1,
            'anio'        => 2026,
            'diagnostico' => 'Test',
        ])->assertForbidden();
    }
}
