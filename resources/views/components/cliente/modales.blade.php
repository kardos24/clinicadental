@props(['cliente', 'dentadura'])

@if(auth()->user()->isGestor())
{{-- ── MODAL: Editar diente (multi-cara) ─────────────────────────────────── --}}
<div class="modal-backdrop" id="modal-diente">
    <div class="modal" style="max-width:460px;">
        <div class="modal-header">
            <h3>🦷 Diente <span id="diente-num-label"></span></h3>
            <button class="modal-close" onclick="cerrarModal('modal-diente')">×</button>
        </div>

        <div class="pieza-estado-section">
            <div class="form-group">
                <label class="form-label">Estado de la pieza</label>
                <select id="diente-pieza" class="form-control" onchange="toggleCaras()">
                    @foreach(App\Models\Dentadura::ESTADOS_PIEZA as $key => $est)
                    <option value="{{ $key }}">{{ $est['icono'] }} {{ $est['label'] }}</option>
                    @endforeach
                </select>
                <p style="font-size:.7rem;color:var(--text-light);margin-top:.25rem;">
                    Si la pieza no está presente, las caras se desactivan.
                </p>
            </div>
        </div>

        <div id="caras-section">
            <p style="font-size:.78rem;font-weight:600;color:var(--text-light);margin-bottom:.4rem;">Estado por cara</p>
            <div class="cara-grid">
                @foreach([
                    ['vestibular', 'Vestibular (exterior)'],
                    ['lingual',    'Lingual (interior)'],
                    ['mesial',     'Mesial'],
                    ['distal',     'Distal'],
                ] as [$cara, $label])
                <div class="cara-field">
                    <label>{{ $label }}</label>
                    <select id="diente-{{ $cara }}" class="form-control">
                        @foreach(App\Models\Dentadura::ESTADOS_CARA as $key => $est)
                        <option value="{{ $key }}">{{ $est['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                @endforeach
                <div class="cara-field full-width" id="campo-oclusal">
                    <label>Oclusal (masticación) — solo premolares y molares</label>
                    <select id="diente-oclusal" class="form-control">
                        @foreach(App\Models\Dentadura::ESTADOS_CARA as $key => $est)
                        <option value="{{ $key }}">{{ $est['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-top:.75rem;">
            <label class="form-label">Notas</label>
            <textarea id="diente-notas" class="form-control" rows="2" placeholder="Observaciones sobre este diente…"></textarea>
        </div>
        <button class="btn btn-primary" style="width:100%;margin-top:.5rem;" onclick="guardarDiente()">Guardar</button>
    </div>
</div>

{{-- ── MODAL: Nuevo registro historial ────────────────────────────────────── --}}
<div class="modal-backdrop" id="modal-historial">
    <div class="modal" style="max-width:640px;">
        <div class="modal-header">
            <h3>📋 Añadir registro al historial</h3>
            <button class="modal-close" onclick="cerrarModal('modal-historial')">×</button>
        </div>
        <form method="POST" action="{{ route('historial.store', $cliente) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
                <div class="form-group">
                    <label class="form-label">Día</label>
                    <input class="form-control" type="number" name="dia" min="1" max="31" value="{{ now()->day }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mes</label>
                    <input class="form-control" type="number" name="mes" min="1" max="12" value="{{ now()->month }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Año</label>
                    <input class="form-control" type="number" name="anio" value="{{ now()->year }}" required>
                </div>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Signo / Código</label>
                    <input class="form-control" type="text" name="signo" placeholder="CAR, IMP…">
                </div>
                <div class="form-group">
                    <label class="form-label">Nº Sesiones</label>
                    <input class="form-control" type="number" name="num_sesiones" value="1" min="1">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Diagnóstico *</label>
                <textarea class="form-control" name="diagnostico" rows="2" required placeholder="Descripción del diagnóstico…"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Tratamiento realizado</label>
                <textarea class="form-control" name="tratamiento_realizado" rows="2"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;">
                @foreach([['importe','Importe €'],['recibo','Nº Recibo'],['debe','Debe €'],['haber','Haber €']] as [$n,$l])
                <div class="form-group">
                    <label class="form-label">{{ $l }}</label>
                    <input class="form-control" type="{{ in_array($n,['importe','debe','haber']) ? 'number' : 'text' }}"
                           name="{{ $n }}" step="0.01" min="0" value="0">
                </div>
                @endforeach
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Guardar registro</button>
        </form>
    </div>
</div>

{{-- ── MODAL: Nueva cita ───────────────────────────────────────────────────── --}}
<div class="modal-backdrop" id="modal-cita">
    <div class="modal" style="max-width:440px;">
        <div class="modal-header">
            <h3>📅 Nueva cita para {{ $cliente->nombre }}</h3>
            <button class="modal-close" onclick="cerrarModal('modal-cita')">×</button>
        </div>
        <form method="POST" action="{{ route('citas.store') }}">
            @csrf
            <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
            <div class="form-group">
                <label class="form-label">Fecha y hora *</label>
                <input class="form-control" type="datetime-local" name="fecha_hora" required>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Duración (min)</label>
                    <input class="form-control" type="number" name="duracion_minutos" value="30" min="15" step="15">
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
                <input class="form-control" type="text" name="motivo" required placeholder="Revisión, empaste, limpieza…">
            </div>
            <div class="form-group">
                <label class="form-label">Notas</label>
                <textarea class="form-control" name="notas" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-success" style="width:100%;">Registrar cita</button>
        </form>
    </div>
</div>
@endif
