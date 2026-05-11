# Odontograma Interactivo — Lámina Anatómica Clásica

**Fecha:** 2026-05-11  
**Branch objetivo:** `feature/odontograma-nuevo`  
**Base:** `develop`

---

## Objetivo

Crear un nuevo odontograma interactivo con estilo de lámina anatómica clínica que convive con los dos existentes (oclusal y anatómico-bucal) en la ficha del paciente. El nuevo componente ofrece 4 vistas apiladas por diente, 29 estados clínicos y 5 zonas clicables, reemplazando la interacción modal por un panel lateral inline.

---

## Contexto del proyecto

- Laravel 11, PHP 8.3, Blade, MySQL 8.4
- App en `http://127.0.0.1:8080` vía Laragon
- Odontogramas existentes a mantener sin modificar:
  - `x-cliente.odontograma.oclusal.index` — vista oclusal (top-down, 5 caras)
  - `x-cliente.odontograma.anatomico.index` — vista bucal frontal (corona + raíz)
- Endpoint existente de save: `POST /clientes/{cliente}/dentadura` (JSON)
- Permisos: solo `gestor` puede editar

---

## Arquitectura

### Ficheros nuevos

| Fichero | Propósito |
|---------|-----------|
| `resources/views/components/cliente/odontograma/nuevo/index.blade.php` | Componente Blade — emite JSON a `window.*`, carga CSS y JS |
| `public/js/odontograma-nuevo.js` | Módulo JS — renderiza SVG, interacción, save AJAX |
| Estilos en `public/css/app.css` | Clases `.odon-nuevo-*` añadidas al final |

### Ficheros modificados

| Fichero | Cambio |
|---------|--------|
| `app/Models/Dentadura.php` | `ESTADOS_PIEZA` 6→26, `ESTADOS_CARA` 6→11 |
| `resources/views/clientes/partials/_odontograma.blade.php` | Añade pestaña "Lámina completa" |

### Ficheros sin tocar

- `oclusal/index.blade.php`, `oclusal/diente.blade.php`
- `anatomico/index.blade.php`, `anatomico/diente.blade.php`
- `ClienteController`, `DentaduraService`, rutas, API Android

---

## Visual — Anatomía por diente (4 vistas apiladas)

Para **arcada superior** (top → bottom):

```
[FDI número en caja monospace]
[SVG Raíces — anatomía por tipo]
[Línea CEJ (unión esmalte-cemento)]
[SVG Corona vestibular — exterior]
[SVG Oclusal/Incisal — 5 zonas clickables]
[SVG Corona lingual — interior]
```

Para **arcada inferior**: orden invertido (raíz abajo, oclusal arriba hacia el centro).

### Anatomía por tipo de diente

| Tipo | Raíces | Corona vestibular | Oclusal | Detalles |
|------|--------|-------------------|---------|----------|
| Incisivo (11,12,21,22,31,32,41,42) | 1 | Blade rectangular | Vista incisal (I) | 3 mamelones en borde incisal |
| Canino (13,23,33,43) | 1 (la más larga) | Cúspide puntiaguda | Vista incisal (I) | Cíngulo en lingual |
| Premolar (14,15,24,25,34,35,44,45) | 2 | 2 cúspides V+L | Oclusal (O) | Surco vertical |
| Molar superior (16,17,18,26,27,28) | 3 (2 vestibulares + 1 palatino) | 4 cúspides | Oclusal (O) | Fisura en Y |
| Molar inferior (36,37,38,46,47,48) | 2 (mesial + distal) | 4 cúspides | Oclusal (O) | Fisura en cruz |

### Estilo visual

- Fondo: crema/pergamino `#faf6ee`, grid sutil `rgba(140,120,80,.13)`
- Fill diente sano: `#faf5ea` (marfil)
- Fill raíz: `#e8d4a8` (dentina)
- Trazos: `#2a1a08` (tinta oscura), raíz `#8b5830`
- Línea CEJ: trazo marrón más grueso
- Hover: `filter: brightness(.72) saturate(1.4)`
- Diente seleccionado: `outline` azul tenue

---

## Interacción

### Modos de edición

| Modo | Activación | Qué edita |
|------|-----------|-----------|
| **Por cara** | Click en zona V/L/M/D/O de la vista oclusal, o en corona V/L | Estado de esa superficie específica |
| **Pieza entera** | Click en la raíz SVG | Estado global del diente |

El panel derecho muestra los estados disponibles según el modo activo. Un chip por cada cara (V, L, M, D, O/I) permite cambiar de cara sin volver al SVG.

### Flujo de click

```
Click zona SVG
  ├── zona = 'root'   → setMode('pieza') + updatePanel
  └── zona = V/L/M/D/O → setMode('cara') + selectedFace = zona + updatePanel

Click estado en panel
  └── applyState(key, face)
        ├── state[n][face] = key   (modo cara)
        ├── state[n].pieza = key   (modo pieza)
        ├── redrawTooth(n)         (solo ese diente)
        └── saveState(n)           (AJAX POST)
```

### Save AJAX

```javascript
POST /clientes/{id}/dentadura
Content-Type: application/json
{
  "num_diente": 16,
  "estado_pieza": "corona",
  "cara_vestibular": "composite",
  "cara_lingual": "sano",
  "cara_mesial": "caries",
  "cara_distal": "sano",
  "cara_oclusal": "sano"
}
```
Mismo payload que usa `odontograma.js` actualmente. Sin cambios en el controller.

Feedback visual: el diente guardado muestra un borde verde `#059669` durante 800ms.

---

## Estados clínicos

### Por cara (11)

| Key | Label | Color |
|-----|-------|-------|
| `sano` | Sano | `#faf5ea` |
| `caries` | Caries | `#dc2626` |
| `caries_det` | Caries detenida | `#f59e0b` |
| `composite` | Composite | `#3b82f6` |
| `amalgama` | Amalgama | `#64748b` |
| `sellador` | Sellador | `#8b5cf6` |
| `erosion` | Erosión | `#d97706` |
| `fractura` | Fractura | `#991b1b` |
| `tincion` | Tinción | `#92400e` |
| `fisura` | Fisura | `#374151` |
| `reconstruccion` | Reconstrucción | `#059669` |

### Por pieza (26)

| Key | Label | Color |
|-----|-------|-------|
| `presente` | Presente | `#faf5ea` |
| `ausente` | Ausente | `#d1d5db` |
| `no_erupcionado` | No erupcionado | `#fef3c7` |
| `extrac_indicada` | Extracción indicada | `#fca5a5` |
| `extraido` | Extraído | `#9ca3af` |
| `temporal` | Temporal (deciduo) | `#f9a8d4` |
| `implante` | Implante | `#1d4ed8` |
| `corona` | Corona | `#b45309` |
| `puente` | Puente *(legacy — conservado)* | `#3b82f6` |
| `endodoncia` | Endodoncia | `#dc2626` |
| `pulpitis` | Pulpitis | `#f97316` |
| `necrosis` | Necrosis pulpar | `#1f2937` |
| `apicectomia` | Apicectomía | `#0f766e` |
| `incluido` | Incluido/Retenido | `#7c3aed` |
| `supernumerario` | Supernumerario | `#db2777` |
| `movilidad_1` | Movilidad Grado I | `#facc15` |
| `movilidad_2` | Movilidad Grado II | `#f97316` |
| `movilidad_3` | Movilidad Grado III | `#dc2626` |
| `carilla` | Carilla | `#93c5fd` |
| `pilar_puente` | Pilar de puente | `#a78bfa` |
| `pontico` | Póntico de puente | `#c4b5fd` |
| `prot_removible` | Prótesis removible | `#fb923c` |
| `giroversion` | Giroversión | `#84cc16` |
| `migracion` | Migración | `#22d3ee` |
| `diastema` | Diastema | `#e879f9` |
| `fluorosis` | Fluorosis | `#a3e635` |
| `agenesia` | Agenesia | `#94a3b8` |

**Compatibilidad:** las keys existentes (`presente`, `ausente`, `corona`, `puente`, `implante`, `endodoncia`, `sano`, `caries`, `obturacion`, `fractura`, `sellante`, `desgaste`) no cambian. Los nuevos son adiciones. La API Android solo conoce los 6 originales y sigue funcionando.

---

## Modelo Dentadura.php — cambios

```php
// ESTADOS_PIEZA: añadir al array existente
'no_erupcionado'  => ['label' => 'No erupcionado',      'color' => '#fef3c7', 'icono' => '?'],
'extrac_indicada' => ['label' => 'Extracción indicada', 'color' => '#fca5a5', 'icono' => '!'],
'extraido'        => ['label' => 'Extraído',            'color' => '#9ca3af', 'icono' => '✕'],
// ... (26 total)

// ESTADOS_CARA: añadir al array existente
'caries_det'      => ['label' => 'Caries detenida',    'color' => '#f59e0b'],
'composite'       => ['label' => 'Composite',           'color' => '#3b82f6'],
// ... (11 total)
```

No se elimina ninguna key existente. Se verifica que las columnas `estado_pieza` y `cara_*` en MySQL sean `VARCHAR` (no ENUM) para admitir los nuevos valores sin migración de esquema.

---

## Integración en `_odontograma.blade.php`

```blade
{{-- Tab switcher --}}
<div class="odon-tabs">
    <button class="odon-tab" onclick="switchTab('nuevo')" id="tab-nuevo">Lámina completa</button>
    <button class="odon-tab" onclick="switchTab('oclusal')" id="tab-oclusal">Oclusal</button>
    <button class="odon-tab" onclick="switchTab('anatomico')" id="tab-anatomico">Anatómico</button>
</div>

<div id="panel-nuevo">   <x-cliente.odontograma.nuevo.index ... />   </div>
<div id="panel-oclusal" style="display:none">   <x-cliente.odontograma.oclusal.index ... />   </div>
<div id="panel-anatomico" style="display:none"> <x-cliente.odontograma.anatomico.index ... /> </div>
```

La pestaña activa por defecto es "Lámina completa".

---

## Módulo JS — firma de funciones

```javascript
// Constantes
const FACE_STATES, PIECE_STATES, FDI_UPPER, FDI_LOWER

// Estado local
let state = {}          // copia mutable de odontogramaNuevoData
let ui = { tooth, face, mode }

// SVG
function svgRoot(n)     → string HTML
function svgCEJ()       → string HTML
function svgCrownV(n)   → string HTML  (incluye decor: mamelones/cúspides)
function svgOcl(n)      → string HTML  (5 polígonos clickables + labels M/D/V/L/O)
function svgCrownL(n)   → string HTML

// Render
function renderBoard()
function renderTooth(n, arch)  → string HTML
function redrawTooth(n)        → reemplaza el elemento DOM

// Interacción
function zoneClick(n, face, e)
function setMode(m)
function applyState(key, face)
function updatePanel(n, face)

// Persistencia
function saveState(n)   → fetch POST, feedback visual 800ms
function renderLegend() → genera leyenda de colores

// Init (DOMContentLoaded)
```

---

## Restricciones

- Solo gestor puede editar: el componente Blade comprueba `auth()->user()->isGestor()` antes de emitir `window.odontogramaNuevoUrl`. Sin esa variable, el JS renderiza en modo lectura (sin `onclick` en zonas).
- El JS no asume nada sobre el estado inicial — lee siempre de `window.odontogramaNuevoData`.
- Los SVG generados son `pointer-events: none` en elementos decorativos (surcos, mamelones) para no interferir con los clicks de zona.

---

## Fuera de scope

- Exportar a PDF / imprimir
- Historial de cambios por diente
- Anotaciones de texto libre por diente
- API Android con nuevos estados (los 6 originales siguen sin cambios)
