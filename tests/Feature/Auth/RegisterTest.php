<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_valido_crea_usuario_cliente(): void
    {
        $this->post('/registro', [
            'name'                  => 'Juan García',
            'email'                 => 'juan@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('citas.mis-citas'));

        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
            'role'  => 'cliente',
        ]);
    }

    public function test_email_duplicado_falla_validacion(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->post('/registro', [
            'name'                  => 'Otro',
            'email'                 => 'existing@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors('email');
    }

    public function test_password_sin_confirmar_falla(): void
    {
        $this->post('/registro', [
            'name'                  => 'Juan',
            'email'                 => 'juan@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Different123!',
        ])->assertSessionHasErrors('password');
    }

    public function test_password_corto_falla(): void
    {
        $this->post('/registro', [
            'name'                  => 'Juan',
            'email'                 => 'juan@example.com',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');
    }
}
