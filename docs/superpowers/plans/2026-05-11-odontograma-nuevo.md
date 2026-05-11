# Odontograma Nuevo — Lámina Anatómica Interactiva — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Añadir un tercer odontograma de lámina anatómica clásica con 4 vistas SVG apiladas por diente (raíz, vestibular, oclusal/incisal, lingual), 29 estados clínicos y panel lateral inline, accesible desde una nueva pestaña en la ficha del paciente.

**Architecture:** Componente Blade serializa los datos de dentadura del paciente a `window.odontogramaNuevoData`; módulo vanilla JS `odontograma-nuevo.js` renderiza SVG dinámicamente y guarda vía AJAX al endpoint `POST /clientes/{id}/dentadura` existente. Las constantes del modelo Dentadura se amplían; las columnas de BD ya son VARCHAR — sin migración de esquema.

**Tech Stack:** Laravel 11, Blade, PHP 8.3, Vanilla JS ES6, SVG inline, MySQL 8.4 (VARCHAR columns), PHPUnit

---

## File Map

| Fichero | Acción | Responsabilidad |
|---------|--------|-----------------|
| `app/Models/Dentadura.php` | Modificar | ESTADOS_PIEZA 6→27, ESTADOS_CARA 6→13 |
| `resources/views/components/cliente/odontograma/nuevo/index.blade.php` | Crear | Serializa JSON, emite `window.*`, carga JS |
| `public/js/odontograma-nuevo.js` | Crear | SVG generators, interacción, save AJAX |
| `public/css/app.css` | Modificar | Clases `.odon-nuevo-*`, tabs, panel |
| `resources/views/clientes/partials/_odontograma.blade.php` | Modificar | Tab switcher (3 pestañas) |
| `tests/Feature/DentaduraEstadosNuevosTest.php` | Crear | Verifica constantes del modelo |
| `tests/Feature/DentaduraEndpointEstadosTest.php` | Crear | Verifica endpoint con estados nuevos |

---

## Task 1: Feature branch + extensión del modelo Dentadura

**Files:**
- Modify: `app/Models/Dentadura.php`
- Create: `tests/Feature/DentaduraEstadosNuevosTest.php`

- [ ] **Step 1.1: Crear la rama de feature**

```bash
git checkout develop
git pull origin develop
git checkout -b feature/odontograma-nuevo
```

- [ ] **Step 1.2: Escribir el test**

Crear `tests/Feature/DentaduraEstadosNuevosTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Dentadura;
use Tests\TestCase;

class DentaduraEstadosNuevosTest extends TestCase
{
    /** @test */
    public function estados_pieza_contiene_los_27_estados_requeridos(): void
    {
        $requeridos = [
            'presente', 'ausente', 'corona', 'puente', 'implante', 'endodoncia',
            'no_erupcionado', 'extrac_indicada', 'extraido', 'temporal',
            'pulpitis', 'necrosis', 'apicectomia', 'incluido', 'supernumerario',
            'movilidad_1', 'movilidad_2', 'movilidad_3',
            'carilla', 'pilar_puente', 'pontico', 'prot_removible',
            'giroversion', 'migracion', 'diastema', 'fluorosis', 'agenesia',
        ];

        foreach ($requeridos as $estado) {
            $this->assertArrayHasKey(
                $estado,
                Dentadura::ESTADOS_PIEZA,
                "Falta el estado de pieza: {$estado}"
            );
        }
    }

    /** @test */
    public function estados_cara_contiene_los_13_estados_requeridos(): void
    {
        $requeridos = [
            'sano', 'caries', 'obturacion', 'fractura', 'sellante', 'desgaste',
            'caries_det', 'composite', 'amalgama', 'erosion',
            'tincion', 'fisura', 'reconstruccion',
        ];

        foreach ($requeridos as $estado) {
            $this->assertArrayHasKey(
                $estado,
                Dentadura::ESTADOS_CARA,
                "Falta el estado de cara: {$estado}"
            );
        }
    }

    /** @test */
    public function cada_estado_pieza_tiene_label_color_e_icono(): void
    {
        foreach (Dentadura::ESTADOS_PIEZA as $key => $data) {
            $this->assertArrayHasKey('label', $data, "Estado pieza '{$key}' sin label");
            $this->assertArrayHasKey('color', $data, "Estado pieza '{$key}' sin color");
            $this->assertArrayHasKey('icono', $data, "Estado pieza '{$key}' sin icono");
        }
    }

    /** @test */
    public function cada_estado_cara_tiene_label_y_color(): void
    {
        foreach (Dentadura::ESTADOS_CARA as $key => $data) {
            $this->assertArrayHasKey('label', $data, "Estado cara '{$key}' sin label");
            $this->assertArrayHasKey('color', $data, "Estado cara '{$key}' sin color");
        }
    }
}
```

- [ ] **Step 1.3: Ejecutar el test para ver que falla**

```bash
php vendor/bin/phpunit tests/Feature/DentaduraEstadosNuevosTest.php --testdox
```

Esperado: 2-4 tests fallan con mensajes "Falta el estado de pieza/cara: ..."

- [ ] **Step 1.4: Ampliar ESTADOS_PIEZA en `app/Models/Dentadura.php`**

Reemplazar el bloque `const ESTADOS_PIEZA` completo (líneas ~40-47):

```php
const ESTADOS_PIEZA = [
    // ── Existentes (compatibilidad API Android) ─────────────────────
    'presente'        => ['label' => 'Presente',            'color' => '#4ade80', 'icono' => '✓'],
    'ausente'         => ['label' => 'Ausente',             'color' => '#6b7280', 'icono' => '○'],
    'corona'          => ['label' => 'Corona',              'color' => '#b45309', 'icono' => '♛'],
    'puente'          => ['label' => 'Puente',              'color' => '#3b82f6', 'icono' => 'P'],
    'implante'        => ['label' => 'Implante',            'color' => '#1d4ed8', 'icono' => 'I'],
    'endodoncia'      => ['label' => 'Endodoncia',          'color' => '#dc2626', 'icono' => 'E'],
    // ── Nuevos ──────────────────────────────────────────────────────
    'no_erupcionado'  => ['label' => 'No erupcionado',      'color' => '#fef3c7', 'icono' => '?'],
    'extrac_indicada' => ['label' => 'Extracción indicada', 'color' => '#fca5a5', 'icono' => '!'],
    'extraido'        => ['label' => 'Extraído',            'color' => '#9ca3af', 'icono' => '✕'],
    'temporal'        => ['label' => 'Temporal (deciduo)',  'color' => '#f9a8d4', 'icono' => 'T'],
    'pulpitis'        => ['label' => 'Pulpitis',            'color' => '#f97316', 'icono' => 'Pu'],
    'necrosis'        => ['label' => 'Necrosis pulpar',     'color' => '#1f2937', 'icono' => 'N'],
    'apicectomia'     => ['label' => 'Apicectomía',         'color' => '#0f766e', 'icono' => 'Ap'],
    'incluido'        => ['label' => 'Incluido/Retenido',   'color' => '#7c3aed', 'icono' => 'R'],
    'supernumerario'  => ['label' => 'Supernumerario',      'color' => '#db2777', 'icono' => 'S'],
    'movilidad_1'     => ['label' => 'Movilidad Grado I',   'color' => '#facc15', 'icono' => 'M1'],
    'movilidad_2'     => ['label' => 'Movilidad Grado II',  'color' => '#ea580c', 'icono' => 'M2'],
    'movilidad_3'     => ['label' => 'Movilidad Grado III', 'color' => '#b91c1c', 'icono' => 'M3'],
    'carilla'         => ['label' => 'Carilla',             'color' => '#93c5fd', 'icono' => 'Ca'],
    'pilar_puente'    => ['label' => 'Pilar de puente',     'color' => '#a78bfa', 'icono' => 'Pp'],
    'pontico'         => ['label' => 'Póntico de puente',   'color' => '#c4b5fd', 'icono' => 'Po'],
    'prot_removible'  => ['label' => 'Prótesis removible',  'color' => '#fb923c', 'icono' => 'Pr'],
    'giroversion'     => ['label' => 'Giroversión',         'color' => '#84cc16', 'icono' => 'G'],
    'migracion'       => ['label' => 'Migración',           'color' => '#22d3ee', 'icono' => '→'],
    'diastema'        => ['label' => 'Diastema',            'color' => '#e879f9', 'icono' => '◁▷'],
    'fluorosis'       => ['label' => 'Fluorosis',           'color' => '#a3e635', 'icono' => 'F'],
    'agenesia'        => ['label' => 'Agenesia',            'color' => '#94a3b8', 'icono' => '∅'],
];
```

- [ ] **Step 1.5: Ampliar ESTADOS_CARA en `app/Models/Dentadura.php`**

Reemplazar el bloque `const ESTADOS_CARA` completo (líneas ~53-60):

```php
const ESTADOS_CARA = [
    // ── Existentes ──────────────────────────────────────────────────
    'sano'           => ['label' => 'Sano',             'color' => '#4ade80'],
    'caries'         => ['label' => 'Caries',           'color' => '#ef4444'],
    'obturacion'     => ['label' => 'Obturación',       'color' => '#f97316'],
    'fractura'       => ['label' => 'Fractura',         'color' => '#a855f7'],
    'sellante'       => ['label' => 'Sellante',         'color' => '#06b6d4'],
    'desgaste'       => ['label' => 'Desgaste',         'color' => '#78716c'],
    // ── Nuevos ──────────────────────────────────────────────────────
    'caries_det'     => ['label' => 'Caries detenida',  'color' => '#f59e0b'],
    'composite'      => ['label' => 'Composite',        'color' => '#3b82f6'],
    'amalgama'       => ['label' => 'Amalgama',         'color' => '#64748b'],
    'erosion'        => ['label' => 'Erosión',          'color' => '#d97706'],
    'tincion'        => ['label' => 'Tinción',          'color' => '#92400e'],
    'fisura'         => ['label' => 'Fisura',           'color' => '#374151'],
    'reconstruccion' => ['label' => 'Reconstrucción',   'color' => '#059669'],
];
```

- [ ] **Step 1.6: Ejecutar el test para confirmar que pasa**

```bash
php vendor/bin/phpunit tests/Feature/DentaduraEstadosNuevosTest.php --testdox
```

Esperado: 4 tests en verde.

- [ ] **Step 1.7: Ejecutar la suite completa para confirmar sin regresiones**

```bash
php vendor/bin/phpunit --testdox
```

Esperado: todos los tests en verde.

- [ ] **Step 1.8: Commit**

```bash
git add app/Models/Dentadura.php tests/Feature/DentaduraEstadosNuevosTest.php
git commit -m "feat: ampliar ESTADOS_PIEZA (27) y ESTADOS_CARA (13) en modelo Dentadura"
```

---

## Task 2: CSS para el nuevo odontograma

**Files:**
- Modify: `public/css/app.css` (añadir al final)

- [ ] **Step 2.1: Añadir estilos al final de `public/css/app.css`**

```css
/* ══════════════════════════════════════════════════════════════════════
   ODONTOGRAMA NUEVO — Lámina Anatómica Clásica
   ══════════════════════════════════════════════════════════════════════ */

.odon-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid #2e1a06;
    margin-bottom: 0;
}
.odon-tab {
    padding: 7px 16px;
    font-size: .8rem;
    font-weight: 600;
    color: #6b4020;
    background: #f0e8d6;
    border: 1px solid #c8a870;
    border-bottom: none;
    cursor: pointer;
    font-family: 'Georgia', serif;
    letter-spacing: .3px;
    transition: background .12s;
    margin-right: 2px;
}
.odon-tab:hover { background: #e8d8b8; }
.odon-tab.odon-tab-active {
    background: #faf6ee;
    color: #2e1a06;
    border-color: #2e1a06;
    border-bottom: 2px solid #faf6ee;
    margin-bottom: -2px;
    z-index: 1;
    position: relative;
}
.odon-nuevo-wrap {
    background: #faf6ee;
    background-image:
        linear-gradient(rgba(140,120,80,.1) 1px, transparent 1px),
        linear-gradient(90deg, rgba(140,120,80,.1) 1px, transparent 1px);
    background-size: 20px 20px;
    border: 1px solid #2e1a06;
    border-top: none;
    border-radius: 0 0 6px 6px;
    overflow: hidden;
}
.odon-nuevo-body { display: flex; }
.odon-nuevo-board {
    flex: 1;
    min-width: 0;
    padding: 8px 10px;
    overflow-x: auto;
}
.odon-nuevo-panel {
    width: 215px;
    flex-shrink: 0;
    border-left: 1.5px solid #2e1a06;
    display: flex;
    flex-direction: column;
    background: #faf6ee;
    font-size: .85rem;
}
.odon-nuevo-arch-lbl {
    text-align: center;
    font-size: .72rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: #6b4020;
    margin: 3px 0;
    font-family: 'Georgia', serif;
}
.odon-nuevo-row {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 1px;
    min-width: 590px;
}
.odon-nuevo-row.lower { align-items: flex-start; }
.odon-nuevo-divider {
    border-top: 2px solid #2e1a06;
    margin: 1px auto;
    min-width: 590px;
}
.odon-nuevo-qsep {
    width: 8px;
    flex-shrink: 0;
    border-left: 1px dashed #8b6030;
    align-self: stretch;
    margin: 4px 0;
}
.odon-nuevo-tc {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 36px;
    flex-shrink: 0;
    cursor: pointer;
    position: relative;
    border-radius: 1px;
    transition: background .1s;
}
.odon-nuevo-tc:hover { background: rgba(37,99,235,.06); }
.odon-nuevo-tc.odon-sel {
    background: rgba(37,99,235,.12);
    outline: 1px solid rgba(37,99,235,.45);
}
.odon-nuevo-fdi {
    font-family: 'Courier New', monospace;
    font-size: .6rem;
    font-weight: bold;
    color: #2e1a06;
    background: #ede0c4;
    border: .8px solid #4a3010;
    width: 22px;
    height: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.odon-nuevo-tc svg { display: block; overflow: visible; flex-shrink: 0; }
.odon-tz { cursor: pointer; transition: filter .12s; }
.odon-tz:hover { filter: brightness(.72) saturate(1.4); }
.odon-psec {
    border-bottom: 1px solid #c8a870;
    padding: 8px 9px;
}
.odon-psec:last-child {
    border-bottom: none;
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.odon-phdr {
    font-size: .7rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #2e1a06;
    margin-bottom: 5px;
}
.odon-sel-num { font-size: .95rem; font-weight: bold; color: #2e1a06; }
.odon-sel-type { font-size: .78rem; color: #6b4020; margin-left: 3px; }
.odon-face-chips { display: flex; flex-wrap: wrap; gap: 3px; margin-top: 5px; }
.odon-fc {
    font-size: .65rem;
    padding: 2px 6px;
    border: .8px solid #4a3010;
    background: #ede0c4;
    color: #2e1a06;
    cursor: pointer;
    border-radius: 1px;
    font-family: 'Courier New', monospace;
}
.odon-fc.odon-fc-on { background: #2e1a06; color: #f5f0e8; }
.odon-mode-row { display: flex; gap: 4px; margin-top: 6px; }
.odon-mbtn {
    flex: 1;
    font-size: .7rem;
    padding: 4px 2px;
    border: .8px solid #4a3010;
    background: #ede0c4;
    color: #2e1a06;
    cursor: pointer;
    font-family: 'Georgia', serif;
    text-align: center;
    border-radius: 1px;
}
.odon-mbtn.odon-mbtn-on { background: #2e1a06; color: #f5f0e8; }
.odon-states { flex: 1; overflow-y: auto; padding-top: 2px; }
.odon-sgrp { margin-bottom: 6px; }
.odon-sgrp-lbl {
    font-size: .65rem;
    font-weight: bold;
    text-transform: uppercase;
    color: #6b4020;
    letter-spacing: .3px;
    margin-bottom: 2px;
    padding-left: 2px;
}
.odon-si {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: .78rem;
    color: #2e1a06;
    padding: 2px 4px;
    cursor: pointer;
    border-radius: 1px;
    font-family: 'Georgia', serif;
}
.odon-si:hover { background: #e8d8b8; }
.odon-si.odon-si-on { background: #2e1a06; color: #f5f0e8; }
.odon-sdot {
    width: 9px; height: 9px;
    border-radius: 50%;
    border: .7px solid rgba(0,0,0,.2);
    flex-shrink: 0;
}
.odon-nuevo-legend {
    border-top: 1.5px solid #2e1a06;
    background: #f0e8d6;
    padding: 6px 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 4px 12px;
}
.odon-li { display: flex; align-items: center; gap: 3px; font-size: .7rem; color: #2e1a06; }
.odon-ldot { width: 9px; height: 9px; border-radius: 50%; border: .6px solid rgba(0,0,0,.18); }
.odon-saved { outline: 2px solid #059669 !important; }
```

- [ ] **Step 2.2: Verificar que el CSS no rompe nada**

```bash
php artisan view:clear
```

Abrir `http://127.0.0.1:8080/clientes/{cualquier-id}` y confirmar que la ficha sigue renderizando sin errores.

- [ ] **Step 2.3: Commit**

```bash
git add public/css/app.css
git commit -m "feat: estilos CSS para odontograma nuevo (tabs, lámina anatómica, panel)"
```

---

## Task 3: Componente Blade

**Files:**
- Create: `resources/views/components/cliente/odontograma/nuevo/index.blade.php`

- [ ] **Step 3.1: Crear el directorio**

```bash
mkdir -p resources/views/components/cliente/odontograma/nuevo
```

- [ ] **Step 3.2: Crear el componente**

Crear `resources/views/components/cliente/odontograma/nuevo/index.blade.php`:

```blade
@props(['cliente', 'dentadura'])

@php
    $dentaduraJson = json_encode($dentadura->mapWithKeys(function ($d, $key) {
        return [$key => [
            'estado_pieza'    => $d->estado_pieza    ?? 'presente',
            'cara_vestibular' => $d->cara_vestibular ?? 'sano',
            'cara_lingual'    => $d->cara_lingual    ?? 'sano',
            'cara_mesial'     => $d->cara_mesial     ?? 'sano',
            'cara_distal'     => $d->cara_distal     ?? 'sano',
            'cara_oclusal'    => $d->cara_oclusal    ?? 'sano',
            'tiene_oclusal'   => in_array((int)$d->num_diente, \App\Models\Dentadura::DIENTES_CON_OCLUSAL),
        ]];
    }));
@endphp

<div class="odon-nuevo-wrap">
    <div class="odon-nuevo-body">
        <div class="odon-nuevo-board" id="odon-nuevo-board"></div>

        @if(auth()->user()->isGestor())
        <div class="odon-nuevo-panel" id="odon-nuevo-panel">
            <div class="odon-psec">
                <div class="odon-phdr">Diente seleccionado</div>
                <div id="odon-sel-info" style="color:#6b4020;font-size:.78rem;opacity:.6">
                    — Haz clic en un diente —
                </div>
                <div class="odon-face-chips" id="odon-fchips"></div>
                <div class="odon-mode-row" id="odon-mrow" style="display:none">
                    <div class="odon-mbtn odon-mbtn-on" id="odon-btn-cara"
                         onclick="odonSetMode('cara')">Por cara</div>
                    <div class="odon-mbtn" id="odon-btn-pieza"
                         onclick="odonSetMode('pieza')">Pieza entera</div>
                </div>
            </div>
            <div class="odon-psec">
                <div class="odon-phdr">Estado</div>
                <div class="odon-states" id="odon-states">
                    <span style="opacity:.5;font-size:.78rem">Selecciona un diente primero</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="odon-nuevo-legend" id="odon-nuevo-legend"></div>
</div>

@push('scripts')
<script>
window.odontogramaNuevoUrl    = '{{ route('clientes.dentadura', $cliente) }}';
window.odontogramaNuevoData   = {!! $dentaduraJson !!};
window.odontogramaNuevoGestor = {{ auth()->user()->isGestor() ? 'true' : 'false' }};
</script>
<script src="{{ asset('js/odontograma-nuevo.js') }}"></script>
@endpush
```

- [ ] **Step 3.3: Verificar sintaxis Blade**

```bash
php artisan view:clear
```

Sin errores PHP.

- [ ] **Step 3.4: Commit**

```bash
git add resources/views/components/cliente/odontograma/nuevo/
git commit -m "feat: componente Blade odontograma nuevo (serialización JSON + container)"
```

---

## Task 4: Módulo JS — constantes + generadores SVG

**Files:**
- Create: `public/js/odontograma-nuevo.js`

- [ ] **Step 4.1: Crear el fichero con constantes y helpers**

Crear `public/js/odontograma-nuevo.js`:

```javascript
// ══════════════════════════════════════════════════════════════════════════
// ODONTOGRAMA NUEVO — Lámina Anatómica Interactiva
// ══════════════════════════════════════════════════════════════════════════

const ODON_UP = [18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28];
const ODON_LO = [48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38];

// Face states (13 — includes legacy obturacion/sellante/desgaste)
const ODON_FS = [
    {k:'sano',           l:'Sano',             c:'#faf5ea', b:'#c8b090'},
    {k:'caries',         l:'Caries',           c:'#ef4444'},
    {k:'caries_det',     l:'Caries detenida',  c:'#f59e0b'},
    {k:'composite',      l:'Composite',        c:'#3b82f6'},
    {k:'amalgama',       l:'Amalgama',         c:'#64748b'},
    {k:'sellante',       l:'Sellante',         c:'#06b6d4'},
    {k:'erosion',        l:'Erosión',          c:'#d97706'},
    {k:'fractura',       l:'Fractura',         c:'#991b1b'},
    {k:'tincion',        l:'Tinción',          c:'#92400e'},
    {k:'fisura',         l:'Fisura',           c:'#374151'},
    {k:'reconstruccion', l:'Reconstrucción',   c:'#059669'},
    {k:'desgaste',       l:'Desgaste',         c:'#78716c'},
    {k:'obturacion',     l:'Obturación',       c:'#f97316'},
];

// Piece states (27)
const ODON_PS = [
    {k:'presente',        l:'Presente',            c:'#faf5ea', b:'#c8b090'},
    {k:'ausente',         l:'Ausente',             c:'#d1d5db'},
    {k:'no_erupcionado',  l:'No erupcionado',      c:'#fef3c7'},
    {k:'extrac_indicada', l:'Extracción indicada', c:'#fca5a5'},
    {k:'extraido',        l:'Extraído',            c:'#9ca3af'},
    {k:'temporal',        l:'Temporal (deciduo)',  c:'#f9a8d4'},
    {k:'implante',        l:'Implante',            c:'#1d4ed8'},
    {k:'corona',          l:'Corona',              c:'#b45309'},
    {k:'puente',          l:'Puente',              c:'#3b82f6'},
    {k:'endodoncia',      l:'Endodoncia',          c:'#dc2626'},
    {k:'pulpitis',        l:'Pulpitis',            c:'#f97316'},
    {k:'necrosis',        l:'Necrosis pulpar',     c:'#1f2937'},
    {k:'apicectomia',     l:'Apicectomía',         c:'#0f766e'},
    {k:'incluido',        l:'Incluido/Retenido',   c:'#7c3aed'},
    {k:'supernumerario',  l:'Supernumerario',      c:'#db2777'},
    {k:'movilidad_1',     l:'Movilidad Grado I',   c:'#facc15'},
    {k:'movilidad_2',     l:'Movilidad Grado II',  c:'#ea580c'},
    {k:'movilidad_3',     l:'Movilidad Grado III', c:'#b91c1c'},
    {k:'carilla',         l:'Carilla',             c:'#93c5fd'},
    {k:'pilar_puente',    l:'Pilar de puente',     c:'#a78bfa'},
    {k:'pontico',         l:'Póntico de puente',   c:'#c4b5fd'},
    {k:'prot_removible',  l:'Prótesis removible',  c:'#fb923c'},
    {k:'giroversion',     l:'Giroversión',         c:'#84cc16'},
    {k:'migracion',       l:'Migración',           c:'#22d3ee'},
    {k:'diastema',        l:'Diastema',            c:'#e879f9'},
    {k:'fluorosis',       l:'Fluorosis',           c:'#a3e635'},
    {k:'agenesia',        l:'Agenesia',            c:'#94a3b8'},
];

const SK  = '#2a1a08';
const SKR = '#8b5830';
const SF  = '#faf5ea';
const RF  = '#e8d4a8';

let odonState = {};
let odonUI    = { tooth: null, face: null, mode: 'cara' };

function odonType(n) {
    if ([11,12,21,22,31,32,41,42].includes(n)) return 'inc';
    if ([13,23,33,43].includes(n))             return 'can';
    if ([14,15,24,25,34,35,44,45].includes(n)) return 'pre';
    if ([16,17,18,26,27,28].includes(n))       return 'mS';
    return 'mI';
}
function odonDistalRight(n) { return [1,4].includes(Math.floor(n/10)); }
function odonIsAbsent(n) {
    return ['ausente','extraido','agenesia','no_erupcionado'].includes(odonState[n]?.pieza);
}
function odonFaceFill(n, face) {
    const s = odonState[n];
    if (!s) return SF;
    if (s.pieza !== 'presente') return ODON_PS.find(x => x.k === s.pieza)?.c || '#d1d5db';
    return ODON_FS.find(x => x.k === s[face])?.c || SF;
}
function odonRootFill(n) {
    const s = odonState[n];
    if (!s) return RF;
    if (['ausente','extraido','agenesia'].includes(s.pieza)) return 'none';
    if (s.pieza !== 'presente') return ODON_PS.find(x => x.k === s.pieza)?.c || RF;
    return RF;
}
```

- [ ] **Step 4.2: Añadir los 5 generadores SVG al mismo fichero**

```javascript
function odonSvgRoot(n) {
    const t = odonType(n), f = odonRootFill(n);
    const gone = ['ausente','extraido','agenesia'].includes(odonState[n]?.pieza);
    const ra = `fill="${f}" stroke="${SKR}" stroke-width=".9" stroke-linejoin="round"`;
    let p = '';
    if (!gone) {
        if (t === 'inc') {
            p = `<path d="M18,0C16,0 13,7 13,20L13,30 23,30 23,20C23,7 20,0 18,0Z" ${ra}/>`;
        } else if (t === 'can') {
            p = `<path d="M18,0C15,0 11,9 11,24L11,30 25,30 25,24C25,9 21,0 18,0Z" ${ra}/>`;
        } else if (t === 'pre') {
            p = `<path d="M11,0C9,0 7,6 7,18L7,30 16,30 16,18C16,6 13,0 11,0Z" ${ra}/>
                 <path d="M25,0C23,0 21,6 21,18L21,30 30,30 30,18C30,6 27,0 25,0Z" ${ra}/>`;
        } else if (t === 'mS') {
            p = `<path d="M9,3C7,3 5,9 5,20L5,30 15,30 15,20C15,9 11,3 9,3Z" ${ra}/>
                 <path d="M27,3C25,3 23,9 23,20L23,30 33,30 33,20C33,9 29,3 27,3Z" ${ra}/>
                 <path d="M18,0C16,0 14,6 14,17L14,27 22,27 22,17C22,6 20,0 18,0Z" ${ra}/>`;
        } else {
            p = `<path d="M10,0C8,0 6,7 6,20L6,30 17,30 17,20C17,7 13,0 10,0Z" ${ra}/>
                 <path d="M26,0C24,0 22,7 22,20L22,30 32,30 32,20C32,7 29,0 26,0Z" ${ra}/>`;
        }
    }
    const g = window.odontogramaNuevoGestor;
    const click = g ? `onclick="odonZoneClick(${n},'root',event)"` : '';
    return `<svg viewBox="0 0 36 30" width="36" height="30">
        <rect width="36" height="30" fill="transparent" class="odon-tz" ${click} title="Raíz — pieza entera"/>
        ${p}
    </svg>`;
}

function odonSvgCej() {
    return `<svg viewBox="0 0 36 4" width="36" height="4">
        <line x1="1" y1="2" x2="35" y2="2" stroke="${SKR}" stroke-width="1.4"/>
    </svg>`;
}

function odonSvgCrownV(n) {
    const t = odonType(n), f = odonFaceFill(n, 'V');
    const abs = odonIsAbsent(n), ex = odonState[n]?.pieza === 'extraido';
    let d = '', dec = '';
    if (t === 'inc') {
        d = 'M10,2L26,2C28,2 30,5 30,11L30,34C30,39 24,41 18,41C12,41 6,39 6,34L6,11C6,5 8,2 10,2Z';
        dec = `<ellipse cx="11" cy="3" rx="3" ry="2.5" fill="${f}" stroke="${SK}" stroke-width=".5"/>
               <ellipse cx="18" cy="2" rx="3" ry="2.5" fill="${f}" stroke="${SK}" stroke-width=".5"/>
               <ellipse cx="25" cy="3" rx="3" ry="2.5" fill="${f}" stroke="${SK}" stroke-width=".5"/>`;
    } else if (t === 'can') {
        d = 'M8,0L28,0C30,0 32,6 32,15L32,34C32,41 25,45 18,45C11,45 4,41 4,34L4,15C4,6 6,0 8,0Z';
        dec = `<path d="M10,0L18,-4L26,0" fill="${f}" stroke="${SK}" stroke-width=".6" stroke-linejoin="round"/>`;
    } else if (t === 'pre') {
        d = 'M6,1L30,1C32,1 33,4 33,9L33,30C33,37 27,40 18,40C9,40 3,37 3,30L3,9C3,4 4,1 6,1Z';
        dec = `<ellipse cx="12" cy="2" rx="5" ry="3.5" fill="${f}" stroke="${SK}" stroke-width=".5"/>
               <ellipse cx="24" cy="2" rx="5" ry="3.5" fill="${f}" stroke="${SK}" stroke-width=".5"/>
               <line x1="18" y1="5" x2="18" y2="36" stroke="${SK}" stroke-width=".6" opacity=".2" pointer-events="none"/>`;
    } else {
        d = 'M2,0L34,0C36,0 36,4 36,8L36,30C36,36 28,38 18,38C8,38 0,36 0,30L0,8C0,4 0,0 2,0Z';
        dec = `<ellipse cx="8" cy="2" rx="5.5" ry="3.5" fill="${f}" stroke="${SK}" stroke-width=".5"/>
               <ellipse cx="18" cy="1" rx="4" ry="3" fill="${f}" stroke="${SK}" stroke-width=".5"/>
               <ellipse cx="28" cy="2" rx="5.5" ry="3.5" fill="${f}" stroke="${SK}" stroke-width=".5"/>`;
    }
    const h = t === 'can' ? 45 : (t === 'inc' ? 41 : 40);
    const g = window.odontogramaNuevoGestor;
    const click = g ? `class="odon-tz" onclick="odonZoneClick(${n},'V',event)"` : '';
    if (abs) {
        return `<svg viewBox="0 0 36 ${h}" width="36" height="${h}">
            <path d="${d}" fill="none" stroke="#9ca3af" stroke-width=".8" stroke-dasharray="2,2"/>
            ${ex ? `<line x1="10" y1="8" x2="26" y2="${h-6}" stroke="#9ca3af" stroke-width="1.2"/>
                    <line x1="26" y1="8" x2="10" y2="${h-6}" stroke="#9ca3af" stroke-width="1.2"/>` : ''}
        </svg>`;
    }
    return `<svg viewBox="0 0 36 ${h}" width="36" height="${h}">
        ${dec}
        <path d="${d}" fill="${f}" stroke="${SK}" stroke-width="1.1" stroke-linejoin="round" ${click}/>
    </svg>`;
}

function odonSvgOcl(n) {
    const t = odonType(n);
    const hasO = (t === 'pre' || t === 'mS' || t === 'mI');
    const dr = odonDistalRight(n);
    const fV = odonFaceFill(n,'V'), fL = odonFaceFill(n,'L');
    const fM = odonFaceFill(n,'M'), fD = odonFaceFill(n,'D'), fO = odonFaceFill(n,'O');
    const fLft = dr ? fD : fM, fRgt = dr ? fM : fD;
    const lLft = dr ? 'D' : 'M', lRgt = dr ? 'M' : 'D';
    const lb = `font-size="5.5" fill="${SK}" opacity=".45" pointer-events="none" font-family="monospace" text-anchor="middle"`;
    const abs = odonIsAbsent(n);
    const g = window.odontogramaNuevoGestor;

    function poly(face, fill, pts) {
        const cl = g ? `class="odon-tz" onclick="odonZoneClick(${n},'${face}',event)"` : '';
        return `<polygon ${cl} points="${pts}" fill="${fill}" stroke="${SK}" stroke-width=".7"/>`;
    }
    function cRect(face, fill) {
        const cl = g ? `class="odon-tz" onclick="odonZoneClick(${n},'${face}',event)"` : '';
        return `<rect ${cl} x="8" y="8" width="20" height="12" fill="${fill}" stroke="${SK}" stroke-width=".7"/>`;
    }

    let inner = '';
    if (abs) {
        inner = `<rect x="0" y="0" width="36" height="28" fill="none" stroke="#9ca3af" stroke-width=".7" stroke-dasharray="2,2"/>`;
    } else if (hasO) {
        const fiss = t === 'mS'
            ? `<line x1="18" y1="14" x2="18" y2="8" stroke="${SK}" stroke-width=".5" opacity=".3" pointer-events="none"/>
               <line x1="18" y1="14" x2="8" y2="22" stroke="${SK}" stroke-width=".5" opacity=".3" pointer-events="none"/>
               <line x1="18" y1="14" x2="28" y2="22" stroke="${SK}" stroke-width=".5" opacity=".3" pointer-events="none"/>`
            : `<line x1="18" y1="8" x2="18" y2="22" stroke="${SK}" stroke-width=".5" opacity=".3" pointer-events="none"/>
               <line x1="8" y1="14" x2="28" y2="14" stroke="${SK}" stroke-width=".5" opacity=".3" pointer-events="none"/>`;
        inner = `
            ${poly('V',   fV,   '0,0 36,0 28,8 8,8')}
            ${poly(lLft,  fLft, '0,0 8,8 8,20 0,28')}
            ${poly(lRgt,  fRgt, '28,8 36,0 36,28 28,20')}
            ${poly('L',   fL,   '8,20 28,20 36,28 0,28')}
            ${cRect('O',  fO)}
            <text x="18" y="5.5" ${lb}>V</text>
            <text x="18" y="25.5" ${lb}>L</text>
            <text x="3.5" y="15.5" ${lb}>${lLft}</text>
            <text x="32.5" y="15.5" ${lb}>${lRgt}</text>
            <text x="18" y="15.5" ${lb}>O</text>
            ${fiss}`;
    } else {
        const cl4 = g ? `class="odon-tz" onclick="odonZoneClick(${n},'O',event)"` : '';
        inner = `
            ${poly('V',   fV,   '0,0 36,0 28,9 8,9')}
            ${poly(lLft,  fLft, '0,0 8,9 8,19 0,28')}
            ${poly(lRgt,  fRgt, '28,9 36,0 36,28 28,19')}
            ${poly('L',   fL,   '8,19 28,19 36,28 0,28')}
            <rect ${cl4} x="8" y="9" width="20" height="10" fill="${fO}" stroke="${SK}" stroke-width=".6" opacity=".8"/>
            <text x="18" y="5.5" ${lb}>V</text>
            <text x="18" y="26" ${lb}>L</text>
            <text x="3.5" y="15" ${lb}>${lLft}</text>
            <text x="32.5" y="15" ${lb}>${lRgt}</text>
            <text x="18" y="15" ${lb}>I</text>`;
    }
    return `<svg viewBox="0 0 36 28" width="36" height="28">
        ${inner}
        <rect x="0" y="0" width="36" height="28" fill="none" stroke="${SK}" stroke-width=".8" pointer-events="none"/>
    </svg>`;
}

function odonSvgCrownL(n) {
    const t = odonType(n), f = odonFaceFill(n, 'L');
    const abs = odonIsAbsent(n);
    let d = '', dec = '';
    if (t === 'inc') {
        d = 'M11,0L25,0C27,0 28,3 28,8L28,22C28,26 23,28 18,28C13,28 8,26 8,22L8,8C8,3 9,0 11,0Z';
        dec = `<ellipse cx="18" cy="24" rx="5" ry="2.5" fill="${f}" stroke="${SK}" stroke-width=".5" opacity=".5"/>`;
    } else if (t === 'can') {
        d = 'M10,0L26,0C28,0 30,4 30,11L30,26C30,31 24,33 18,33C12,33 6,31 6,26L6,11C6,4 8,0 10,0Z';
        dec = `<ellipse cx="18" cy="29" rx="5" ry="2.5" fill="${f}" stroke="${SK}" stroke-width=".5" opacity=".5"/>`;
    } else if (t === 'pre') {
        d = 'M8,0L28,0C30,0 30,3 30,7L30,22C30,27 24,29 18,29C12,29 6,27 6,22L6,7C6,3 6,0 8,0Z';
    } else {
        d = 'M4,0L32,0C34,0 34,3 34,7L34,22C34,27 27,29 18,29C9,29 2,27 2,22L2,7C2,3 2,0 4,0Z';
    }
    const h = t === 'can' ? 33 : 29;
    const g = window.odontogramaNuevoGestor;
    const click = g ? `class="odon-tz" onclick="odonZoneClick(${n},'L',event)"` : '';
    return `<svg viewBox="0 0 36 ${h}" width="36" height="${h}">
        ${abs
            ? `<path d="${d}" fill="none" stroke="#9ca3af" stroke-width=".7" stroke-dasharray="1.5,2"/>`
            : `${dec}<path d="${d}" fill="${f}" stroke="${SK}" stroke-width=".9" stroke-linejoin="round" ${click}/>`
        }
    </svg>`;
}
```

- [ ] **Step 4.3: Añadir renderTooth y renderBoard**

```javascript
function odonRenderTooth(n, arch) {
    const up = (arch === 'upper');
    const parts = up
        ? [odonSvgRoot(n), odonSvgCej(), odonSvgCrownV(n), odonSvgOcl(n), odonSvgCrownL(n)]
        : [odonSvgCrownL(n), odonSvgOcl(n), odonSvgCrownV(n), odonSvgCej(), odonSvgRoot(n)];
    const fdi = `<div class="odon-nuevo-fdi">${n}</div>`;
    const sel = (odonUI.tooth === n) ? ' odon-sel' : '';
    const click = window.odontogramaNuevoGestor
        ? `onclick="odonSelectTooth(${n},event)"` : '';
    return `<div class="odon-nuevo-tc${sel}" id="odon-tc-${n}" ${click}>
        ${up ? fdi : ''}${parts.join('')}${up ? '' : fdi}
    </div>`;
}

function odonRenderBoard() {
    const upper = ODON_UP.map((n, i) =>
        (i === 8 ? '<div class="odon-nuevo-qsep"></div>' : '') + odonRenderTooth(n, 'upper')
    ).join('');
    const lower = ODON_LO.map((n, i) =>
        (i === 8 ? '<div class="odon-nuevo-qsep"></div>' : '') + odonRenderTooth(n, 'lower')
    ).join('');
    document.getElementById('odon-nuevo-board').innerHTML = `
        <div class="odon-nuevo-arch-lbl">◀ Arcada Superior — Cuadrante 1 · 2 ▶</div>
        <div class="odon-nuevo-row upper">${upper}</div>
        <div class="odon-nuevo-divider"></div>
        <div class="odon-nuevo-row lower">${lower}</div>
        <div class="odon-nuevo-arch-lbl">◀ Arcada Inferior — Cuadrante 4 · 3 ▶</div>`;
}

function odonRedrawTooth(n) {
    const el = document.getElementById(`odon-tc-${n}`);
    if (!el) return;
    const arch = ODON_UP.includes(n) ? 'upper' : 'lower';
    const tmp = document.createElement('div');
    tmp.innerHTML = odonRenderTooth(n, arch);
    el.replaceWith(tmp.firstElementChild);
}
```

- [ ] **Step 4.4: Verificar sintaxis JS**

```bash
node --check public/js/odontograma-nuevo.js
```

Sin output = sin errores de sintaxis.

- [ ] **Step 4.5: Commit**

```bash
git add public/js/odontograma-nuevo.js
git commit -m "feat: JS odontograma-nuevo — constantes, SVG generators, render board"
```

---

## Task 5: Módulo JS — interacción + save

**Files:**
- Modify: `public/js/odontograma-nuevo.js` (añadir al final del fichero)

- [ ] **Step 5.1: Añadir funciones de interacción**

```javascript
function odonSelectTooth(n, e) {
    if (e) e.stopPropagation();
    if (odonUI.tooth && odonUI.tooth !== n) {
        document.getElementById(`odon-tc-${odonUI.tooth}`)?.classList.remove('odon-sel');
    }
    odonUI.tooth = n;
    odonUI.face  = null;
    document.getElementById(`odon-tc-${n}`)?.classList.add('odon-sel');
    odonUpdatePanel(n, null);
}

function odonZoneClick(n, face, e) {
    if (e) e.stopPropagation();
    if (odonUI.tooth && odonUI.tooth !== n) {
        document.getElementById(`odon-tc-${odonUI.tooth}`)?.classList.remove('odon-sel');
    }
    odonUI.tooth = n;
    if (face === 'root') {
        odonSetMode('pieza');
        face = null;
    }
    odonUI.face = face;
    document.getElementById(`odon-tc-${n}`)?.classList.add('odon-sel');
    odonUpdatePanel(n, face);
}

function odonSetMode(m) {
    odonUI.mode = m;
    document.getElementById('odon-btn-cara')?.classList.toggle('odon-mbtn-on', m === 'cara');
    document.getElementById('odon-btn-pieza')?.classList.toggle('odon-mbtn-on', m === 'pieza');
    if (odonUI.tooth) odonUpdatePanel(odonUI.tooth, odonUI.face);
}

function odonUpdatePanel(n, face) {
    const typeLabel = {
        inc:'Incisivo', can:'Canino', pre:'Premolar', mS:'Molar Sup.', mI:'Molar Inf.'
    }[odonType(n)];
    document.getElementById('odon-sel-info').innerHTML =
        `<span class="odon-sel-num">${n}</span><span class="odon-sel-type">${typeLabel}</span>`;
    const mrow = document.getElementById('odon-mrow');
    if (mrow) mrow.style.display = 'flex';
    const chips = document.getElementById('odon-fchips');
    if (chips) {
        chips.innerHTML = ['V','L','M','D','O'].map(f =>
            `<div class="odon-fc${face === f ? ' odon-fc-on' : ''}"
                  onclick="odonZoneClick(${n},'${f}',event)">${f}</div>`
        ).join('');
    }
    const states  = odonUI.mode === 'cara' ? ODON_FS : ODON_PS;
    const current = odonUI.mode === 'cara'
        ? (face ? odonState[n]?.[face] : null)
        : odonState[n]?.pieza;
    const statesEl = document.getElementById('odon-states');
    if (statesEl) {
        statesEl.innerHTML = `<div class="odon-sgrp">
            <div class="odon-sgrp-lbl">${odonUI.mode === 'cara' ? 'Estado de cara' : 'Estado de pieza'}</div>
            ${states.map(s =>
                `<div class="odon-si${s.k === current ? ' odon-si-on' : ''}"
                      onclick="odonApplyState('${s.k}','${face || ''}')">
                    <div class="odon-sdot" style="background:${s.c};border-color:${s.b||'rgba(0,0,0,.2)'}"></div>
                    ${s.l}
                </div>`
            ).join('')}
        </div>`;
    }
    odonUI.face = face;
}

function odonApplyState(stateKey, face) {
    if (!odonUI.tooth) return;
    const n = odonUI.tooth;
    if (odonUI.mode === 'cara' && face) {
        odonState[n][face] = stateKey;
    } else if (odonUI.mode === 'pieza') {
        odonState[n].pieza = stateKey;
    }
    odonRedrawTooth(n);
    document.getElementById(`odon-tc-${n}`)?.classList.add('odon-sel');
    odonUpdatePanel(n, face || odonUI.face);
    odonSaveState(n);
}
```

- [ ] **Step 5.2: Añadir saveState, renderLegend e init**

```javascript
function odonSaveState(n) {
    if (!window.odontogramaNuevoUrl || !window.odontogramaNuevoGestor) return;
    const s = odonState[n];
    fetch(window.odontogramaNuevoUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            num_diente:      n,
            estado_pieza:    s.pieza,
            cara_vestibular: s.V,
            cara_lingual:    s.L,
            cara_mesial:     s.M,
            cara_distal:     s.D,
            cara_oclusal:    s.O,
        }),
    })
    .then(r => {
        if (!r.ok) { console.error('Odontograma save error', r.status); return; }
        const el = document.getElementById(`odon-tc-${n}`);
        if (el) {
            el.classList.add('odon-saved');
            setTimeout(() => el.classList.remove('odon-saved'), 800);
        }
    })
    .catch(err => console.error('Odontograma save error', err));
}

function odonRenderLegend() {
    const all = [...ODON_FS.slice(1), ...ODON_PS.filter(s => s.k !== 'presente')];
    const el = document.getElementById('odon-nuevo-legend');
    if (el) {
        el.innerHTML = all.map(s =>
            `<div class="odon-li">
                <div class="odon-ldot" style="background:${s.c}"></div>
                <span>${s.l}</span>
            </div>`
        ).join('');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (!window.odontogramaNuevoData) return;
    const raw = window.odontogramaNuevoData;
    [...ODON_UP, ...ODON_LO].forEach(n => {
        const d = raw[n] || {};
        odonState[n] = {
            pieza: d.estado_pieza    || 'presente',
            V:     d.cara_vestibular || 'sano',
            L:     d.cara_lingual    || 'sano',
            M:     d.cara_mesial     || 'sano',
            D:     d.cara_distal     || 'sano',
            O:     d.cara_oclusal    || 'sano',
        };
    });
    odonRenderBoard();
    odonRenderLegend();
});
```

- [ ] **Step 5.3: Verificar sintaxis JS**

```bash
node --check public/js/odontograma-nuevo.js
```

Sin output = sin errores.

- [ ] **Step 5.4: Commit**

```bash
git add public/js/odontograma-nuevo.js
git commit -m "feat: JS odontograma-nuevo — interacción panel lateral, save AJAX, leyenda"
```

---

## Task 6: Integración de pestañas en `_odontograma.blade.php`

**Files:**
- Modify: `resources/views/clientes/partials/_odontograma.blade.php`

- [ ] **Step 6.1: Leer el contenido actual del partial**

Abrir `resources/views/clientes/partials/_odontograma.blade.php` y revisar su estructura. Tomar nota de cómo incluye los componentes existentes y qué variables recibe (`$cliente`, `$dentadura`).

- [ ] **Step 6.2: Reemplazar el contenido completo del partial**

```blade
{{-- Tab activo por defecto: nuevo (lámina completa) --}}
<div class="card" style="padding:0;border:none;background:transparent;">

    {{-- Tab buttons --}}
    <div class="odon-tabs">
        <button class="odon-tab odon-tab-active" id="odon-tab-nuevo"
                onclick="odonSwitchTab('nuevo')">
            Lámina completa
        </button>
        <button class="odon-tab" id="odon-tab-oclusal"
                onclick="odonSwitchTab('oclusal')">
            Oclusal
        </button>
        <button class="odon-tab" id="odon-tab-anatomico"
                onclick="odonSwitchTab('anatomico')">
            Anatómico
        </button>
    </div>

    {{-- Panel: lámina completa --}}
    <div id="odon-panel-nuevo">
        <x-cliente.odontograma.nuevo.index
            :cliente="$cliente"
            :dentadura="$dentadura" />
    </div>

    {{-- Panel: oclusal (existente sin cambios) --}}
    <div id="odon-panel-oclusal" style="display:none">
        <x-cliente.odontograma.oclusal.index
            :cliente="$cliente"
            :dentadura="$dentadura" />
    </div>

    {{-- Panel: anatómico (existente sin cambios) --}}
    <div id="odon-panel-anatomico" style="display:none">
        <x-cliente.odontograma.anatomico.index
            :dentadura="$dentadura"
            :modoEdicion="true" />
    </div>

</div>

@push('scripts')
<script>
function odonSwitchTab(tab) {
    ['nuevo','oclusal','anatomico'].forEach(function(t) {
        document.getElementById('odon-panel-' + t).style.display = (t === tab) ? '' : 'none';
        document.getElementById('odon-tab-' + t).classList.toggle('odon-tab-active', t === tab);
    });
}
</script>
@endpush
```

- [ ] **Step 6.3: Limpiar cache de vistas**

```bash
php artisan view:clear
```

- [ ] **Step 6.4: Verificar en el navegador como gestor**

Abrir `http://127.0.0.1:8080/clientes/{cualquier-id}` logueado como `admin@clinicamula.es`.

Verificar:
- [ ] Se ven las 3 pestañas: "Lámina completa", "Oclusal", "Anatómico"
- [ ] "Lámina completa" activa por defecto, muestra el nuevo odontograma con anatomía
- [ ] Click en "Oclusal" → muestra el odontograma oclusal existente
- [ ] Click en "Anatómico" → muestra el odontograma bucal existente
- [ ] Click en zona SVG → panel lateral se actualiza con cara seleccionada
- [ ] Click en estado en panel → diente cambia de color inmediatamente
- [ ] Borde verde aparece ~800ms en el diente guardado
- [ ] No hay errores en la consola del navegador (`F12 > Console`)
- [ ] El odontograma es horizontalmente scrollable en pantallas pequeñas

- [ ] **Step 6.5: Commit**

```bash
git add resources/views/clientes/partials/_odontograma.blade.php
git commit -m "feat: tab switcher con tres odontogramas en ficha del paciente"
```

---

## Task 7: Test de integración del endpoint con estados nuevos

**Files:**
- Create: `tests/Feature/DentaduraEndpointEstadosTest.php`

> **Nota:** Este test usa `User::factory()`. Si el proyecto no tiene `Cliente::factory()` ni `Dentadura::factory()`, usar creación directa: `Cliente::create([...])` y `Dentadura::create([...])`.

- [ ] **Step 7.1: Crear el test**

Crear `tests/Feature/DentaduraEndpointEstadosTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Dentadura;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DentaduraEndpointEstadosTest extends TestCase
{
    use RefreshDatabase;

    private User $gestor;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gestor  = User::factory()->create(['role' => 'gestor']);
        $this->cliente = Cliente::create([
            'nombre'    => 'Test',
            'apellidos' => 'Paciente',
            'user_id'   => null,
        ]);
        Dentadura::create([
            'cliente_id'   => $this->cliente->id,
            'num_diente'   => '16',
            'estado_pieza' => 'presente',
        ]);
    }

    /** @test */
    public function gestor_puede_guardar_estados_de_cara_nuevos(): void
    {
        $response = $this->actingAs($this->gestor)
            ->postJson(route('clientes.dentadura', $this->cliente), [
                'num_diente'      => 16,
                'estado_pieza'    => 'endodoncia',
                'cara_vestibular' => 'caries_det',
                'cara_lingual'    => 'composite',
                'cara_mesial'     => 'sano',
                'cara_distal'     => 'sano',
                'cara_oclusal'    => 'amalgama',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('dentadura', [
            'cliente_id'      => $this->cliente->id,
            'num_diente'      => '16',
            'estado_pieza'    => 'endodoncia',
            'cara_vestibular' => 'caries_det',
            'cara_lingual'    => 'composite',
            'cara_oclusal'    => 'amalgama',
        ]);
    }

    /** @test */
    public function gestor_puede_guardar_todos_los_estados_pieza_nuevos(): void
    {
        $nuevos = [
            'no_erupcionado','extrac_indicada','extraido','temporal',
            'pulpitis','necrosis','apicectomia','incluido','supernumerario',
            'movilidad_1','movilidad_2','movilidad_3','carilla','pilar_puente',
            'pontico','prot_removible','giroversion','migracion','diastema',
            'fluorosis','agenesia',
        ];
        foreach ($nuevos as $estado) {
            $r = $this->actingAs($this->gestor)
                ->postJson(route('clientes.dentadura', $this->cliente), [
                    'num_diente'   => 16,
                    'estado_pieza' => $estado,
                ]);
            $r->assertOk("Falló para estado_pieza: {$estado}");
        }
    }

    /** @test */
    public function cliente_no_puede_guardar_dentadura(): void
    {
        $userCliente = User::factory()->create(['role' => 'cliente']);
        $r = $this->actingAs($userCliente)
            ->postJson(route('clientes.dentadura', $this->cliente), [
                'num_diente'   => 16,
                'estado_pieza' => 'caries_det',
            ]);
        $r->assertForbidden();
    }
}
```

- [ ] **Step 7.2: Ejecutar el test**

```bash
php vendor/bin/phpunit tests/Feature/DentaduraEndpointEstadosTest.php --testdox
```

Esperado: 3 tests en verde. Si falla por `num_filiacion` obligatorio en Cliente, añadir `'num_filiacion' => 'MUL-99999'` al create.

- [ ] **Step 7.3: Ejecutar suite completa**

```bash
php vendor/bin/phpunit --testdox
```

Esperado: todos los tests en verde.

- [ ] **Step 7.4: Commit**

```bash
git add tests/Feature/DentaduraEndpointEstadosTest.php
git commit -m "test: endpoint dentadura acepta todos los estados nuevos de pieza y cara"
```

---

## Task 8: Merge a develop

- [ ] **Step 8.1: Limpiar cachés**

```bash
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

- [ ] **Step 8.2: Suite completa de tests**

```bash
php vendor/bin/phpunit --testdox
```

Esperado: verde.

- [ ] **Step 8.3: Merge a develop**

```bash
git checkout develop
git merge --no-ff feature/odontograma-nuevo -m "feat: odontograma lámina anatómica interactiva (29 estados, 4 vistas, 3 pestañas)"
git push origin develop
```

- [ ] **Step 8.4: Eliminar la rama de feature**

```bash
git branch -d feature/odontograma-nuevo
git push origin --delete feature/odontograma-nuevo
```
