@extends('layouts.app')
@section('title', 'Acceder — Clínica Dental Mula')

@section('content')
<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:2rem;">
    <div style="width:100%;max-width:420px;">
        <div style="text-align:center;margin-bottom:2rem;">
            <div style="font-size:3rem;margin-bottom:.5rem;">🦷</div>
            <h1 style="font-size:1.8rem;">Clínica Dental Mula</h1>
            <p style="color:var(--text-light);font-size:.95rem;">Accede a tu área personal</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <input class="form-control" type="email" id="email" name="email"
                           value="{{ old('email') }}" required autofocus
                           placeholder="tu@email.com">
                    @error('email')<p style="color:var(--danger);font-size:.82rem;margin-top:.3rem">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <input class="form-control" type="password" id="password" name="password"
                           required placeholder="••••••••">
                </div>

                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;">
                    <input type="checkbox" id="remember" name="remember" style="width:16px;height:16px;">
                    <label for="remember" style="font-size:.9rem;cursor:pointer;">Recuérdame</label>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                    Entrar →
                </button>
            </form>

            <div style="text-align:center;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--gray-200);font-size:.9rem;color:var(--text-light);">
                ¿Eres paciente y no tienes cuenta?
                <a href="{{ route('register') }}" style="font-weight:600;">Regístrate aquí</a>
            </div>
        </div>
    </div>
</div>
@endsection
