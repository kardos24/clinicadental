<div class="card">
    <div class="card-header">
        <h3 class="card-title">📅 Próximas citas</h3>
        @if(auth()->user()->isGestor())
            <button class="btn btn-success btn-sm" onclick="abrirModalCita()">+ Cita</button>
        @endif
    </div>
    @forelse($citas as $cita)
    <div style="display:flex;align-items:start;gap:.75rem;padding:.6rem 0;border-bottom:1px solid var(--gray-200);">
        <div style="flex:1;">
            <div style="font-weight:600;font-size:.9rem;">{{ $cita->fecha_hora_formateada }}</div>
            <div style="font-size:.82rem;color:var(--text-light);">{{ $cita->motivo }}</div>
        </div>
        <span class="badge badge-{{ $cita->estado }}">{{ $cita->estado_label }}</span>
    </div>
    @empty
    <p style="color:var(--text-light);font-size:.88rem;text-align:center;padding:1rem;">Sin citas próximas</p>
    @endforelse
</div>
