@extends('layouts.app')
@section('title', (isset($cliente) ? 'Editar' : 'Nuevo') . ' cliente — Clínica Dental Mula')
@section('page-title', isset($cliente) ? 'Editar: ' . $cliente->nombre_completo : 'Nuevo cliente')

@section('content')
<div style="max-width:800px;">
    <div class="card">
        <form method="POST"
              action="{{ isset($cliente) ? route('clientes.update', $cliente) : route('clientes.store') }}">
            @csrf
            @if(isset($cliente)) @method('PUT') @endif

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Apellidos *</label>
                    <input class="form-control" type="text" name="apellidos"
                           value="{{ old('apellidos', $cliente->apellidos ?? '') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input class="form-control" type="text" name="nombre"
                           value="{{ old('nombre', $cliente->nombre ?? '') }}" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Edad</label>
                    <input class="form-control" type="number" name="edad" min="0" max="120"
                           value="{{ old('edad', $cliente->edad ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Profesión</label>
                    <input class="form-control" type="text" name="profesion"
                           value="{{ old('profesion', $cliente->profesion ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input class="form-control" type="tel" name="telefono"
                           value="{{ old('telefono', $cliente->telefono ?? '') }}"
                           placeholder="600 000 000">
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Dirección</label>
                    <input class="form-control" type="text" name="direccion"
                           value="{{ old('direccion', $cliente->direccion ?? '') }}"
                           placeholder="C/ Nombre, Nº — Municipio">
                </div>
                <div class="form-group">
                    <label class="form-label">Código Postal</label>
                    <input class="form-control" type="text" name="cp" maxlength="5"
                           value="{{ old('cp', $cliente->cp ?? '') }}" placeholder="30170">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Observaciones</label>
                <textarea class="form-control" name="observaciones" rows="3"
                          placeholder="Alergias, condiciones médicas relevantes, anotaciones…">{{ old('observaciones', $cliente->observaciones ?? '') }}</textarea>
            </div>

            <div style="display:flex;gap:1rem;margin-top:.5rem;">
                <button type="submit" class="btn btn-primary">
                    {{ isset($cliente) ? '💾 Guardar cambios' : '➕ Crear cliente' }}
                </button>
                <a href="{{ isset($cliente) ? route('clientes.show', $cliente) : route('clientes.index') }}"
                   class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
