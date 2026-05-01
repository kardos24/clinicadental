@extends('layouts.app')
@section('title', 'Mis citas — Clínica Dental Mula')
@section('page-title', 'Mis citas')

@section('content')

@if($proximas->isEmpty() && $anteriores->isEmpty())
<div class="card" style="text-align:center;padding:3rem;">
    <div style="font-size:3rem;margin-bottom:1rem;">📅</div>
    <h3>No tienes citas registradas</h3>
    <p style="color:var(--texto-med);margin:.75rem 0 1.5rem;">
        Puedes solicitar una cita y el equipo de la clínica la confirmará en breve.
    </p>
    <button class="btn btn-success" onclick="document.getElementById('modal-solicitar').classList.add('open')">
        📅 Solicitar cita
    </button>
</div>
@else
<div style="display:flex;justify-content:flex-end;margin-bottom:1rem;">
    <button class="btn btn-success" onclick="document.getElementById('modal-solicitar').classList.add('open')">
        📅 Solicitar nueva cita
    </button>
</div>
@endif

{{-- Próximas citas --}}
@if($proximas->isNotEmpty())
<h3 style="margin-bottom:1rem;">Próximas citas</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;margin-bottom:2rem;">
    @foreach($proximas as $cita)
    <div class="card" style="border-left:4px solid {{ $cita->estado_color }};">
        <div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:.75rem;">
            <div>
                <div style="font-size:1.15rem;font-weight:700;">{{ $cita->fecha_hora->format('d/m/Y') }}</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--azul);">{{ $cita->fecha_hora->format('H:i') }}</div>
            </div>
            <span class="badge badge-{{ $cita->estado }}">{{ $cita->estado_label }}</span>
        </div>
        <p style="font-weight:600;margin-bottom:.3rem;">{{ $cita->motivo }}</p>
        <p style="font-size:.85rem;color:var(--texto-med);">Duración: {{ $cita->duracion_minutos }} min</p>
        @if($cita->notas)
        <p style="font-size:.82rem;background:var(--gris-fondo);padding:.5rem;border-radius:5px;margin-top:.5rem;">
            {{ $cita->notas }}
        </p>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- Historial de citas anteriores --}}
@if($anteriores->isNotEmpty())
<h3 style="margin-bottom:1rem;">Citas anteriores</h3>
<div class="card" style="padding:0;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Fecha</th><th>Motivo</th><th>Duración</th><th>Estado</th></tr>
            </thead>
            <tbody>
                @foreach($anteriores as $cita)
                <tr>
                    <td>{{ $cita->fecha_hora_formateada }}</td>
                    <td>{{ $cita->motivo }}</td>
                    <td>{{ $cita->duracion_minutos }} min</td>
                    <td><span class="badge badge-{{ $cita->estado }}">{{ $cita->estado_label }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($anteriores->hasPages())
    <div style="padding:1rem 1.5rem;">{{ $anteriores->links() }}</div>
    @endif
</div>
@endif

{{-- Modal solicitar cita --}}
<div class="modal-backdrop" id="modal-solicitar">
    <div class="modal" style="max-width:440px;">
        <div class="modal-header">
            <h3>📅 Solicitar cita</h3>
            <button class="modal-close" onclick="document.getElementById('modal-solicitar').classList.remove('open')">×</button>
        </div>
        <p style="font-size:.88rem;color:var(--texto-med);margin-bottom:1rem;background:var(--azul-claro);padding:.75rem;border-radius:6px;">
            ℹ️ Tu solicitud quedará como <strong>Pendiente</strong> hasta que el equipo de la clínica la confirme.
            Recibirás una notificación cuando se confirme.
        </p>
        <form method="POST" action="{{ route('citas.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Fecha y hora preferida *</label>
                <input class="form-control" type="datetime-local" name="fecha_hora" required
                       min="{{ now()->addHour()->format('Y-m-d\TH:i') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Motivo de la visita *</label>
                <input class="form-control" type="text" name="motivo" required
                       placeholder="Revisión, dolor, limpieza, ortodoncia…">
            </div>
            <div class="form-group">
                <label class="form-label">Información adicional</label>
                <textarea class="form-control" name="notas" rows="3"
                          placeholder="Cuéntanos más sobre tu consulta…"></textarea>
            </div>
            <button type="submit" class="btn btn-success" style="width:100%;justify-content:center;">
                Enviar solicitud
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('modal-solicitar').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
@endpush
@endsection
