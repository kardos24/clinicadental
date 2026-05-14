@extends('layouts.app')
@section('title', 'Clientes — Clínica Dental Mula')
@section('page-title', 'Listado de clientes')

@section('topbar-actions')
    <a href="{{ route('clientes.create') }}" class="btn btn-success">➕ Nuevo cliente</a>
@endsection

@section('content')

{{-- ── BUSCADOR ────────────────────────────────────────────────────────────── --}}
<form method="GET" style="margin-bottom:1.5rem;display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
    <input class="form-control" style="max-width:320px;" type="search" name="buscar"
           placeholder="Buscar por nombre, apellidos, nº filiación o teléfono…"
           value="{{ $buscar ?? '' }}">
    <button type="submit" class="btn btn-primary">🔍 Buscar</button>
    @if($buscar)
        <a href="{{ route('clientes.index') }}" class="btn btn-outline">✕ Limpiar</a>
    @endif
</form>

{{-- ── TABLA ───────────────────────────────────────────────────────────────── --}}
<div class="card card-primary" style="padding:0;margin-bottom:1rem;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nº Filiación</th>
                    <th>Paciente</th>
                    <th>Edad</th>
                    <th>Teléfono</th>
                    <th>Citas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr>
                    <td>
                        <span style="font-family:monospace;font-size:.85rem;background:var(--primary-light);
                              padding:.15rem .5rem;border-radius:4px;color:var(--primary);">
                            {{ $cliente->num_filiacion ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <strong>{{ $cliente->apellidos }}</strong>, {{ $cliente->nombre }}
                        @if($cliente->user_id)
                            <span class="badge badge-cliente" style="margin-left:.4rem;">App</span>
                        @endif
                    </td>
                    <td>{{ $cliente->edad ? $cliente->edad . ' años' : '—' }}</td>
                    <td>
                        @if($cliente->telefono)
                            <a href="tel:{{ $cliente->telefono }}" style="font-family:monospace;">
                                {{ $cliente->telefono }}
                            </a>
                        @else — @endif
                    </td>
                    <td>
                        <span class="badge {{ $cliente->citas_count > 0 ? 'badge-confirmada' : '' }}"
                              style="{{ $cliente->citas_count == 0 ? 'color:var(--text-light)' : '' }}">
                            {{ $cliente->citas_count }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:.4rem;">
                            <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-outline btn-sm">Ver</a>
                            <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary btn-sm">Editar</a>
                            <form method="POST" action="{{ route('clientes.destroy', $cliente) }}"
                                  onsubmit="return confirm('¿Eliminar a {{ $cliente->nombre_completo }}? Esta acción se puede deshacer.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Borrar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:var(--text-light);">
                        @if($buscar)
                            No se encontraron pacientes con "{{ $buscar }}"
                        @else
                            Aún no hay clientes registrados.
                            <a href="{{ route('clientes.create') }}">Añadir el primero</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($clientes->hasPages())
    <div style="padding:1rem 1.5rem;border-top:1px solid var(--gray-200);">
        {{ $clientes->links() }}
    </div>
    @endif
</div>

<p style="margin-top:.75rem;font-size:.85rem;color:var(--text-light);">
    Total: <strong>{{ $clientes->total() }}</strong> pacientes
</p>
@endsection
