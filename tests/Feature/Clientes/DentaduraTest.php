<?php

namespace Tests\Feature\Clientes;

use App\Models\Cliente;
use App\Models\Dentadura;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DentaduraTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_puede_actualizar_estado_diente(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        Dentadura::factory()->create(['cliente_id' => $cliente->id, 'num_diente' => '16']);

        $this->actingAs($gestor)->postJson(route('clientes.dentadura', $cliente), [
            'dientes' => [[
                'num_diente'      => '16',
                'estado_pieza'    => 'presente',
                'cara_vestibular' => 'caries',
            ]],
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('dentadura', [
            'cliente_id'      => $cliente->id,
            'num_diente'      => '16',
            'cara_vestibular' => 'caries',
        ]);
    }

    public function test_sano_se_guarda_como_null_en_bd(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->postJson(route('clientes.dentadura', $cliente), [
            'dientes' => [[
                'num_diente'      => '11',
                'cara_vestibular' => 'sano',
            ]],
        ]);

        $this->assertDatabaseHas('dentadura', [
            'cliente_id'      => $cliente->id,
            'num_diente'      => '11',
            'cara_vestibular' => null,
        ]);
    }

    public function test_cliente_no_puede_actualizar_dentadura(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->postJson(route('clientes.dentadura', $cliente), [
            'dientes' => [['num_diente' => '11', 'cara_vestibular' => 'caries']],
        ])->assertForbidden();
    }

    public function test_diente_inexistente_se_crea(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->postJson(route('clientes.dentadura', $cliente), [
            'dientes' => [['num_diente' => '21', 'estado_pieza' => 'ausente']],
        ]);

        $this->assertDatabaseHas('dentadura', [
            'cliente_id'   => $cliente->id,
            'num_diente'   => '21',
            'estado_pieza' => 'ausente',
        ]);
    }

    public function test_estado_pieza_invalido_falla_validacion(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->postJson(route('clientes.dentadura', $cliente), [
            'dientes' => [['num_diente' => '11', 'estado_pieza' => 'invalido']],
        ])->assertUnprocessable();
    }
}
