<?php

namespace Tests\Feature\Api;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiClientesTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_puede_listar_clientes(): void
    {
        $gestor = User::factory()->gestor()->create();
        Cliente::factory()->count(3)->create();
        $token  = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/clientes')
             ->assertOk()
             ->assertJsonStructure(['data']);
    }

    public function test_cliente_no_puede_listar_clientes(): void
    {
        $user  = User::factory()->create(['role' => 'cliente']);
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/clientes')->assertForbidden();
    }

    public function test_gestor_puede_buscar_clientes(): void
    {
        $gestor = User::factory()->gestor()->create();
        Cliente::factory()->create(['apellidos' => 'Garcia']);
        Cliente::factory()->create(['apellidos' => 'Lopez']);
        $token  = $gestor->createToken('app')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/clientes?buscar=Garcia');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_gestor_puede_ver_cualquier_cliente(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $token   = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$cliente->id}")->assertOk();
    }

    public function test_cliente_puede_ver_su_propio_perfil(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$cliente->id}")->assertOk();
    }

    public function test_cliente_no_puede_ver_perfil_ajeno(): void
    {
        $user  = User::factory()->create(['role' => 'cliente']);
        $ajeno = Cliente::factory()->create();
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$ajeno->id}")->assertForbidden();
    }

    public function test_gestor_puede_ver_historial(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $token   = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$cliente->id}/historial")->assertOk();
    }

    public function test_propietario_puede_ver_su_historial(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$cliente->id}/historial")->assertOk();
    }

    public function test_otro_cliente_no_puede_ver_historial_ajeno(): void
    {
        $user  = User::factory()->create(['role' => 'cliente']);
        $ajeno = Cliente::factory()->create();
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$ajeno->id}/historial")->assertForbidden();
    }

    public function test_gestor_puede_ver_dentadura(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $token   = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$cliente->id}/dentadura")->assertOk();
    }

    public function test_propietario_puede_ver_su_dentadura(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$cliente->id}/dentadura")->assertOk();
    }

    public function test_otro_cliente_no_puede_ver_dentadura_ajena(): void
    {
        $user  = User::factory()->create(['role' => 'cliente']);
        $ajeno = Cliente::factory()->create();
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$ajeno->id}/dentadura")->assertForbidden();
    }
}