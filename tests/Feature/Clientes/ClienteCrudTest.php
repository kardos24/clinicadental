<?php

namespace Tests\Feature\Clientes;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_puede_listar_clientes(): void
    {
        $gestor = User::factory()->gestor()->create();
        Cliente::factory()->count(3)->create();

        $this->actingAs($gestor)->get(route('clientes.index'))->assertOk();
    }

    public function test_listado_soporta_busqueda(): void
    {
        $gestor = User::factory()->gestor()->create();
        Cliente::factory()->create(['apellidos' => 'García']);
        Cliente::factory()->create(['apellidos' => 'López']);

        $this->actingAs($gestor)
             ->get(route('clientes.index', ['buscar' => 'García']))
             ->assertOk()
             ->assertSee('García')
             ->assertDontSee('López');
    }

    public function test_gestor_puede_ver_formulario_crear(): void
    {
        $gestor = User::factory()->gestor()->create();

        $this->actingAs($gestor)->get(route('clientes.create'))->assertOk();
    }

    public function test_store_crea_cliente_con_num_filiacion(): void
    {
        $gestor = User::factory()->gestor()->create();

        $this->actingAs($gestor)->post(route('clientes.store'), [
            'apellidos' => 'Pérez',
            'nombre'    => 'Ana',
        ])->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('clientes', ['apellidos' => 'Pérez']);
        $cliente = Cliente::where('apellidos', 'Pérez')->first();
        $this->assertSame('MUL-' . str_pad($cliente->id, 5, '0', STR_PAD_LEFT), $cliente->num_filiacion);
    }

    public function test_store_inicializa_32_dientes(): void
    {
        $gestor = User::factory()->gestor()->create();

        $this->actingAs($gestor)->post(route('clientes.store'), [
            'apellidos' => 'Pérez',
            'nombre'    => 'Ana',
        ]);

        $cliente = Cliente::where('apellidos', 'Pérez')->first();
        $this->assertSame(32, $cliente->dentadura()->count());
    }

    public function test_gestor_puede_ver_cualquier_ficha(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->get(route('clientes.show', $cliente))->assertOk();
    }

    public function test_cliente_solo_puede_ver_su_propia_ficha(): void
    {
        $user   = User::factory()->create(['role' => 'cliente']);
        $propio = Cliente::factory()->create(['user_id' => $user->id]);
        $ajeno  = Cliente::factory()->create();

        $this->actingAs($user)->get(route('clientes.show', $propio))->assertOk();
        $this->actingAs($user)->get(route('clientes.show', $ajeno))->assertForbidden();
    }

    public function test_no_autenticado_redirige_a_login(): void
    {
        $cliente = Cliente::factory()->create();

        $this->get(route('clientes.show', $cliente))->assertRedirect(route('login'));
    }

    public function test_gestor_puede_actualizar_cliente(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->put(route('clientes.update', $cliente), [
            'apellidos' => 'NuevoApellido',
            'nombre'    => 'NuevoNombre',
        ])->assertRedirect(route('clientes.show', $cliente));

        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'apellidos' => 'NuevoApellido']);
    }

    public function test_gestor_puede_hacer_soft_delete(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->delete(route('clientes.destroy', $cliente));

        $this->assertSoftDeleted('clientes', ['id' => $cliente->id]);
    }

    public function test_cliente_no_puede_acceder_al_crud(): void
    {
        $user = User::factory()->create(['role' => 'cliente']);

        $this->actingAs($user)->get(route('clientes.index'))->assertForbidden();
        $this->actingAs($user)->get(route('clientes.create'))->assertForbidden();
    }

    public function test_store_requiere_apellidos_y_nombre(): void
    {
        $gestor = User::factory()->gestor()->create();

        $this->actingAs($gestor)->post(route('clientes.store'), [])
             ->assertSessionHasErrors(['apellidos', 'nombre']);
    }
}
