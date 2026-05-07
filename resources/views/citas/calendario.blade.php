@extends('layouts.app')
@section('title', 'Calendario de citas — Clínica Dental Mula')
@section('page-title', 'Calendario de citas')

@section('topbar-actions')
    <button class="btn btn-success" onclick="document.getElementById('modal-nueva-cita').classList.add('open')">
        📅 Nueva cita
    </button>
@endsection

@push('styles')
<style>
#calendario { max-width:100%; }
.fc-event { border-radius:5px !important; font-size:.82rem !important; padding:2px 5px !important; border:none !important; }
.fc-toolbar-title { font-family:'Playfair Display',serif !important; font-size:1.2rem !important; }
.fc-button-primary { background:var(--primary) !important; border-color:var(--primary) !important; }
.fc-button-primary:not(:disabled):hover { background:var(--secondary) !important; }
</style>
@endpush

@section('content')
<div class="card">
    <div id="calendario"></div>
</div>

{{-- ── MODAL nueva cita ─────────────────────────────────────────────────────── --}}
<div class="modal-backdrop" id="modal-nueva-cita">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <h3>📅 Nueva cita</h3>
            <button class="modal-close" onclick="document.getElementById('modal-nueva-cita').classList.remove('open')">×</button>
        </div>
        <form method="POST" action="{{ route('citas.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Paciente *</label>
                <select class="form-control" name="cliente_id" required>
                    <option value="">— Selecciona un paciente —</option>
                    @foreach($clientes as $c)
                    <option value="{{ $c->id }}">{{ $c->apellidos }}, {{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Fecha y hora *</label>
                <input class="form-control" type="datetime-local" name="fecha_hora" id="fc-fecha-input" required>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Duración (min)</label>
                    <select class="form-control" name="duracion_minutos">
                        <option value="15">15 min</option>
                        <option value="30" selected>30 min</option>
                        <option value="45">45 min</option>
                        <option value="60">1 hora</option>
                        <option value="90">1h 30 min</option>
                        <option value="120">2 horas</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select class="form-control" name="estado">
                        <option value="confirmada">Confirmada</option>
                        <option value="pendiente">Pendiente</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Motivo *</label>
                <input class="form-control" type="text" name="motivo" required
                       placeholder="Revisión, empaste, extracción, ortodoncia…">
            </div>
            <div class="form-group">
                <label class="form-label">Notas</label>
                <textarea class="form-control" name="notas" rows="2"
                          placeholder="Información adicional para la cita…"></textarea>
            </div>
            <button type="submit" class="btn btn-success" style="width:100%;justify-content:center;">
                ✅ Registrar cita
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cal = new FullCalendar.Calendar(document.getElementById('calendario'), {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today:    'Hoy',
            month:    'Mes',
            week:     'Semana',
            day:      'Día',
            list:     'Lista'
        },
        slotMinTime: '08:00:00',
        slotMaxTime: '21:00:00',
        allDaySlot: false,
        height: 'auto',
        events: function(info, successCallback, failureCallback) {
            fetch(`/citas/eventos?year=${info.start.getFullYear()}&month=${info.start.getMonth() + 1}`)
                .then(r => r.json())
                .then(data => successCallback(data))
                .catch(() => failureCallback());
        },
        eventClick: function(info) {
            const e = info.event;
            const props = e.extendedProps;
            const msg = [
                `Paciente: ${props.cliente_nombre}`,
                `Motivo: ${e.title.split(' — ')[1] || ''}`,
                `Estado: ${props.estado}`,
                `Inicio: ${e.start.toLocaleString('es-ES')}`,
            ].join('\n');
            alert(msg);
        },
        dateClick: function(info) {
            // Al hacer clic en un día, prellenar la fecha en el modal
            const dt = new Date(info.dateStr);
            dt.setHours(9, 0, 0, 0);
            const iso = dt.toISOString().slice(0, 16);
            document.getElementById('fc-fecha-input').value = iso;
            document.getElementById('modal-nueva-cita').classList.add('open');
        },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false },
    });

    cal.render();
});

// Cerrar modal al clic fuera
document.getElementById('modal-nueva-cita').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
@endpush
@endsection
