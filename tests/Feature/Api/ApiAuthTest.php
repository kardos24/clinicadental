<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_devuelve_token_y_datos_usuario(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertOk()
          ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);
    }

    public function test_login_credenciales_incorrectas_devuelve_401(): void
    {
        User::factory()->create(['email' => 'test@test.com']);

        $this->postJson('/api/login', [
            'email'    => 'test@test.com',
            'password' => 'wrong',
        ])->assertUnauthorized();
    }

    public function test_login_cuenta_desactivada_devuelve_403(): void
    {
        $user = User::factory()->inactivo()->create();

        $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertForbidden();
    }

    public function test_logout_elimina_el_token(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('app-movil')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')
             ->assertOk()
             ->assertJson(['ok' => true]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_registrar_token_fcm(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson('/api/dispositivo/token', [
            'token_fcm'  => 'firebase_token_abc123',
            'plataforma' => 'android',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('dispositivos_push', [
            'user_id'   => $user->id,
            'token_fcm' => 'firebase_token_abc123',
        ]);
    }

    public function test_segundo_registro_mismo_token_hace_upsert(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson('/api/dispositivo/token', ['token_fcm' => 'abc123']);
        $this->withToken($token)->postJson('/api/dispositivo/token', ['token_fcm' => 'abc123']);

        $this->assertDatabaseCount('dispositivos_push', 1);
    }

    public function test_rutas_protegidas_requieren_token(): void
    {
        $this->getJson('/api/clientes')->assertUnauthorized();
        $this->getJson('/api/mis-citas')->assertUnauthorized();
    }
}