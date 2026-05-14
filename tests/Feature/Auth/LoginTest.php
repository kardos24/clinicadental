<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_redirige_a_dashboard(): void
    {
        $user = User::factory()->gestor()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
             ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_cliente_redirige_a_mis_citas(): void
    {
        $user = User::factory()->create(['role' => 'cliente']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
             ->assertRedirect(route('citas.mis-citas'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_credenciales_incorrectas_devuelven_error(): void
    {
        User::factory()->create(['email' => 'test@test.com']);

        $this->post('/login', ['email' => 'test@test.com', 'password' => 'wrong'])
             ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_cuenta_desactivada_no_puede_entrar(): void
    {
        $user = User::factory()->inactivo()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
             ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_usuario_autenticado_es_redirigido_desde_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertRedirect();
    }
}
