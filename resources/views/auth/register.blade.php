@extends('layouts.app')
@section('title', 'Crear cuenta — Clínica Dental Mula')

@section('content')
<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:2rem;">
    <div style="width:100%;max-width:460px;">
        <div style="text-align:center;margin-bottom:2rem;">
            <div style="font-size:3rem;margin-bottom:.5rem;">📋</div>
            <h1 style="font-size:1.8rem;">Crear cuenta de paciente</h1>
            <p style="color:var(--text-light);font-size:.95rem;">
                Regístrate para gestionar tus citas y consultar tu historial.
            </p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="name">Nombre completo</label>
                    <input class="form-control" type="text" id="name" name="name"
                           value="{{ old('name') }}" required autofocus>
                    @error('name')<p style="color:var(--danger);font-size:.82rem;margin-top:.3rem">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <input class="form-control" type="email" id="email" name="email"
                           value="{{ old('email') }}" required placeholder="tu@email.com">
                    @error('email')<p style="color:var(--danger);font-size:.82rem;margin-top:.3rem">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <input class="form-control" type="password" id="password" name="password" required placeholder="Mínimo 8 caracteres">
                    @error('password')<p style="color:var(--danger);font-size:.82rem;margin-top:.3rem">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Repite la contraseña</label>
                    <input class="form-control" type="password" id="password_confirmation"
                           name="password_confirmation" required>
                </div>

                <div style="background:var(--primary-light);border-radius:8px;padding:.75rem;font-size:.82rem;color:var(--primary);margin-bottom:1.25rem;">
                    🔒 Tus datos están protegidos según el RGPD. Solo el personal de la clínica y tú tenéis acceso a tu información clínica.
                </div>

                <button type="submit" class="btn btn-success" style="width:100%;justify-content:center;">
                    Crear mi cuenta →
                </button>
            </form>

            <div style="text-align:center;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--gray-200);font-size:.9rem;color:var(--text-light);">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}" style="font-weight:600;">Inicia sesión</a>
            </div>
        </div>
    </div>
</div>
@endsection
