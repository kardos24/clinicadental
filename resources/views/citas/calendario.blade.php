@extends('layouts.app')
@section('title', 'Calendario de citas — Clínica Dental Mula')
@section('page-title', 'Calendario de citas')

@section('topbar-actions')
    <button class="btn btn-success" onclick="abrirModalNuevaCita(null)">
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

/* Día seleccionado */
.fc-daygrid-day.fc-day-selected,
.fc-timegrid-col.fc-day-selected { background: rgba(37,99,235,.08) !important; }

/* Popup contextual */
.day-popup {
    position: fixed;
    z-index: 9999;
    width: 292px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}
.day-popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 11px 15px;
    background: var(--primary);
    color: #fff;
}
.day-popup-header span {
    font-weight: 600;
    font-size: .87rem;
    text-transform: capitalize;
}
.day-popup-close {
    background: none;
    border: none;
    color: rgba(255,255,255,.75);
    font-size: 1.25rem;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    transition: color .15s;
}
.day-popup-close:hover { color: #fff; }
.day-popup-body {
    padding: 10px 14px;
    max-height: 230px;
    overflow-y: auto;
}
.day-popup-empty {
    color: #94a3b8;
    font-size: .83rem;
    margin: 2px 0 6px;
}
.day-popup-cita {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 5px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: .82rem;
}
.day-popup-cita:last-child { border-bottom: none; }
.day-popup-cita-bar {
    width: 3px;
    min-height: 32px;
    border-radius: 2px;
    flex-shrink: 0;
    align-self: stretch;
}
.day-popup-cita-hora {
    color: #64748b;
    white-space: nowrap;
    font-size: .78rem;
    padding-top: 2px;
    min-width: 34px;
}
.day-popup-cita-info { flex: 1; min-width: 0; }
.day-popup-cita-paciente {
    display: block;
    color: #1e293b;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
}
.day-popup-cita-motivo {
    display: block;
    color: #64748b;
    font-size: .75rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.day-popup-footer {
    padding: 8px 14px 13px;
    border-top: 1px solid #f1f5f9;
}

/* Modal detalle */
#detalle-header { transition: background .2s; }
#detalle-header .modal-close { color: rgba(255,255,255,.75); }
#detalle-header .modal-close:hover { color: #fff; }
.detalle-fila {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 10px;
}
.detalle-label {
    color: #64748b;
    font-size: .82rem;
    width: 70px;
    flex-shrink: 0;
}
.detalle-valor {
    font-size: .9rem;
    color: #1e293b;
}
.detalle-seccion {
    border-top: 1px solid #f1f5f9;
    padding-top: 14px;
    margin-top: 14px;
}
.detalle-seccion-titulo {
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #94a3b8;
    margin-bottom: 10px;
}
#detalle-botones-estado { display: flex; flex-wrap: wrap; gap: 6px; }
.detalle-btn-estado {
    font-size: .8rem;
    padding: 5px 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #1e293b;
    border-radius: 6px;
    cursor: pointer;
    transition: opacity .15s;
}
.detalle-btn-estado:hover { opacity: .8; }
.detalle-btn-estado:disabled { opacity: .5; cursor: not-allowed; }
#detalle-btn-eliminar {
    background: #fef2f2;
    color: #ef4444;
    border: 1px solid #fecaca;
    font-size: .82rem;
    transition: background .15s, color .15s;
}
</style>
@endpush

@section('content')
<div class="card">
    <div id="calendario"></div>
</div>

{{-- ── POPUP contextual de día ──────────────────────────────────────────────── --}}
<div id="day-popup" class="day-popup" style="display:none;">
    <div class="day-popup-header">
        <span id="day-popup-title"></span>
        <button class="day-popup-close" id="day-popup-close">×</button>
    </div>
    <div class="day-popup-body">
        <div id="day-popup-citas"></div>
    </div>
    <div class="day-popup-footer">
        <button class="btn btn-success" id="day-popup-add"
                style="width:100%;justify-content:center;font-size:.84rem;padding:7px 14px;">
            📅 Nueva cita
        </button>
    </div>
</div>

{{-- ── MODAL detalle de cita ─────────────────────────────────────────────────────────── --}}
<div class="modal-backdrop" id="modal-detalle-cita">
    <div class="modal" style="max-width:440px;">
        <div class="modal-header" id="detalle-header">
            <div>
                <span id="detalle-estado-badge"
                      style="font-size:.78rem;opacity:.85;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:2px;"></span>
                <span id="detalle-fecha" style="font-weight:600;font-size:.95rem;"></span>
            </div>
            <button class="modal-close" id="detalle-close">×</button>
        </div>
        <div style="padding:18px 22px 20px;">
            <div class="detalle-fila">
                <span class="detalle-label">Paciente</span>
                <a id="detalle-paciente-link" href="#" target="_blank"
                   style="font-weight:500;color:var(--primary);font-size:.9rem;text-decoration:none;"></a>
            </div>
            <div class="detalle-fila">
                <span class="detalle-label">Motivo</span>
                <span id="detalle-motivo" class="detalle-valor"></span>
            </div>
            <div class="detalle-fila">
                <span class="detalle-label">Duración</span>
                <span id="detalle-duracion" class="detalle-valor"></span>
            </div>
            <div class="detalle-fila" style="margin-bottom:0;">
                <span class="detalle-label">Notas</span>
                <span id="detalle-notas" class="detalle-valor" style="color:#64748b;font-style:italic;"></span>
            </div>

            <div class="detalle-seccion">
                <p class="detalle-seccion-titulo">Cambiar estado</p>
                <div id="detalle-botones-estado"></div>
            </div>

            <div class="detalle-seccion" style="display:flex;justify-content:flex-end;">
                <button id="detalle-btn-eliminar" class="btn">🗑 Eliminar cita</button>
            </div>
        </div>
    </div>
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
    let selectedDate    = null;   // Date object del día seleccionado
    let selectedDateStr = null;   // 'YYYY-MM-DD'

    const popup = document.getElementById('day-popup');

    // ── FullCalendar ──────────────────────────────────────────────────────────
    const cal = new FullCalendar.Calendar(document.getElementById('calendario'), {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Lista'
        },
        slotMinTime: '08:00:00',
        slotMaxTime: '21:00:00',
        allDaySlot: false,
        height: 'auto',

        events: function(info, successCallback, failureCallback) {
            fetch(`/citas/eventos?start=${info.startStr}&end=${info.endStr}`)
                .then(r => r.json())
                .then(data => successCallback(data))
                .catch(() => failureCallback());
        },

        // Re-aplica el highlight al navegar entre meses/vistas
        datesSet: function() {
            setTimeout(resaltarDiaSeleccionado, 0);
        },

        eventClick: function(info) {
            info.jsEvent.stopPropagation();
            abrirModalDetalle(info.event);
        },

        dateClick: function(info) {
            const dateOnly = info.dateStr.slice(0, 10);
            selectedDate    = info.date;
            selectedDateStr = dateOnly;

            // Navegar al día clickado — FullCalendar usa esta fecha al cambiar de vista
            cal.gotoDate(info.date);

            resaltarDiaSeleccionado();
            mostrarPopup(info);
        },

        eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false },
    });

    cal.render();

    // ── Highlight ─────────────────────────────────────────────────────────────

    function resaltarDiaSeleccionado() {
        document.querySelectorAll('.fc-day-selected')
                .forEach(el => el.classList.remove('fc-day-selected'));
        if (!selectedDateStr) return;
        document.querySelectorAll(`[data-date="${selectedDateStr}"]`)
                .forEach(el => el.classList.add('fc-day-selected'));
    }

    // ── Popup ─────────────────────────────────────────────────────────────────

    function mostrarPopup(info) {
        const dateOnly = info.dateStr.slice(0, 10);

        // Título
        const opts = { weekday: 'long', day: 'numeric', month: 'long' };
        document.getElementById('day-popup-title').textContent =
            info.date.toLocaleDateString('es-ES', opts);

        // Citas del día (ya cargadas en FullCalendar)
        const eventosDia = cal.getEvents()
            .filter(e => e.startStr.startsWith(dateOnly))
            .sort((a, b) => a.start - b.start);

        const container = document.getElementById('day-popup-citas');
        if (eventosDia.length === 0) {
            container.innerHTML = '<p class="day-popup-empty">Sin citas este día</p>';
        } else {
            container.innerHTML = eventosDia.map(e => {
                const hora   = e.start.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
                const color  = e.backgroundColor || '#6b7280';
                const partes = e.title.split(' — ');
                return `<div class="day-popup-cita">
                    <span class="day-popup-cita-bar" style="background:${color}"></span>
                    <span class="day-popup-cita-hora">${hora}</span>
                    <span class="day-popup-cita-info">
                        <span class="day-popup-cita-paciente">${partes[0]}</span>
                        ${partes[1] ? `<span class="day-popup-cita-motivo">${partes[1]}</span>` : ''}
                    </span>
                </div>`;
            }).join('');
        }

        posicionarPopup(info.jsEvent);
        popup.style.display = 'block';
    }

    function posicionarPopup(evt) {
        // Medir antes de posicionar
        popup.style.visibility = 'hidden';
        popup.style.display    = 'block';

        const pw = popup.offsetWidth;
        const ph = popup.offsetHeight;
        const vw = window.innerWidth;
        const vh = window.innerHeight;
        const cx = evt.clientX;
        const cy = evt.clientY;

        let left = cx + 14;
        let top  = cy + 14;

        if (left + pw > vw - 10) left = cx - pw - 14;
        if (top  + ph > vh - 10) top  = cy - ph - 14;
        if (top  < 10) top  = 10;
        if (left < 10) left = 10;

        popup.style.left       = left + 'px';
        popup.style.top        = top  + 'px';
        popup.style.visibility = 'visible';
    }

    function cerrarPopup() { popup.style.display = 'none'; }

    // Cerrar popup con botón ×
    document.getElementById('day-popup-close').addEventListener('click', cerrarPopup);

    // Cerrar popup al clic fuera
    document.addEventListener('click', function(e) {
        if (popup.style.display !== 'none' && !popup.contains(e.target)) {
            cerrarPopup();
        }
    });

    // ── "Nueva cita" desde popup ──────────────────────────────────────────────

    document.getElementById('day-popup-add').addEventListener('click', function() {
        cerrarPopup();
        abrirModalNuevaCita(selectedDate);
    });

    // ── Modal ─────────────────────────────────────────────────────────────────

    document.getElementById('modal-nueva-cita').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
});

const COLORES_ESTADO = {
    pendiente:     '#f97316',
    confirmada:    '#3b82f6',
    realizada:     '#4ade80',
    cancelada:     '#ef4444',
    no_presentado: '#6b7280',
};

const LABELS_ESTADO = {
    pendiente:     'Pendiente',
    confirmada:    'Confirmada',
    realizada:     'Realizada',
    cancelada:     'Cancelada',
    no_presentado: 'No se presentó',
};

let citaActual = null;

function abrirModalDetalle(fcEvent) {
    citaActual = fcEvent;
    const props  = fcEvent.extendedProps;
    const estado = props.estado;
    const color  = COLORES_ESTADO[estado] || '#6b7280';

    document.getElementById('detalle-header').style.background = color;
    document.getElementById('detalle-estado-badge').textContent = LABELS_ESTADO[estado] || estado;

    const optsDate = { weekday: 'long', day: 'numeric', month: 'long' };
    const optsTime = { hour: '2-digit', minute: '2-digit' };
    const fechaStr = fcEvent.start.toLocaleDateString('es-ES', optsDate);
    const horaIni  = fcEvent.start.toLocaleTimeString('es-ES', optsTime);
    const horaFin  = fcEvent.end ? fcEvent.end.toLocaleTimeString('es-ES', optsTime) : '';
    document.getElementById('detalle-fecha').textContent =
        horaFin ? `${fechaStr} · ${horaIni}–${horaFin}` : `${fechaStr} · ${horaIni}`;

    const link = document.getElementById('detalle-paciente-link');
    link.textContent = props.cliente_nombre;
    link.href = `/clientes/${props.cliente_id}`;

    document.getElementById('detalle-motivo').textContent = props.motivo || '—';
    document.getElementById('detalle-notas').textContent  = props.notas  || '—';

    const durMin = (fcEvent.end && fcEvent.start)
        ? Math.round((fcEvent.end - fcEvent.start) / 60000)
        : null;
    document.getElementById('detalle-duracion').textContent = durMin ? `${durMin} min` : '—';

    document.getElementById('detalle-botones-estado').innerHTML =
        Object.entries(LABELS_ESTADO)
            .filter(([key]) => key !== estado)
            .map(([key, label]) =>
                `<button class="detalle-btn-estado" onclick="cambiarEstadoCita('${key}', this)">${label}</button>`
            ).join('');

    const btnElim = document.getElementById('detalle-btn-eliminar');
    btnElim.textContent         = '🗑 Eliminar cita';
    btnElim.disabled            = false;
    btnElim._confirmPending     = false;
    btnElim.style.background    = '#fef2f2';
    btnElim.style.color         = '#ef4444';
    btnElim.style.border        = '1px solid #fecaca';

    document.getElementById('modal-detalle-cita').classList.add('open');
}

function cambiarEstadoCita(nuevoEstado, btn) {
    if (!citaActual) return;
    const textoOriginal = btn.textContent;
    btn.disabled    = true;
    btn.textContent = 'Guardando…';

    fetch(`/citas/${citaActual.id}`, {
        method: 'PUT',
        headers: {
            'Content-Type':  'application/json',
            'Accept':        'application/json',
            'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ estado: nuevoEstado }),
    })
    .then(r => { if (!r.ok) throw new Error(); return r.json(); })
    .then(() => {
        citaActual.setExtendedProp('estado', nuevoEstado);
        citaActual.setProp('color', COLORES_ESTADO[nuevoEstado] || '#6b7280');
        document.getElementById('modal-detalle-cita').classList.remove('open');
    })
    .catch(() => {
        btn.disabled    = false;
        btn.textContent = textoOriginal;
        alert('No se pudo actualizar la cita. Recarga la página e inténtalo de nuevo.');
    });
}

function abrirModalNuevaCita(date) {
    const input = document.getElementById('fc-fecha-input');
    if (date) {
        const dt = new Date(date);
        if (dt.getHours() === 0 && dt.getMinutes() === 0) dt.setHours(9, 0, 0, 0);
        input.value = dt.toISOString().slice(0, 16);
    } else {
        input.value = '';
    }
    document.getElementById('modal-nueva-cita').classList.add('open');
}
</script>
@endpush
@endsection
