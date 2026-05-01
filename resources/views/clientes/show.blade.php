@extends('layouts.app')
@section('title', $cliente->nombre_completo . ' — Clínica Dental Mula')
@section('page-title', $cliente->nombre_completo)

@section('topbar-actions')
    @if(auth()->user()->isGestor())
        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary btn-sm">✏️ Editar datos</a>
        <a href="{{ route('citas.calendario') }}" class="btn btn-outline btn-sm">📅 Calendario</a>
    @endif
@endsection

@push('styles')
<style>
/* ── ODONTOGRAMA ─────────────────────────────────────────────────────────── */
.odontograma { background:#fff; border-radius:12px; padding:1.5rem; border:1px solid var(--gris-borde); }
.dientes-row { display:flex; justify-content:center; gap:4px; margin:6px 0; flex-wrap:nowrap; }
.diente {
    width:38px; height:42px; border-radius:6px 6px 10px 10px;
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    cursor:pointer; border:2px solid #d1d9e6;
    font-size:.65rem; font-weight:700; transition:all .15s;
    position:relative; background:#f8faff;
}
.diente:hover { transform:scale(1.1); z-index:2; box-shadow:0 4px 12px rgba(0,0,0,.15); }
.diente .num { font-size:.6rem; color:rgba(0,0,0,.4); line-height:1; }
.diente .ico { font-size:.85rem; line-height:1.2; }
.diente.sano       { background:#f0fdf4; border-color:#4ade80; }
.diente.picado     { background:#fff7ed; border-color:#f97316; }
.diente.caries     { background:#fef2f2; border-color:#ef4444; }
.diente.partido    { background:#faf5ff; border-color:#a855f7; }
.diente.caido      { background:#f9fafb; border-color:#9ca3af; opacity:.6; }
.diente.puente     { background:#eff6ff; border-color:#3b82f6; }
.diente.sustituido { background:#fefce8; border-color:#eab308; }
.diente-separador { width:20px; }
.leyenda-item { display:flex; align-items:center; gap:.4rem; font-size:.8rem; }
.leyenda-color { width:14px; height:14px; border-radius:3px; flex-shrink:0; }
/* Modal diente */
#modal-diente select.form-control { width:100%; }
</style>
@endpush

@section('content')
<div style="display:grid;grid-template-columns:320px 1fr;gap:1.5rem;align-items:start;">

    {{-- ── DATOS PERSONALES ───────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">👤 Datos personales</h3>
                <span style="font-family:monospace;font-size:.8rem;background:var(--azul);color:#fff;
                      padding:.2rem .6rem;border-radius:4px;">{{ $cliente->num_filiacion ?? '—' }}</span>
            </div>
            @foreach([
                ['Apellidos',   $cliente->apellidos],
                ['Nombre',      $cliente->nombre],
                ['Edad',        $cliente->edad ? $cliente->edad . ' años' : '—'],
                ['Profesión',   $cliente->profesion ?? '—'],
                ['Dirección',   $cliente->direccion ?? '—'],
                ['CP',          $cliente->cp ?? '—'],
                ['Teléfono',    $cliente->telefono ?? '—'],
            ] as [$label, $val])
            <div style="display:flex;justify-content:space-between;padding:.5rem 0;
                        border-bottom:1px solid var(--gris-borde);font-size:.88rem;">
                <span style="color:var(--texto-med);font-weight:600;">{{ $label }}</span>
                <span style="text-align:right;max-width:60%;">{{ $val }}</span>
            </div>
            @endforeach

            @if($cliente->observaciones)
            <div style="margin-top:1rem;">
                <p style="font-size:.8rem;font-weight:600;color:var(--texto-med);margin-bottom:.3rem;">Observaciones</p>
                <p style="font-size:.88rem;background:var(--gris-fondo);padding:.75rem;border-radius:6px;">
                    {{ $cliente->observaciones }}
                </p>
            </div>
            @endif
        </div>

        {{-- ── PRÓXIMAS CITAS ──────────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📅 Próximas citas</h3>
                @if(auth()->user()->isGestor())
                    <button class="btn btn-success btn-sm" onclick="abrirModalCita()">+ Cita</button>
                @endif
            </div>
            @forelse($citasFuturas as $cita)
            <div style="display:flex;align-items:start;gap:.75rem;padding:.6rem 0;border-bottom:1px solid var(--gris-borde);">
                <div style="flex:1;">
                    <div style="font-weight:600;font-size:.9rem;">{{ $cita->fecha_hora_formateada }}</div>
                    <div style="font-size:.82rem;color:var(--texto-med);">{{ $cita->motivo }}</div>
                </div>
                <span class="badge badge-{{ $cita->estado }}">{{ $cita->estado_label }}</span>
            </div>
            @empty
            <p style="color:var(--texto-med);font-size:.88rem;text-align:center;padding:1rem;">Sin citas próximas</p>
            @endforelse
        </div>
    </div>

    {{-- ── COLUMNA DERECHA ─────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        {{-- ODONTOGRAMA ──────────────────────────────────────────────────────── --}}
        <div class="card odontograma">
            <div class="card-header">
                <h3 class="card-title">🦷 Odontograma</h3>
                <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                    @foreach(App\Models\Dentadura::ESTADOS as $key => $est)
                    <div class="leyenda-item">
                        <div class="leyenda-color" style="background:{{ $est['color'] }};border:1px solid #ddd;"></div>
                        <span>{{ $est['label'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Arcada superior --}}
            <p style="text-align:center;font-size:.75rem;color:var(--texto-med);margin-bottom:4px;">◀ Arcada superior ▶</p>
            <div class="dientes-row">
                @foreach(App\Models\Dentadura::DIENTES_SUPERIORES as $i => $num)
                    @if($i === 8)<div class="diente-separador"></div>@endif
                    @php $d = $dentadura[$num] ?? null; $estado = $d['estado'] ?? 'sano'; $icon = App\Models\Dentadura::ESTADOS[$estado]['icono'] ?? '?'; @endphp
                    <div class="diente {{ $estado }}"
                         onclick="@if(auth()->user()->isGestor())abrirModalDiente({{ $num }}, '{{ $estado }}', '{{ addslashes($d['notas'] ?? '') }}')@endif"
                         title="Diente {{ $num }} — {{ App\Models\Dentadura::ESTADOS[$estado]['label'] ?? $estado }}">
                        <span class="num">{{ $num }}</span>
                        <span class="ico">{{ $icon }}</span>
                    </div>
                @endforeach
            </div>

            <div style="border-top:2px dashed var(--gris-borde);margin:8px auto;width:90%;"></div>

            {{-- Arcada inferior --}}
            <div class="dientes-row">
                @foreach(App\Models\Dentadura::DIENTES_INFERIORES as $i => $num)
                    @if($i === 8)<div class="diente-separador"></div>@endif
                    @php $d = $dentadura[$num] ?? null; $estado = $d['estado'] ?? 'sano'; $icon = App\Models\Dentadura::ESTADOS[$estado]['icono'] ?? '?'; @endphp
                    <div class="diente {{ $estado }}"
                         onclick="@if(auth()->user()->isGestor())abrirModalDiente({{ $num }}, '{{ $estado }}', '{{ addslashes($d['notas'] ?? '') }}')@endif"
                         title="Diente {{ $num }} — {{ App\Models\Dentadura::ESTADOS[$estado]['label'] ?? $estado }}">
                        <span class="ico">{{ $icon }}</span>
                        <span class="num">{{ $num }}</span>
                    </div>
                @endforeach
            </div>
            <p style="text-align:center;font-size:.75rem;color:var(--texto-med);margin-top:4px;">◀ Arcada inferior ▶</p>

            @if(auth()->user()->isGestor())
            <p style="text-align:center;font-size:.78rem;color:var(--texto-med);margin-top:.75rem;">
                Haz clic en cualquier diente para cambiar su estado.
            </p>
            @endif
        </div>

        {{-- HISTORIAL CLÍNICO ─────────────────────────────────────────────── --}}
        <div class="card" style="padding:0;">
            <div class="card-header" style="padding:1.25rem 1.5rem;">
                <h3 class="card-title">📋 Historial clínico</h3>
                @if(auth()->user()->isGestor())
                <button class="btn btn-primary btn-sm" onclick="document.getElementById('modal-historial').classList.add('open')">
                    + Añadir registro
                </button>
                @endif
            </div>

            {{-- Resumen financiero --}}
            @php
                $totalDebe  = $historial->sum('debe');
                $totalHaber = $historial->sum('haber');
                $saldo      = $totalDebe - $totalHaber;
            @endphp
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-bottom:1px solid var(--gris-borde);">
                @foreach([['Debe total','€ ' . number_format($totalDebe,2),'var(--rojo)'],['Haber total','€ ' . number_format($totalHaber,2),'var(--verde)'],['Saldo','€ ' . number_format($saldo,2),$saldo > 0 ? 'var(--rojo)' : 'var(--verde)']] as [$l,$v,$c])
                <div style="padding:1rem;text-align:center;border-right:1px solid var(--gris-borde);">
                    <div style="font-size:.75rem;color:var(--texto-med);font-weight:600;">{{ $l }}</div>
                    <div style="font-size:1.1rem;font-weight:700;color:{{ $c }};">{{ $v }}</div>
                </div>
                @endforeach
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th><th>Signo</th><th>Diagnóstico</th>
                            <th>Ses.</th><th>Importe</th><th>Debe</th><th>Haber</th><th>Saldo</th>
                            @if(auth()->user()->isGestor())<th>Acc.</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historial as $reg)
                        <tr>
                            <td style="white-space:nowrap;font-size:.82rem;">{{ $reg->fecha_formateada }}</td>
                            <td><span style="font-family:monospace;font-size:.82rem;">{{ $reg->signo ?? '—' }}</span></td>
                            <td style="max-width:200px;font-size:.85rem;">{{ Str::limit($reg->diagnostico, 60) }}</td>
                            <td style="text-align:center;">{{ $reg->num_sesiones }}</td>
                            <td style="text-align:right;font-size:.85rem;">{{ number_format($reg->importe,2) }}€</td>
                            <td style="text-align:right;font-size:.85rem;color:var(--rojo);">{{ number_format($reg->debe,2) }}€</td>
                            <td style="text-align:right;font-size:.85rem;color:var(--verde);">{{ number_format($reg->haber,2) }}€</td>
                            <td style="text-align:right;font-size:.85rem;font-weight:600;color:{{ $reg->saldo > 0 ? 'var(--rojo)' : 'var(--verde)' }};">
                                {{ number_format($reg->saldo,2) }}€
                            </td>
                            @if(auth()->user()->isGestor())
                            <td>
                                <form method="POST" action="{{ route('historial.destroy', $reg) }}"
                                      onsubmit="return confirm('¿Eliminar este registro?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">✕</button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--texto-med);">Sin registros de historial.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($historial->hasPages())
            <div style="padding:1rem 1.5rem;">{{ $historial->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- ── MODAL: Editar diente ────────────────────────────────────────────────── --}}
@if(auth()->user()->isGestor())
<div class="modal-backdrop" id="modal-diente">
    <div class="modal" style="max-width:380px;">
        <div class="modal-header">
            <h3>🦷 Diente <span id="diente-num-label"></span></h3>
            <button class="modal-close" onclick="cerrarModal('modal-diente')">×</button>
        </div>
        <div class="form-group">
            <label class="form-label">Estado</label>
            <select id="diente-estado" class="form-control">
                @foreach(App\Models\Dentadura::ESTADOS as $key => $est)
                <option value="{{ $key }}">{{ $est['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Notas</label>
            <textarea id="diente-notas" class="form-control" rows="3" placeholder="Observaciones sobre este diente…"></textarea>
        </div>
        <button class="btn btn-primary" style="width:100%;" onclick="guardarDiente()">Guardar</button>
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

@push('scripts')
<script>
let dienteActual = null;

function abrirModalDiente(num, estado, notas) {
    dienteActual = num;
    document.getElementById('diente-num-label').textContent = num;
    document.getElementById('diente-estado').value = estado;
    document.getElementById('diente-notas').value = notas || '';
    document.getElementById('modal-diente').classList.add('open');
}

function abrirModalCita() {
    document.getElementById('modal-cita').classList.add('open');
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('open');
}

function guardarDiente() {
    const estado = document.getElementById('diente-estado').value;
    const notas  = document.getElementById('diente-notas').value;

    fetch('{{ route('clientes.dentadura', $cliente) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ dientes: [{ num_diente: String(dienteActual), estado, notas }] }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) { cerrarModal('modal-diente'); location.reload(); }
    })
    .catch(() => alert('Error al guardar. Inténtalo de nuevo.'));
}

// Cerrar modales al hacer clic fuera
document.querySelectorAll('.modal-backdrop').forEach(b => {
    b.addEventListener('click', e => { if (e.target === b) b.classList.remove('open'); });
});
</script>
@endpush
@endsection
