# Modal detalle de cita — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reemplazar el `alert()` del calendario con un modal de flujo rápido que muestre los datos de la cita y permita cambiar estado o eliminarla sin recargar la página.

**Architecture:** Solo frontend (`calendario.blade.php`) más dos pequeños parches de backend: añadir `motivo`/`notas` a los extendedProps del JSON de FullCalendar y devolver JSON desde `CitaController@destroy`. El modal lee los datos del evento FullCalendar en memoria (sin llamada extra al servidor al abrir), envía `PUT` o `DELETE` vía `fetch`, y actualiza el evento directamente con la API de FullCalendar.

**Tech Stack:** Laravel 11, Blade, FullCalendar 6, Vanilla JS, PHPUnit 11

---

## Mapa de ficheros

| Acción | Fichero |
|--------|---------|
| Modificar | `app/Services/CitaService.php` — añadir `motivo` y `notas` al mapa de `citasPorRango()` |
| Modificar | `app/Http/Controllers/CitaController.php` — rama JSON en `destroy()` |
| Modificar | `tests/Feature/Citas/CitaTest.php` — test JSON destroy |
| Modificar | `resources/views/citas/calendario.blade.php` — HTML modal, CSS, JS |

---

## Task 1: Preparar backend — extendedProps y destroy JSON

**Files:**
- Modify: `app/Services/CitaService.php`
- Modify: `app/Http/Controllers/CitaController.php`
- Modify: `tests/Feature/Citas/CitaTest.php`

- [ ] **Paso 1: Escribir el test que falla para destroy JSON**

En `tests/Feature/Citas/CitaTest.php`, añadir al final de la clase (antes del `}`):

```php
public function test_gestor_puede_eliminar_cita_via_json(): void
{
    $gestor  = User::factory()->gestor()->create();
    $cliente = Cliente::factory()->create();
    $cita    = Cita::factory()->create(['cliente_id' => $cliente->id]);

    $this->actingAs($gestor)
         ->deleteJson(route('citas.destroy', $cita))
         ->assertOk()
         ->assertJson(['ok' => true]);

    $this->assertDatabaseMissing('citas', ['id' => $cita->id]);
}
```

- [ ] **Paso 2: Ejecutar el test — verificar que falla**

```bash
C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe vendor/bin/phpunit tests/Feature/Citas/CitaTest.php::test_gestor_puede_eliminar_cita_via_json --no-coverage
```

Esperado: FAIL — el método devuelve redirect, no JSON.

- [ ] **Paso 3: Añadir rama JSON a `CitaController@destroy`**

En `app/Http/Controllers/CitaController.php`, reemplazar:

```php
public function destroy(Cita $cita)
{
    $cita->delete();
    return back()->with('success', 'Cita eliminada.');
}
```

por:

```php
public function destroy(Cita $cita)
{
    $cita->delete();
    if (request()->expectsJson()) {
        return response()->json(['ok' => true]);
    }
    return back()->with('success', 'Cita eliminada.');
}
```

- [ ] **Paso 4: Añadir `motivo` y `notas` a `citasPorRango()` en `CitaService`**

En `app/Services/CitaService.php`, dentro del `map(...)` de `citasPorRango()`, añadir dos líneas:

```php
return Cita::with('cliente:id,apellidos,nombre')
    ->where('fecha_hora', '>=', $start)
    ->where('fecha_hora', '<',  $end)
    ->orderBy('fecha_hora')
    ->get()
    ->map(fn($c) => [
        'id'             => $c->id,
        'title'          => $c->cliente->nombre_completo . ' — ' . $c->motivo,
        'start'          => $c->fecha_hora->toIso8601String(),
        'end'            => $c->fecha_hora->addMinutes($c->duracion_minutos)->toIso8601String(),
        'color'          => $c->estado_color,
        'estado'         => $c->estado,
        'motivo'         => $c->motivo,
        'notas'          => $c->notas,
        'cliente_id'     => $c->cliente_id,
        'cliente_nombre' => $c->cliente->nombre_completo,
    ]);
```

- [ ] **Paso 5: Ejecutar los tests del área de citas**

```bash
C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe vendor/bin/phpunit tests/Feature/Citas/CitaTest.php --no-coverage
```

Esperado: todos los tests PASS (incluyendo el nuevo).

- [ ] **Paso 6: Commit**

```bash
git add app/Services/CitaService.php app/Http/Controllers/CitaController.php tests/Feature/Citas/CitaTest.php
git commit -m "feat: extendedProps motivo/notas en citasPorRango y JSON en destroy"
```

---

## Task 2: HTML + CSS del modal de detalle

**Files:**
- Modify: `resources/views/citas/calendario.blade.php`

- [ ] **Paso 1: Añadir CSS del modal al bloque `@push('styles')`**

Añadir al final del bloque `<style>` existente (antes de `</style>`):

```css
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
```

- [ ] **Paso 2: Añadir el HTML del modal después del popup de día**

Justo después del bloque `{{-- ── POPUP contextual de día ──── --}}` y antes de `{{-- ── MODAL nueva cita ──── --}}`, insertar:

```html
{{-- ── MODAL detalle de cita ─────────────────────────────────────────────────── --}}
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
```

- [ ] **Paso 3: Commit**

```bash
git add resources/views/citas/calendario.blade.php
git commit -m "feat: HTML y CSS del modal de detalle de cita"
```

---

## Task 3: JS — constantes globales y función `abrirModalDetalle`

**Files:**
- Modify: `resources/views/citas/calendario.blade.php`

- [ ] **Paso 1: Añadir constantes globales y `abrirModalDetalle` antes de `abrirModalNuevaCita`**

Justo antes de la función `abrirModalNuevaCita` (al final del bloque `<script>`), insertar:

```js
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

    document.getElementById('detalle-motivo').textContent  = props.motivo || '—';
    document.getElementById('detalle-notas').textContent   = props.notas  || '—';

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
```

- [ ] **Paso 2: Reemplazar `alert()` en `eventClick`**

Localizar en el bloque `<script>`:

```js
        eventClick: function(info) {
            info.jsEvent.stopPropagation();
            const e = info.event;
            const props = e.extendedProps;
            const partes = e.title.split(' — ');
            alert([
                `Paciente: ${props.cliente_nombre}`,
                `Motivo: ${partes[1] || partes[0]}`,
                `Estado: ${props.estado}`,
                `Inicio: ${e.start.toLocaleString('es-ES')}`,
            ].join('\n'));
        },
```

Reemplazar por:

```js
        eventClick: function(info) {
            info.jsEvent.stopPropagation();
            abrirModalDetalle(info.event);
        },
```

- [ ] **Paso 3: Commit**

```bash
git add resources/views/citas/calendario.blade.php
git commit -m "feat: abrirModalDetalle reemplaza alert() en eventClick"
```

---

## Task 4: JS — función `cambiarEstadoCita`

**Files:**
- Modify: `resources/views/citas/calendario.blade.php`

- [ ] **Paso 1: Añadir `cambiarEstadoCita` junto a las otras funciones globales**

Justo después de la función `abrirModalDetalle` (antes de `abrirModalNuevaCita`), insertar:

```js
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
```

- [ ] **Paso 2: Commit**

```bash
git add resources/views/citas/calendario.blade.php
git commit -m "feat: cambiarEstadoCita — PUT fetch + actualización en FullCalendar"
```

---

## Task 5: JS — cierre de modal y `eliminarCita` (double-confirm)

**Files:**
- Modify: `resources/views/citas/calendario.blade.php`

- [ ] **Paso 1: Añadir event listeners del modal dentro de `DOMContentLoaded`**

Al final del bloque dentro de `document.addEventListener('DOMContentLoaded', function () { ... });`, justo antes del `});` de cierre, insertar:

```js
    // ── Modal detalle ─────────────────────────────────────────────────────────

    document.getElementById('detalle-close').addEventListener('click', function() {
        document.getElementById('modal-detalle-cita').classList.remove('open');
    });

    document.getElementById('modal-detalle-cita').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });

    document.getElementById('detalle-btn-eliminar').addEventListener('click', function() {
        if (!citaActual) return;

        if (!this._confirmPending) {
            this._confirmPending  = true;
            this.textContent      = '¿Seguro? Clic para confirmar';
            this.style.background = '#ef4444';
            this.style.color      = '#fff';
            this.style.border     = '1px solid #ef4444';

            const btn = this;
            btn._confirmTimer = setTimeout(() => {
                btn._confirmPending  = false;
                btn.textContent      = '🗑 Eliminar cita';
                btn.style.background = '#fef2f2';
                btn.style.color      = '#ef4444';
                btn.style.border     = '1px solid #fecaca';
            }, 3000);
            return;
        }

        clearTimeout(this._confirmTimer);
        this.disabled    = true;
        this.textContent = 'Eliminando…';

        fetch(`/citas/${citaActual.id}`, {
            method: 'DELETE',
            headers: {
                'Accept':       'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
        .then(r => { if (!r.ok) throw new Error(); })
        .then(() => {
            citaActual.remove();
            document.getElementById('modal-detalle-cita').classList.remove('open');
        })
        .catch(() => {
            this.disabled        = false;
            this._confirmPending = false;
            this.textContent     = '🗑 Eliminar cita';
            this.style.background = '#fef2f2';
            this.style.color     = '#ef4444';
            this.style.border    = '1px solid #fecaca';
            alert('No se pudo eliminar la cita. Recarga la página e inténtalo de nuevo.');
        });
    });
```

- [ ] **Paso 2: Commit**

```bash
git add resources/views/citas/calendario.blade.php
git commit -m "feat: eliminarCita double-confirm + cierre del modal de detalle"
```

---

## Task 6: Push y verificación final

- [ ] **Paso 1: Ejecutar la suite completa de citas**

```bash
C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe vendor/bin/phpunit tests/Feature/Citas/ tests/Unit/Services/CitaServiceTest.php --no-coverage
```

Esperado: todos los tests PASS.

- [ ] **Paso 2: Push a develop**

```bash
git push origin develop
```

---

## Notas de prueba manual

1. Abrir `http://127.0.0.1:8080/calendario` como gestor
2. Hacer clic en un evento → el modal muestra paciente, motivo, duración, notas y botones de estado
3. Pulsar un botón de estado → el evento cambia de color en el calendario y el modal se cierra
4. Abrir el mismo evento → el header muestra el nuevo estado
5. Pulsar "Eliminar cita" una vez → el texto cambia a "¿Seguro? Clic para confirmar"
6. Pulsar de nuevo → el evento desaparece del calendario
7. Verificar que esperar 3 s sin confirmar revierte el botón al estado original
