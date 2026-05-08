# Componentes perfil cliente — Plan de implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convertir las 5 secciones de la vista de perfil de cliente en componentes Blade anónimos organizados en `components/cliente/` y `components/cliente/odontograma/oclusal|anatomico/`.

**Architecture:** Reorganización pura de vistas — sin cambios de lógica PHP, CSS ni JS. Cada partial/componente existente se mueve a su nueva ubicación dentro de `components/`, se añade `@props`, y se actualiza `show.blade.php` para usar la sintaxis `<x-*>`. Los archivos obsoletos se eliminan al final.

**Tech Stack:** Laravel 11, Blade anonymous components, PHP 8.3.

---

## Mapa de archivos

| Origen | Destino | Cambios |
|--------|---------|---------|
| `clientes/partials/_datos-personales.blade.php` | `components/cliente/datos-personales.blade.php` | Añadir `@props` |
| `clientes/partials/_historial.blade.php` | `components/cliente/historial.blade.php` | Añadir `@props` |
| `clientes/partials/_citas.blade.php` | `components/cliente/citas.blade.php` | Añadir `@props` |
| `clientes/partials/_modales.blade.php` | `components/cliente/modales.blade.php` | Añadir `@props` |
| `components/_diente-inner.blade.php` | `components/cliente/odontograma/oclusal/diente.blade.php` | Añadir `@props`, cambiar comentario de cabecera |
| Primera mitad de `_odontograma.blade.php` + `@push scripts` | `components/cliente/odontograma/oclusal/index.blade.php` | Añadir `@props`, reemplazar `@include('components._diente-inner')` por `<x-cliente.odontograma.oclusal.diente>` |
| `components/diente-cara.blade.php` | `components/cliente/odontograma/anatomico/diente.blade.php` | Sin cambios de contenido |
| `components/odontograma-bucal.blade.php` + card wrapper de `_odontograma.blade.php` | `components/cliente/odontograma/anatomico/index.blade.php` | Añadir card wrapper, reemplazar `<x-diente-cara>` por `<x-cliente.odontograma.anatomico.diente>` |
| `clientes/show.blade.php` | `clientes/show.blade.php` | Reemplazar `@include` por `<x-cliente.*>` |

---

## Task 1: Crear carpetas necesarias

**Files:**
- Create dirs: `components/cliente/`, `components/cliente/odontograma/oclusal/`, `components/cliente/odontograma/anatomico/`

- [ ] **Crear estructura de directorios**

```powershell
New-Item -ItemType Directory -Force -Path "resources/views/components/cliente"
New-Item -ItemType Directory -Force -Path "resources/views/components/cliente/odontograma/oclusal"
New-Item -ItemType Directory -Force -Path "resources/views/components/cliente/odontograma/anatomico"
```

- [ ] **Verificar**

```powershell
Get-ChildItem -Recurse "resources/views/components/cliente" | Select-Object FullName
```

Expected: tres carpetas creadas.

---

## Task 2: `cliente/datos-personales.blade.php`

**Files:**
- Create: `resources/views/components/cliente/datos-personales.blade.php`

- [ ] **Crear el componente**

Contenido completo:

```blade
@props(['cliente'])

<div class="card">
    <div class="card-header">
        <h3 class="card-title">👤 Datos personales</h3>
        <span style="font-family:monospace;font-size:.8rem;background:var(--primary);color:#fff;
              padding:.2rem .6rem;border-radius:4px;">{{ $cliente->num_filiacion ?? '—' }}</span>
    </div>
    @foreach([
        ['Apellidos',  $cliente->apellidos],
        ['Nombre',     $cliente->nombre],
        ['Edad',       $cliente->edad ? $cliente->edad . ' años' : '—'],
        ['Profesión',  $cliente->profesion ?? '—'],
        ['Dirección',  $cliente->direccion ?? '—'],
        ['CP',         $cliente->cp ?? '—'],
        ['Teléfono',   $cliente->telefono ?? '—'],
    ] as [$label, $val])
    <div style="display:flex;justify-content:space-between;padding:.5rem 0;
                border-bottom:1px solid var(--gray-200);font-size:.88rem;">
        <span style="color:var(--text-light);font-weight:600;">{{ $label }}</span>
        <span style="text-align:right;max-width:60%;">{{ $val }}</span>
    </div>
    @endforeach
    @if($cliente->observaciones)
    <div style="margin-top:1rem;">
        <p style="font-size:.8rem;font-weight:600;color:var(--text-light);margin-bottom:.3rem;">Observaciones</p>
        <p style="font-size:.88rem;background:var(--gray-50);padding:.75rem;border-radius:6px;">
            {{ $cliente->observaciones }}
        </p>
    </div>
    @endif
</div>
```

- [ ] **Commit**

```bash
git add resources/views/components/cliente/datos-personales.blade.php
git commit -m "refactor: crear componente cliente.datos-personales"
```

---

## Task 3: `cliente/historial.blade.php`

**Files:**
- Create: `resources/views/components/cliente/historial.blade.php`

- [ ] **Crear el componente**

```blade
@props(['cliente', 'historial'])

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
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-bottom:1px solid var(--gray-200);">
        @foreach([['Debe total','€ ' . number_format($totalDebe,2),'var(--danger)'],['Haber total','€ ' . number_format($totalHaber,2),'var(--success)'],['Saldo','€ ' . number_format($saldo,2),$saldo > 0 ? 'var(--danger)' : 'var(--success)']] as [$l,$v,$c])
        <div style="padding:1rem;text-align:center;border-right:1px solid var(--gray-200);">
            <div style="font-size:.75rem;color:var(--text-light);font-weight:600;">{{ $l }}</div>
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
                    <td style="text-align:right;font-size:.85rem;color:var(--danger);">{{ number_format($reg->debe,2) }}€</td>
                    <td style="text-align:right;font-size:.85rem;color:var(--success);">{{ number_format($reg->haber,2) }}€</td>
                    <td style="text-align:right;font-size:.85rem;font-weight:600;color:{{ $reg->saldo > 0 ? 'var(--danger)' : 'var(--success)' }};">
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
                <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--text-light);">Sin registros de historial.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($historial->hasPages())
    <div style="padding:1rem 1.5rem;">{{ $historial->links() }}</div>
    @endif
</div>
```

- [ ] **Commit**

```bash
git add resources/views/components/cliente/historial.blade.php
git commit -m "refactor: crear componente cliente.historial"
```

---

## Task 4: `cliente/citas.blade.php`

**Files:**
- Create: `resources/views/components/cliente/citas.blade.php`

- [ ] **Crear el componente**

```blade
@props(['cliente', 'citas'])

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
```

- [ ] **Commit**

```bash
git add resources/views/components/cliente/citas.blade.php
git commit -m "refactor: crear componente cliente.citas"
```

---

## Task 5: `cliente/modales.blade.php`

**Files:**
- Create: `resources/views/components/cliente/modales.blade.php`

- [ ] **Crear el componente**

```blade
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
```

- [ ] **Commit**

```bash
git add resources/views/components/cliente/modales.blade.php
git commit -m "refactor: crear componente cliente.modales"
```

---

## Task 6: `odontograma/oclusal/diente.blade.php`

**Files:**
- Create: `resources/views/components/cliente/odontograma/oclusal/diente.blade.php`

Origen: `components/_diente-inner.blade.php`. Único cambio: el comentario de cabecera se sustituye por `@props`.

- [ ] **Crear el componente**

```blade
@props(['t', 'num'])

@php
    $shapes = [
        'molar'    => 'M 20,4 C 25,3 32,4 35,7 C 38,10 37,17 36,20 C 37,23 38,30 35,33 C 32,36 25,37 20,36 C 15,37 8,36 5,33 C 2,30 3,23 4,20 C 3,17 2,10 5,7 C 8,4 15,3 20,4 Z',
        'premolar' => 'M 20,5 C 27,5 34,11 34,20 C 34,29 27,35 20,35 C 13,35 6,29 6,20 C 6,11 13,5 20,5 Z',
        'canino'   => 'M 20,4 C 25,4 36,13 36,20 C 36,27 25,36 20,36 C 15,36 4,27 4,20 C 4,13 15,4 20,4 Z',
        'incisivo' => 'M 13,4 L 27,4 C 31,4 34,7 34,11 L 34,29 C 34,33 31,36 27,36 L 13,36 C 9,36 6,33 6,29 L 6,11 C 6,7 9,4 13,4 Z',
    ];
    $shape = $shapes[$t['tipo']] ?? $shapes['molar'];

    $grooveMolar = $t['esPresente'] && $t['tipo'] === 'molar';
    $groovePre   = $t['esPresente'] && $t['tipo'] === 'premolar';
    $grooveCan   = $t['esPresente'] && $t['tipo'] === 'canino';

    $esGestor  = auth()->user()->isGestor();
    $tieneOcl  = $t['tieneOcl'];

    if ($tieneOcl) {
        $polyV   = '0,0 40,0 30,10 10,10';
        $polyIzq = '0,0 10,10 10,30 0,40';
        $polyDer = '30,10 40,0 40,40 30,30';
        $polyL   = '10,30 30,30 40,40 0,40';
    } else {
        $polyV   = '0,0 40,0 20,20';
        $polyIzq = '0,0 0,40 20,20';
        $polyDer = '40,0 40,40 20,20';
        $polyL   = '0,40 40,40 20,20';
    }
@endphp

<svg class="diente-svg" viewBox="0 0 40 40" width="46" height="46">

    <defs>
        <clipPath id="tc-{{ $num }}">
            <path d="{{ $shape }}"/>
        </clipPath>
    </defs>

    <g clip-path="url(#tc-{{ $num }})">
        @if($esGestor)
            <polygon class="cara-svg" points="{{ $polyV }}"   fill="{{ $t['cV'] }}"
                     onclick="abrirModalCaraDiente({{ $num }},'vestibular')"/>
            <polygon class="cara-svg" points="{{ $polyIzq }}" fill="{{ $t['cIzq'] }}"
                     onclick="abrirModalCaraDiente({{ $num }},'{{ $t['caraIzq'] }}')"/>
            <polygon class="cara-svg" points="{{ $polyDer }}" fill="{{ $t['cDer'] }}"
                     onclick="abrirModalCaraDiente({{ $num }},'{{ $t['caraDer'] }}')"/>
            <polygon class="cara-svg" points="{{ $polyL }}"   fill="{{ $t['cL'] }}"
                     onclick="abrirModalCaraDiente({{ $num }},'lingual')"/>
            @if($tieneOcl)
                <rect class="cara-svg" x="10" y="10" width="20" height="20" fill="{{ $t['cO'] }}"
                      onclick="abrirModalCaraDiente({{ $num }},'oclusal')"/>
            @endif
        @else
            <polygon points="{{ $polyV }}"   fill="{{ $t['cV'] }}"   stroke="#94a3b8" stroke-width=".6"/>
            <polygon points="{{ $polyIzq }}" fill="{{ $t['cIzq'] }}" stroke="#94a3b8" stroke-width=".6"/>
            <polygon points="{{ $polyDer }}" fill="{{ $t['cDer'] }}" stroke="#94a3b8" stroke-width=".6"/>
            <polygon points="{{ $polyL }}"   fill="{{ $t['cL'] }}"   stroke="#94a3b8" stroke-width=".6"/>
            @if($tieneOcl)
                <rect x="10" y="10" width="20" height="20" fill="{{ $t['cO'] }}" stroke="#94a3b8" stroke-width=".6"/>
            @endif
        @endif
    </g>

    {{-- Surcos decorativos --}}
    @if($grooveMolar)
        <line x1="20" y1="9"  x2="20" y2="31" stroke="#64748b" stroke-width=".8" pointer-events="none"/>
        <line x1="9"  y1="20" x2="31" y2="20" stroke="#64748b" stroke-width=".8" pointer-events="none"/>
        <circle cx="20" cy="20" r="1.2" fill="#64748b" fill-opacity=".5" pointer-events="none"/>
    @elseif($groovePre)
        <line x1="20" y1="9" x2="20" y2="31" stroke="#64748b" stroke-width=".8" pointer-events="none"/>
        <circle cx="14" cy="20" r=".9" fill="#64748b" fill-opacity=".45" pointer-events="none"/>
        <circle cx="26" cy="20" r=".9" fill="#64748b" fill-opacity=".45" pointer-events="none"/>
    @elseif($grooveCan)
        <line x1="20" y1="7" x2="20" y2="33" stroke="#64748b" stroke-width=".7" stroke-dasharray="1,2" pointer-events="none"/>
    @endif

    <path d="{{ $shape }}" fill="none" stroke="#334155" stroke-width="1.4" class="borde-diente"/>

</svg>
```

- [ ] **Commit**

```bash
git add resources/views/components/cliente/odontograma/oclusal/diente.blade.php
git commit -m "refactor: crear componente cliente.odontograma.oclusal.diente"
```

---

## Task 7: `odontograma/oclusal/index.blade.php`

**Files:**
- Create: `resources/views/components/cliente/odontograma/oclusal/index.blade.php`

Origen: `clientes/partials/_odontograma.blade.php` completo (incluye `@push styles` y `@push scripts`).
Cambios respecto al original:
1. Reemplaza el `@push('styles')` + PHP + card del original (sin la segunda mitad del bucal).
2. `@include('components._diente-inner', ['t' => $t, 'num' => $num])` → `<x-cliente.odontograma.oclusal.diente :t="$t" :num="$num" />` (aparece dos veces).
3. Se añade `@props(['cliente', 'dentadura'])` al inicio.
4. El bloque `@push('scripts')` se incluye al final del componente.

- [ ] **Crear el componente**

```blade
@props(['cliente', 'dentadura'])

@push('styles')
<style>
/* ── ODONTOGRAMA MULTI-CARA ────────────────────────────────────────────── */
.odontograma { background:#fff; border-radius:12px; padding:1.25rem 1.5rem; border:1px solid var(--gray-200); }
.dientes-row { display:flex; justify-content:center; gap:3px; margin:6px 0; flex-wrap:nowrap; }
.diente-wrap {
    display:flex; flex-direction:column; align-items:center; gap:1px;
    cursor:default; transition:transform .12s; position:relative;
}
.diente-wrap:hover { transform:scale(1.18); z-index:10; }
.diente-wrap.ausente { opacity:.35; }
.diente-num { font-size:.65rem; color:#475569; font-weight:700; line-height:1.3; user-select:none; }
.diente-svg { display:block; overflow:visible; border-radius:3px; }
/* Caras individuales clicables */
.diente-svg .cara-svg {
    stroke:#94a3b8; stroke-width:.7; cursor:pointer;
    transition:filter .1s, stroke .1s;
}
.diente-svg .cara-svg:hover {
    filter:brightness(.72) saturate(1.3);
    stroke:#1e3a8a; stroke-width:1.2;
}
/* Cara no-oclusal (incisivos/caninos): decorativa, no clicable */
.diente-svg .cara-nooclusal { stroke:#cbd5e1; stroke-width:.5; stroke-dasharray:2,2; pointer-events:none; }
/* Cara resaltada al abrir modal */
.cara-field.cara-highlight > select {
    outline:2px solid #2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.2);
}
.diente-pieza-icon {
    position:absolute; top:50%; left:50%; transform:translate(-50%,-52%);
    font-size:.75rem; font-weight:800; color:#fff; text-shadow:0 1px 3px rgba(0,0,0,.65);
    pointer-events:none; line-height:1;
}
.diente-separador { width:12px; flex-shrink:0; }
/* Leyendas */
.leyenda-grupo { margin-bottom:.4rem; }
.leyenda-titulo { font-size:.66rem; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:.5px; margin-bottom:.2rem; }
.leyenda-items { display:flex; gap:.5rem; flex-wrap:wrap; }
.leyenda-item { display:flex; align-items:center; gap:.3rem; font-size:.72rem; color:#475569; }
.leyenda-color { width:12px; height:12px; border-radius:2px; flex-shrink:0; border:1px solid #cbd5e1; }
/* Modal diente multi-cara */
.pieza-estado-section { border-bottom:1px solid var(--gray-200); padding-bottom:.75rem; margin-bottom:.75rem; }
.cara-grid { display:grid; grid-template-columns:1fr 1fr; gap:.6rem; margin-top:.5rem; }
.cara-field label { font-size:.78rem; font-weight:600; color:var(--text-light); display:block; margin-bottom:.15rem; }
.cara-field select { width:100%; }
.cara-field.full-width { grid-column:1/-1; }
.caras-disabled { opacity:.35; pointer-events:none; }
#modal-diente select.form-control { width:100%; }
</style>
@endpush

@php
    $sanoColor = '#e8ecf1';
    $toothData = function($num) use ($dentadura, $sanoColor) {
        $d = $dentadura["$num"] ?? null;
        $pieza = $d?->estado_pieza ?? 'presente';
        $esPresente = ($pieza === 'presente');
        $tieneOcl = in_array($num, \App\Models\Dentadura::DIENTES_CON_OCLUSAL);
        $cuadrante = (int) floor($num / 10);
        $mesialDer = in_array($cuadrante, [1, 4]);

        $tipo = match(true) {
            in_array($num, [11,12,21,22,31,32,41,42]) => 'incisivo',
            in_array($num, [13,23,33,43])             => 'canino',
            in_array($num, [14,15,24,25,34,35,44,45]) => 'premolar',
            default                                   => 'molar',
        };

        if ($esPresente) {
            $gc = function($cara) use ($d, $sanoColor) {
                $val = $d ? $d->{"cara_{$cara}"} : null;
                if (!$val) return $sanoColor;
                return \App\Models\Dentadura::ESTADOS_CARA[$val]['color'] ?? $sanoColor;
            };
            $cV = $gc('vestibular'); $cL = $gc('lingual');
            $cM = $gc('mesial');     $cD = $gc('distal');
            $cO = $tieneOcl ? $gc('oclusal') : '#f1f5f9';
        } else {
            $pColor = \App\Models\Dentadura::ESTADOS_PIEZA[$pieza]['color'] ?? '#6b7280';
            $cV = $cL = $cM = $cD = $cO = $pColor;
        }

        return [
            'pieza' => $pieza, 'esPresente' => $esPresente, 'tieneOcl' => $tieneOcl,
            'icono' => !$esPresente ? (\App\Models\Dentadura::ESTADOS_PIEZA[$pieza]['icono'] ?? '') : '',
            'titulo' => "Diente {$num}" . ($d ? ' — ' . $d->resumen() : ''),
            'cV'     => $cV, 'cL' => $cL, 'cO' => $cO,
            'cIzq'   => $mesialDer ? $cD : $cM,
            'cDer'   => $mesialDer ? $cM : $cD,
            'caraIzq' => $mesialDer ? 'distal' : 'mesial',
            'caraDer' => $mesialDer ? 'mesial' : 'distal',
            'tipo'    => $tipo,
        ];
    };
@endphp

<div class="card odontograma">
    <div class="card-header" style="flex-direction:column;align-items:flex-start;">
        <h3 class="card-title" style="margin-bottom:.75rem;">🦷 Odontograma</h3>
        <div style="display:flex;gap:2rem;flex-wrap:wrap;">
            <div class="leyenda-grupo">
                <div class="leyenda-titulo">Estado de pieza</div>
                <div class="leyenda-items">
                    @foreach(App\Models\Dentadura::ESTADOS_PIEZA as $key => $est)
                    <div class="leyenda-item">
                        <div class="leyenda-color" style="background:{{ $est['color'] }};"></div>
                        <span>{{ $est['label'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="leyenda-grupo">
                <div class="leyenda-titulo">Estado por cara</div>
                <div class="leyenda-items">
                    <div class="leyenda-item">
                        <div class="leyenda-color" style="background:{{ $sanoColor }};"></div>
                        <span>Sano</span>
                    </div>
                    @foreach(App\Models\Dentadura::ESTADOS_CARA as $key => $est)
                        @if($key !== 'sano')
                        <div class="leyenda-item">
                            <div class="leyenda-color" style="background:{{ $est['color'] }};"></div>
                            <span>{{ $est['label'] }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Arcada superior --}}
    <p style="text-align:center;font-size:.72rem;color:var(--text-light);margin:.75rem 0 2px;">◀ Arcada superior ▶</p>
    <div class="dientes-row">
        @foreach(App\Models\Dentadura::DIENTES_SUPERIORES as $i => $num)
            @if($i === 8)<div class="diente-separador"></div>@endif
            @php $t = $toothData($num); @endphp
            <div class="diente-wrap {{ $t['pieza'] === 'ausente' ? 'ausente' : '' }}"
                 title="{{ $t['titulo'] }}">
                <span class="diente-num">{{ $num }}</span>
                <x-cliente.odontograma.oclusal.diente :t="$t" :num="$num" />
                @if($t['icono'])<span class="diente-pieza-icon">{{ $t['icono'] }}</span>@endif
            </div>
        @endforeach
    </div>

    <div style="border-top:2px dashed var(--gray-200);margin:6px auto;width:90%;"></div>

    {{-- Arcada inferior --}}
    <div class="dientes-row">
        @foreach(App\Models\Dentadura::DIENTES_INFERIORES as $i => $num)
            @if($i === 8)<div class="diente-separador"></div>@endif
            @php $t = $toothData($num); @endphp
            <div class="diente-wrap {{ $t['pieza'] === 'ausente' ? 'ausente' : '' }}"
                 title="{{ $t['titulo'] }}">
                <x-cliente.odontograma.oclusal.diente :t="$t" :num="$num" />
                @if($t['icono'])<span class="diente-pieza-icon">{{ $t['icono'] }}</span>@endif
                <span class="diente-num">{{ $num }}</span>
            </div>
        @endforeach
    </div>
    <p style="text-align:center;font-size:.72rem;color:var(--text-light);margin-top:2px;">◀ Arcada inferior ▶</p>

    @if(auth()->user()->isGestor())
    <p style="text-align:center;font-size:.78rem;color:var(--text-light);margin-top:.75rem;">
        Haz clic en cualquier diente para editar su estado.
    </p>
    @endif
</div>

@push('scripts')
@php
    $dentaduraJson = json_encode($dentadura->mapWithKeys(function ($d, $key) {
        return [$key => [
            'estado_pieza'    => $d->estado_pieza ?? 'presente',
            'cara_vestibular' => $d->cara_vestibular ?? 'sano',
            'cara_lingual'    => $d->cara_lingual ?? 'sano',
            'cara_mesial'     => $d->cara_mesial ?? 'sano',
            'cara_distal'     => $d->cara_distal ?? 'sano',
            'cara_oclusal'    => $d->cara_oclusal ?? 'sano',
            'notas'           => $d->notas ?? '',
            'tiene_oclusal'   => in_array((int) $d->num_diente, \App\Models\Dentadura::DIENTES_CON_OCLUSAL),
        ]];
    }));
@endphp
<script>
window.dentaduraUrl  = '{{ route('clientes.dentadura', $cliente) }}';
window.dentaduraData = {!! $dentaduraJson !!};
</script>
<script src="{{ asset('js/odontograma.js') }}"></script>
@endpush
```

- [ ] **Commit**

```bash
git add resources/views/components/cliente/odontograma/oclusal/index.blade.php
git commit -m "refactor: crear componente cliente.odontograma.oclusal"
```

---

## Task 8: `odontograma/anatomico/diente.blade.php`

**Files:**
- Create: `resources/views/components/cliente/odontograma/anatomico/diente.blade.php`

Origen: `components/diente-cara.blade.php` — contenido idéntico, solo cambia la ubicación.

- [ ] **Crear el componente** (copiar contenido exacto de `diente-cara.blade.php`)

```blade
{{--
    Diente anatómico — vista bucal (frontal).
    Raíces arriba + corona dividida: lingual (interior) arriba, vestibular (exterior) abajo.
    La arcada inferior aplica scaleY(-1) en CSS para invertir la orientación.
    Props:
      $num       — FDI número (ej: 16)
      $dentadura — colección keyed [num => Dentadura]
      $arcada    — 'superior' | 'inferior'
--}}
@props(['num' => null, 'dentadura' => null, 'arcada' => 'inferior'])

@php
use App\Models\Dentadura;

$num  = (int)$num;
$tipo = match(true) {
    in_array($num, [11,12,21,22,31,32,41,42]) => 'incisivo',
    in_array($num, [13,23,33,43])             => 'canino',
    in_array($num, [14,15,24,25,34,35,44,45]) => 'premolar',
    default                                   => 'molar',
};

$d           = $dentadura[(string)$num] ?? null;
$estadoPieza = $d?->estado_pieza ?? 'presente';
$esPresente  = ($estadoPieza === 'presente' || $estadoPieza === null);
$ausente     = ($estadoPieza === 'ausente');

$ivory    = '#f5eedd';
$raizFill = '#e2d4b6';

if ($esPresente) {
    $lingEst  = $d?->cara_lingual    ?? null;
    $vestEst  = $d?->cara_vestibular ?? null;
    $fillLing = isset(Dentadura::ESTADOS_CARA[$lingEst])
        ? Dentadura::ESTADOS_CARA[$lingEst]['color'] : $ivory;
    $fillVest = isset(Dentadura::ESTADOS_CARA[$vestEst])
        ? Dentadura::ESTADOS_CARA[$vestEst]['color'] : $ivory;
    $fillRaiz = $raizFill;
} else {
    $meta     = Dentadura::ESTADOS_PIEZA[$estadoPieza] ?? [];
    $c        = $meta['color'] ?? '#6b7280';
    $fillLing = $fillVest = $c;
    $fillRaiz = $ausente ? 'none' : $c;
}

$sh = [
    'incisivo' => [
        'raices' => [
            'M 17,2 C 15,2 13,7 13,20 L 13,36 L 21,36 L 21,20 C 21,7 19,2 17,2 Z',
        ],
        'corona' => 'M 11,36 L 23,36 C 24,36 25,39 25,48 L 25,63 C 25,72 23,80 21,86 '
                  . 'C 19,90 18,92 17,93 C 16,92 15,90 13,86 C 11,80 9,72 9,63 '
                  . 'L 9,48 C 9,39 10,36 11,36 Z',
        'split'  => 63,
        'body'   => null,
        'surcos' => [],
    ],
    'canino' => [
        'raices' => [
            'M 17,1 C 15,1 11,7 11,24 L 11,36 L 23,36 L 23,24 C 23,7 19,1 17,1 Z',
        ],
        'corona' => 'M 8,36 C 7,36 6,40 6,55 L 6,68 C 6,78 8,86 12,91 '
                  . 'C 14,94 16,96 17,97 C 18,96 20,94 22,91 '
                  . 'C 26,86 28,78 28,68 L 28,55 C 28,40 27,36 26,36 Z',
        'split'  => 63,
        'body'   => null,
        'surcos' => [
            ['x1'=>17,'y1'=>50,'x2'=>17,'y2'=>90,'sw'=>0.6,'dash'=>true],
        ],
    ],
    'premolar' => [
        'raices' => [
            'M 11,1 C 9,1 7,6 7,22 L 7,36 L 16,36 L 16,22 C 16,6 13,1 11,1 Z',
            'M 23,1 C 21,1 19,6 19,22 L 19,36 L 27,36 L 27,22 C 27,6 25,1 23,1 Z',
        ],
        'corona' => 'M 5,36 L 29,36 C 30,36 31,40 31,50 L 31,64 C 31,74 29,83 27,88 '
                  . 'C 24,92 21,94 17,95 C 13,94 10,92 7,88 '
                  . 'C 5,83 3,74 3,64 L 3,50 C 3,40 4,36 5,36 Z',
        'split'  => 63,
        'body'   => null,
        'surcos' => [
            ['x1'=>17,'y1'=>48,'x2'=>17,'y2'=>88,'sw'=>0.65,'dash'=>false],
        ],
    ],
    'molar' => [
        'raices' => [
            'M 9,0 C 7,0 4,6 4,22 L 4,36 L 15,36 L 15,22 C 15,6 12,0 9,0 Z',
            'M 25,0 C 23,0 20,6 20,22 L 20,36 L 30,36 L 30,22 C 30,6 27,0 25,0 Z',
        ],
        'corona' => 'M 2,36 L 32,36 C 33,36 33,42 33,50 L 33,78 C 33,85 32,89 30,90 '
                  . 'L 4,90 C 2,89 1,85 1,78 L 1,50 C 1,42 1,36 2,36 Z',
        'split'  => 60,
        'body'   => ['y1' => 60, 'y2' => 72],
        'surcos' => [
            ['x1'=>17,'y1'=>48,'x2'=>17,'y2'=>82,'sw'=>0.7,'dash'=>false],
            ['x1'=>6, 'y1'=>66,'x2'=>28,'y2'=>66,'sw'=>0.5,'dash'=>false],
        ],
    ],
];

$s            = $sh[$tipo];
$strokeCorona = '#374151';
$strokeRaiz   = '#a08060';
$isInf        = ($arcada === 'inferior');
@endphp

<svg class="odon-svg odon-diente-bucal{{ $isInf ? ' odon-diente-inf' : '' }}"
     viewBox="0 0 34 100" width="34" height="100"
     xmlns="http://www.w3.org/2000/svg">

    <defs>
        <clipPath id="clip-b{{ $num }}">
            <path d="{{ $s['corona'] }}"/>
        </clipPath>
    </defs>

    {{-- Raíces (siempre en la parte superior del SVG) --}}
    @if(!$ausente)
        @foreach($s['raices'] as $rp)
        <path d="{{ $rp }}"
              fill="{{ $fillRaiz }}"
              stroke="{{ $strokeRaiz }}"
              stroke-width="0.9"
              stroke-linejoin="round"/>
        @endforeach
    @endif

    {{-- Corona --}}
    @if($ausente)
        <path d="{{ $s['corona'] }}" fill="none"
              stroke="#94a3b8" stroke-width="0.8" stroke-dasharray="2,2"/>
        <line x1="11" y1="44" x2="23" y2="82" stroke="#94a3b8" stroke-width="1.1"/>
        <line x1="23" y1="44" x2="11" y2="82" stroke="#94a3b8" stroke-width="1.1"/>
    @else
        {{-- Sección lingual (interior — arriba) --}}
        <rect x="0" y="36" width="34" height="{{ $s['split'] - 36 }}"
              clip-path="url(#clip-b{{ $num }})"
              fill="{{ $fillLing }}"/>

        @if($s['body'])
            <rect x="0" y="{{ $s['body']['y1'] }}" width="34"
                  height="{{ $s['body']['y2'] - $s['body']['y1'] }}"
                  clip-path="url(#clip-b{{ $num }})"
                  fill="{{ $ivory }}"/>

            <rect x="0" y="{{ $s['body']['y2'] }}" width="34" height="30"
                  clip-path="url(#clip-b{{ $num }})"
                  fill="{{ $fillVest }}"/>
        @else
            <rect x="0" y="{{ $s['split'] }}" width="34" height="37"
                  clip-path="url(#clip-b{{ $num }})"
                  fill="{{ $fillVest }}"/>
        @endif

        {{-- Surcos decorativos --}}
        @foreach($s['surcos'] as $sg)
        <line x1="{{ $sg['x1'] }}" y1="{{ $sg['y1'] }}"
              x2="{{ $sg['x2'] }}" y2="{{ $sg['y2'] }}"
              stroke="{{ $strokeCorona }}" stroke-width="{{ $sg['sw'] }}"
              stroke-opacity="0.3"
              pointer-events="none"
              @if($sg['dash']) stroke-dasharray="1.5,2.5" @endif />
        @endforeach

        {{-- Contorno de la corona --}}
        <path d="{{ $s['corona'] }}"
              fill="none"
              stroke="{{ $strokeCorona }}"
              stroke-width="1.1"
              stroke-linejoin="round"/>

        {{-- Línea divisoria lingual / cuerpo o lingual / vestibular --}}
        <line x1="0" y1="{{ $s['split'] }}" x2="34" y2="{{ $s['split'] }}"
              clip-path="url(#clip-b{{ $num }})"
              stroke="{{ $strokeCorona }}"
              stroke-width="0.5"
              stroke-opacity="0.35"
              pointer-events="none"/>

        @if($s['body'])
        <line x1="0" y1="{{ $s['body']['y2'] }}" x2="34" y2="{{ $s['body']['y2'] }}"
              clip-path="url(#clip-b{{ $num }})"
              stroke="{{ $strokeCorona }}"
              stroke-width="0.5"
              stroke-opacity="0.35"
              pointer-events="none"/>
        @endif
    @endif

</svg>
```

- [ ] **Commit**

```bash
git add resources/views/components/cliente/odontograma/anatomico/diente.blade.php
git commit -m "refactor: crear componente cliente.odontograma.anatomico.diente"
```

---

## Task 9: `odontograma/anatomico/index.blade.php`

**Files:**
- Create: `resources/views/components/cliente/odontograma/anatomico/index.blade.php`

Origen: `components/odontograma-bucal.blade.php` + card wrapper de `_odontograma.blade.php`.
Cambios:
1. El contenido se envuelve en el card (`<div class="card odontograma" id="odontograma-bucal-card">`) con su header.
2. `<x-diente-cara>` → `<x-cliente.odontograma.anatomico.diente>`.

- [ ] **Crear el componente**

```blade
@props(['cliente', 'dentadura', 'modoEdicion' => true])

@php
use App\Models\Dentadura;

$tdato = function(int $num) use ($dentadura) {
    $d     = $dentadura[(string)$num] ?? null;
    $pieza = $d?->estado_pieza ?? 'presente';
    $esP   = ($pieza === 'presente' || $pieza === null);

    if ($esP) {
        $ausente = false;
        $tooltip = 'Diente '.$num . ($d ? ' — '.$d->resumen() : '');
    } else {
        $meta    = Dentadura::ESTADOS_PIEZA[$pieza] ?? [];
        $ausente = $pieza === 'ausente';
        $tooltip = 'Diente '.$num.' — '.($meta['label'] ?? $pieza);
    }

    return compact('ausente','tooltip');
};

$superiores = Dentadura::DIENTES_SUPERIORES;
$inferiores = Dentadura::DIENTES_INFERIORES;
@endphp

<div class="card odontograma" id="odontograma-bucal-card">
    <div class="card-header" style="flex-direction:column;align-items:flex-start;">
        <h3 class="card-title" style="margin-bottom:.25rem;">🦷 Odontograma anatómico</h3>
        <p style="font-size:.75rem;color:var(--text-light);margin:0;">
            Vista bucal (frontal) — corona coloreada según estado de la cara vestibular.
        </p>
    </div>

    <div class="odon-bucal-wrap">

        {{-- Leyenda --}}
        <div class="odon-bucal-leyenda">
            <div class="leyenda-grupo">
                <div class="leyenda-titulo">Estado de pieza</div>
                <div class="leyenda-items">
                    @foreach(Dentadura::ESTADOS_PIEZA as $key => $est)
                    <div class="leyenda-item">
                        <div class="leyenda-color" style="background:{{ $est['color'] }};"></div>
                        <span>{{ $est['icono'] }} {{ $est['label'] }}</span>
                    </div>
                    @endforeach
                    <div class="leyenda-item">
                        <div class="leyenda-color" style="background:#f5eedd;border:1px solid #c8b89a;"></div>
                        <span>Sano</span>
                    </div>
                </div>
            </div>
            <div class="leyenda-grupo">
                <div class="leyenda-titulo">Cara vestibular / lingual</div>
                <div class="leyenda-items">
                    @foreach(Dentadura::ESTADOS_CARA as $key => $est)
                        @if($key !== 'sano')
                        <div class="leyenda-item">
                            <div class="leyenda-color" style="background:{{ $est['color'] }};"></div>
                            <span>{{ $est['label'] }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Etiqueta arcada superior --}}
        <p class="odon-bucal-label">◀ Arcada superior ▶</p>

        <div class="odon-bucal-scroll">
          <div class="odon-bucal-inner">

            {{-- Arcada superior --}}
            <div class="odon-bucal-row odon-row-superior">
                @foreach($superiores as $i => $num)
                    @if($i === 8)<div class="odon-bucal-sep"></div>@endif
                    @php $td = $tdato($num); @endphp
                    <div class="odon-bucal-diente{{ $td['ausente'] ? ' odon-ausente' : '' }}"
                         title="{{ $td['tooltip'] }}"
                         @if($modoEdicion && auth()->user()->isGestor())
                             onclick="abrirModalDiente({{ $num }})"
                         @endif>
                        <span class="odon-num">{{ $num }}</span>
                        <x-cliente.odontograma.anatomico.diente :num="$num" :dentadura="$dentadura" arcada="superior" />
                    </div>
                @endforeach
            </div>

            {{-- Línea oclusal central --}}
            <div class="odon-bucal-divider"></div>

            {{-- Arcada inferior --}}
            <div class="odon-bucal-row odon-row-inferior">
                @foreach($inferiores as $i => $num)
                    @if($i === 8)<div class="odon-bucal-sep"></div>@endif
                    @php $td = $tdato($num); @endphp
                    <div class="odon-bucal-diente{{ $td['ausente'] ? ' odon-ausente' : '' }}"
                         title="{{ $td['tooltip'] }}"
                         @if($modoEdicion && auth()->user()->isGestor())
                             onclick="abrirModalDiente({{ $num }})"
                         @endif>
                        <x-cliente.odontograma.anatomico.diente :num="$num" :dentadura="$dentadura" arcada="inferior" />
                        <span class="odon-num">{{ $num }}</span>
                    </div>
                @endforeach
            </div>

          </div>{{-- /.odon-bucal-inner --}}
        </div>{{-- /.odon-bucal-scroll --}}

        <p class="odon-bucal-label">◀ Arcada inferior ▶</p>

        @if($modoEdicion && auth()->user()->isGestor())
        <p class="odon-bucal-hint">Haz clic en cualquier diente para editar su estado.</p>
        @endif
    </div>
</div>

@push('styles')
<style>
/* ── Odontograma bucal ─────────────────────────────────────────────────────── */
.odon-bucal-wrap {
    background: #fff;
}

.odon-bucal-leyenda {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
    margin-bottom: .75rem;
    padding-bottom: .75rem;
    border-bottom: 1px solid var(--gray-200);
}

.odon-bucal-label {
    text-align: center;
    font-size: .72rem;
    color: var(--text-light);
    margin: .4rem 0 2px;
}

.odon-bucal-hint {
    text-align: center;
    font-size: .78rem;
    color: var(--text-light);
    margin-top: .5rem;
}

.odon-bucal-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: .25rem;
}

.odon-bucal-inner {
    min-width: 610px;
    padding: 0 4px;
}

.odon-bucal-row {
    display: flex;
    justify-content: center;
    gap: 2px;
    flex-wrap: nowrap;
}

.odon-row-superior {
    align-items: flex-end;
}

.odon-row-inferior {
    align-items: flex-start;
}

.odon-bucal-sep {
    width: 10px;
    flex-shrink: 0;
    align-self: stretch;
}

.odon-bucal-divider {
    border-top: 2px dashed var(--gray-200);
    margin: 0 auto;
    width: 94%;
}

.odon-bucal-diente {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1px;
    cursor: pointer;
    transition: transform .12s;
    position: relative;
    padding: 0;
}

.odon-bucal-diente:hover {
    transform: scale(1.14);
    z-index: 10;
}

.odon-ausente {
    opacity: .38;
}

.odon-num {
    font-size: .58rem;
    font-weight: 700;
    color: #475569;
    line-height: 1.3;
    user-select: none;
    white-space: nowrap;
}

.odon-svg {
    display: block;
    overflow: visible;
    filter: drop-shadow(0 1px 2px rgba(0,0,0,.13));
    flex-shrink: 0;
}

.odon-diente-inf {
    transform: scaleY(-1);
    display: block;
}

@media (max-width: 640px) {
    .odon-svg.odon-diente-bucal {
        width: 26px;
        height: 77px;
    }
    .odon-bucal-sep {
        width: 6px;
    }
    .odon-num {
        font-size: .52rem;
    }
    .odon-bucal-leyenda {
        gap: 1rem;
    }
}

@media (max-width: 420px) {
    .odon-svg.odon-diente-bucal {
        width: 22px;
        height: 65px;
    }
}
</style>
@endpush
```

- [ ] **Commit**

```bash
git add resources/views/components/cliente/odontograma/anatomico/index.blade.php
git commit -m "refactor: crear componente cliente.odontograma.anatomico"
```

---

## Task 10: Actualizar `show.blade.php`

**Files:**
- Modify: `resources/views/clientes/show.blade.php`

- [ ] **Reemplazar contenido completo**

```blade
@extends('layouts.app')
@section('title', $cliente->nombre_completo . ' — Clínica Dental Mula')
@section('page-title', $cliente->nombre_completo)

@section('topbar-actions')
    @if(auth()->user()->isGestor())
        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary btn-sm">✏️ Editar datos</a>
        <a href="{{ route('citas.calendario') }}" class="btn btn-outline btn-sm">📅 Calendario</a>
    @endif
@endsection

@section('content')
<div class="cliente-grid">
    <div class="cliente-sidebar">
        <x-cliente.datos-personales :cliente="$cliente" />
    </div>
    <div class="cliente-main">
        <x-cliente.odontograma.oclusal :cliente="$cliente" :dentadura="$dentadura" />
        <x-cliente.odontograma.anatomico :cliente="$cliente" :dentadura="$dentadura" />
        <x-cliente.historial :cliente="$cliente" :historial="$historial" />
        <x-cliente.citas :cliente="$cliente" :citas="$citasFuturas" />
    </div>
</div>
<x-cliente.modales :cliente="$cliente" :dentadura="$dentadura" />
@endsection
```

- [ ] **Limpiar caché de vistas**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan view:clear
```

Expected: `Compiled views cleared successfully.`

- [ ] **Commit**

```bash
git add resources/views/clientes/show.blade.php
git commit -m "refactor: show.blade.php usa componentes x-cliente.*"
```

---

## Task 11: Eliminar archivos obsoletos

**Files:**
- Delete: `resources/views/clientes/partials/` (5 archivos)
- Delete: `resources/views/components/_diente-inner.blade.php`
- Delete: `resources/views/components/diente-cara.blade.php`
- Delete: `resources/views/components/odontograma-bucal.blade.php`

- [ ] **Eliminar partials**

```powershell
Remove-Item -Recurse -Force "resources/views/clientes/partials"
```

- [ ] **Eliminar componentes raíz obsoletos**

```powershell
Remove-Item "resources/views/components/_diente-inner.blade.php"
Remove-Item "resources/views/components/diente-cara.blade.php"
Remove-Item "resources/views/components/odontograma-bucal.blade.php"
```

- [ ] **Verificar que no quedan referencias a los archivos eliminados**

```powershell
Select-String -Recurse -Path "resources/views" -Pattern "_diente-inner|diente-cara|odontograma-bucal|clientes\.partials\." | Select-Object Filename, LineNumber, Line
```

Expected: sin resultados.

- [ ] **Commit**

```bash
git add -A
git commit -m "refactor: eliminar partials y componentes raiz obsoletos"
```

---

## Task 12: Verificar en navegador

- [ ] **Arrancar Apache y MySQL si no están activos**

```powershell
Start-Process "C:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18\bin\httpd.exe"
Start-Process "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe" `
    -ArgumentList '--defaults-file=C:\laragon\bin\mysql\mysql-8.4.3-winx64\my.ini'
```

- [ ] **Abrir perfil de paciente de prueba**

Navegar a: `http://127.0.0.1:8080/clientes/1` (o cualquier cliente con dentadura inicializada).

Comprobar:
- [ ] Sidebar: datos personales se muestran correctamente
- [ ] Odontograma oclusal (multi-cara): arcadas superior e inferior con dientes coloreados
- [ ] Odontograma anatómico: vista bucal frontal con coronas y raíces
- [ ] Historial clínico: tabla con resumen financiero
- [ ] Próximas citas: lista o mensaje "sin citas"
- [ ] Modal diente: al hacer clic en un diente en el oclusal, se abre el modal de edición
- [ ] Modal diente desde anatómico: al hacer clic en un diente, se abre el mismo modal
- [ ] Guardar cambios en un diente: el estado se actualiza correctamente
- [ ] Modal historial: botón "+ Añadir registro" abre el modal
- [ ] Modal cita: botón "+ Cita" abre el modal

- [ ] **Commit final**

```bash
git add -A
git commit -m "refactor: completar migracion a componentes x-cliente.*"
```
