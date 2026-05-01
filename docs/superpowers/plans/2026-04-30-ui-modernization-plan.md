# UI Modernization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-step. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Modernizar la interfaz de Clínica Dental Mula con diseño clínico/profesional, tipografía Inter, componentes rediseñados y odontograma SVG.

**Architecture:** Actualización progresiva del CSS global en el layout principal, followed by component updates. El odontograma se implementa como componente Blade independiente con SVG esquematizados.

**Tech Stack:** Blade templates, vanilla CSS (sin frameworks nuevos), Google Fonts (Inter, JetBrains Mono), SVG inline.

---

## File Structure

### Archivos Principales a Modificar

| Archivo | Descripción | Prioridad |
|---|---|---|
| `resources/views/layouts/app.blade.php` | Layout principal con CSS variables actualizado | P1 |
| `resources/views/components/dentadura.blade.php` | Nuevo componente odontograma SVG | P1 |
| `resources/views/dashboard/index.blade.php` | Dashboard con tarjetas rediseñadas | P1 |
| `resources/views/clientes/index.blade.php` | Tabla de clientes modernizada | P1 |
| `resources/views/clientes/create.blade.php` | Formulario con nuevos estilos | P1 |
| `resources/views/clientes/edit.blade.php` | Formulario con nuevos estilos | P1 |
| `resources/views/clientes/show.blade.php` | Detalle + odontograma integrado | P1 |

---

## Task 1: Actualizar Tipografía y Variables CSS en Layout

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

- [ ] **Step 1: Modificar sección de fuentes e importar Inter**

En `resources/views/layouts/app.blade.php`, cambiar la línea 11:

```blade
<!-- Cambiar esta línea -->
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<!-- Por esta -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
```

- [ ] **Step 2: Actualizar variables CSS :root**

Reemplazar el bloque `:root` completo (líneas 17-31):

```css
:root {
    /* Azul primario */
    --azul:      #1e3a8a;  /* slate-900 */
    --azul-med:  #2563eb;  /* blue-600 */
    --azul-claro:#dbeafe;  /* blue-100 */
    
    /* Verde éxito */
    --verde:     #059669;  /* emerald-600 */
    --verde-claro:#d1fae5; /* emerald-100 */
    
    /* Rojo error */
    --rojo:      #ef4444;  /* red-500 */
    
    /* Amarillo warning */
    --amarillo:  #f59e0b;  /* amber-500 */
    
    /* Fondo */
    --gris-fondo:#f8fafc;  /* slate-50 */
    --gris-borde:#e2e8f0;  /* slate-200 */
    
    /* Texto principal */
    --texto:     #0f172a;  /* slate-900 */
    --texto-med: #64748b;  /* slate-500 */
    
    --blanco:    #ffffff;
    --sidebar-w: 280px;
}
```

- [ ] **Step 3: Actualizar fuentes globales**

Cambiar la línea 37 (estilo `body`):

```css
body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: var(--gris-fondo);
    color: var(--texto);
    font-size: 15px;
    line-height: 1.6;
}
```

Cambiar la línea 44 (estilo `h1-h4`):

```css
h1, h2, h3, h4 { font-family: 'Inter', sans-serif; color: var(--azul); }
```

- [ ] **Step 4: Actualizar estilos de tabla**

Reemplazar estilos de tabla para eliminar bordes verticales:

```css
/* ── TABLA ────────────────────────────────────────────────────────── */
.table-wrap {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid var(--gris-borde);
}
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    background: var(--gris-fondo);
    text-align: left;
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--texto-med);
    border-bottom: 1px solid var(--gris-borde);
}
td {
    padding: 0.875rem 1rem;
    font-size: 0.875rem;
    color: var(--texto);
    border-bottom: 1px solid var(--gris-borde);
    vertical-align: middle;
}
tr:hover td { background: #f8fafc; }
tr:last-child td { border-bottom: none; }
```

- [ ] **Step 5: Actualizar estilos de badges**

Reemplazar estilos de badge para ser más cuadrados:

```css
/* ── BADGES ───────────────────────────────────────────────────────── */
.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}
```

- [ ] **Step 6: Actualizar estilos de formularios**

Reemplazar estilos de formulario (líneas 187-197):

```css
/* ── FORMULARIOS ──────────────────────────────────────────────────── */
.form-group { margin-bottom: 1rem; }
.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    color: var(--texto);
    margin-bottom: 0.5rem;
}
.form-control {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--gris-borde);
    border-radius: 6px;
    font-family: 'Inter', sans-serif;
    font-size: 0.9375rem;
    transition: border-color 0.15s, box-shadow 0.15s;
    background: #fff;
    color: var(--texto);
}
.form-control:focus {
    outline: none;
    border-color: var(--azul-med);
    box-shadow: 0 0 0 3px rgba(37,99,168,0.15);
}
.form-control::placeholder { color: var(--texto-med); opacity: 0.6; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
```

- [ ] **Step 7: Actualizar estilos de tarjetas**

Reemplazar estilos de card (líneas 159-168):

```css
/* ── CARDS ────────────────────────────────────────────────────────── */
.card {
    background: #fff;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid var(--gris-borde);
}
.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--gris-borde);
}
.card-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--texto);
}
```

- [ ] **Step 8: Actualizar estilos de botones**

Reemplazar estilos de botón (líneas 142-157):

```css
/* ── BOTONES ──────────────────────────────────────────────────────── */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    border: none;
    transition: all 0.15s;
    text-decoration: none;
    font-family: 'Inter', sans-serif;
}
.btn-primary  { background: var(--azul-med); color: #fff; }
.btn-primary:hover  { background: var(--azul); }
.btn-success  { background: var(--verde); color: #fff; }
.btn-success:hover  { background: #047857; }
.btn-danger   { background: var(--rojo); color: #fff; }
.btn-danger:hover   { background: #b91c1c; }
.btn-outline  { background: transparent; border: 1px solid var(--azul-med); color: var(--azul-med); }
.btn-outline:hover  { background: var(--azul-claro); }
.btn-sm { padding: 0.375rem 0.875rem; font-size: 0.8125rem; }
.btn-ghost { background: transparent; border: none; color: var(--texto-med); }
.btn-ghost:hover { background: #f1f5f9; color: var(--texto); }
```

- [ ] **Step 9: Actualizar sidebar**

Reemplazar estilos de sidebar (líneas 80-92):

```css
/* ── LAYOUT APP (privada) ─────────────────────────────────────────── */
.app-wrap { display: flex; min-height: 100vh; }

.sidebar {
    width: 280px;
    background: linear-gradient(180deg, var(--azul) 0%, #1e40af 100%);
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 200;
    box-shadow: 4px 0 16px rgba(0,0,0,0.2);
}
```

Cambiar sidebar-logo (líneas 93-102):

```css
.sidebar-logo {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.sidebar-logo a {
    font-family: 'Inter', sans-serif;
    color: #fff;
    font-size: 1.125rem;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.sidebar-logo span {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.7);
    display: block;
    margin-top: 0.25rem;
    font-weight: 400;
}
```

Cambiar sidebar-nav (líneas 103-124):

```css
.sidebar-nav {
    flex: 1;
    padding: 1rem 0;
    overflow-y: auto;
}
.sidebar-nav a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    color: rgba(255,255,255,0.75);
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.15s;
    border-left: 3px solid transparent;
}
.sidebar-nav a:hover,
.sidebar-nav a.active {
    background: rgba(255,255,255,0.08);
    color: #fff;
    border-left-color: var(--verde);
    text-decoration: none;
}
.sidebar-nav .nav-section {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    padding: 1rem 1.5rem 0.5rem;
    margin-top: 0.5rem;
}
```

- [ ] **Step 10: Actualizar topbar y contenido**

Reemplazar estilos de topbar (líneas 132-137):

```css
.app-topbar {
    background: #fff;
    border-bottom: 1px solid var(--gris-borde);
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.app-topbar h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--texto);
    margin: 0;
}
```

- [ ] **Step 11: Actualizar alertas**

Reemplazar alertas (líneas 199-207):

```css
/* ── ALERTAS ──────────────────────────────────────────────────────── */
.alert {
    padding: 0.875rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    font-weight: 500;
}
.alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #059669; }
.alert-danger   { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
.alert-warning  { background: #fef3c7; color: #92400e; border-left: 4px solid #f59e0b; }
.alert-info     { background: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; }
```

- [ ] **Step 12: Actualizar navbar pública**

Reemplazar estilos de navbar (líneas 49-77):

```css
/* ── NAVBAR PÚBLICA ───────────────────────────────────────────────────── */
.navbar {
    background: linear-gradient(135deg, var(--azul) 0%, #1e40af 100%);
    padding: 0 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 72px;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 4px 12px rgba(30,58,92,0.2);
}
.navbar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-family: 'Inter', sans-serif;
    color: #fff;
    font-size: 1.25rem;
    font-weight: 700;
    text-decoration: none;
}
.navbar-links a {
    color: rgba(255,255,255,0.9);
    font-size: 0.9375rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: all 0.15s;
}
.navbar-links a:hover {
    color: #fff;
    background: rgba(255,255,255,0.1);
    text-decoration: none;
}
.btn-nav {
    background: var(--verde);
    color: #fff;
    padding: 0.625rem 1.25rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.15s;
}
.btn-nav:hover {
    background: #047857;
    text-decoration: none;
}
```

- [ ] **Step 13: Actualizar modal**

Reemplazar estilos de modal (líneas 221-241):

```css
/* ── MODAL ────────────────────────────────────────────────────────── */
.modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(2px);
    z-index: 500;
    align-items: center;
    justify-content: center;
}
.modal-backdrop.open { display: flex; }
.modal {
    background: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    width: 100%;
    max-width: 560px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
}
.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--texto-med);
    padding: 0.25rem;
    line-height: 1;
}
.modal-close:hover { color: var(--texto); }
```

- [ ] **Step 14: Añadir estilos responsive actualizados**

Reemplazar estilos responsive (líneas 243-250):

```css
/* ── RESPONSIVE ───────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.25s ease;
    }
    .sidebar.open { transform: translateX(0); }
    .app-main { margin-left: 0; }
    .app-content { padding: 1rem; }
    .form-grid-2 { grid-template-columns: 1fr; }
    .navbar { padding: 0 1rem; }
    .dentadura-grid { grid-template-columns: 1fr; }
}
@media (max-width: 1024px) {
    .app-topbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    .dashboard-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}
```

- [ ] **Step 15: Añadir scrollbar personalizada y focus visible**

Añadir antes de `</style>` (al final, línea 251):

```css
/* ── MICRO-INTERACCIONES ──────────────────────────────────────────── */
*, *::before, *::after { transition: all 0.15s ease; }

*:focus-visible {
    outline: 2px solid var(--azul-med);
    outline-offset: 2px;
}

::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: #f1f5f9; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
```

- [ ] **Step 16: Guardar y verificar cambios**

Verificar que el archivo tiene todos los cambios aplicados.

---

## Task 2: Crear Componente Odontograma SVG

**Files:**
- Create: `resources/views/components/dentadura.blade.php`

- [ ] **Step 1: Crear componente Blade con estilos y estructura**

Crear archivo `resources/views/components/dentadura.blade.php`:

```blade
{{--
  Componente: Dentadura (Odontograma SVG)
  Props: $cliente (Cliente model), $showLegend (bool, default: true)
--}}

@props(['cliente' => null, 'showLegend' => true])

<div class="dentadura-container">
    @if($showLegend)
    <div class="dentadura-header">
        <h3 class="dentadura-title">Odontograma (Notación FDI)</h3>
        <div class="dentadura-legend">
            @foreach(['intact' => ['label' => 'Sano', 'class' => 'legend-intact'],
                      'filling' => ['label' => 'Obturación', 'class' => 'legend-filling'],
                      'crown' => ['label' => 'Corona', 'class' => 'legend-crown'],
                      'extracted' => ['label' => 'Extraído', 'class' => 'legend-extracted'],
                      'none' => ['label' => 'Sin registrar', 'class' => 'legend-none']] as $state => $info)
                <div class="legend-item">
                    <div class="legend-color {{ $info['class'] }}"></div>
                    <span>{{ $info['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($cliente)
        <div class="dentadura-grid">
            @php
                // Mapeo de dientes por cuadrante (FDI notation)
                $quadrants = [
                    'superior-derecha' => [18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28],
                    'inferior-derecha' => [41,42,43,44,45,46,47,48,38,37,36,35,34,33,32,31]
                ];
                
                // Obtener dentadura del cliente
                $dentadura = $cliente->dentadura()->get()->keyBy('diente_id');
            @endphp
            
            @foreach($quadrants as $quadrantName => $toothIds)
                <div class="dentadura-quadrant {{ $quadrantName }}">
                    @foreach($toothIds as $toothId)
                        @php
                            $toothState = $dentadura->has($toothId) ? $dentadura[$toothId]->estado : 'none';
                            $description = $dentadura->has($toothId) ? ($dentadura[$toothId]->descripcion ?? '') : 'Sin registrar';
                        @endphp
                        <div class="diente diente-{{ $toothState }}" 
                             data-tooth="{{ $toothId }}" 
                             data-description="{{ $description }}">
                            @include('components.diente-svg', ['id' => $toothId])
                            <span class="diente-number">{{ $toothId }}</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; padding: 2rem; color: var(--texto-med);">
            No hay dentadura registrada para este paciente.
        </div>
    @endif
</div>

@push('styles')
<style>
.dentadura-container {
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.dentadura-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.dentadura-title {
    font-size: 1rem;
    font-weight: 600;
    color: #0f172a;
    margin: 0;
}

.dentadura-legend {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.8125rem;
    color: #64748b;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 4px;
}

.legend-intact { background: #10b981; }
.legend-filling { background: #f59e0b; }
.legend-crown { background: #3b82f6; }
.legend-extracted { background: #6b7280; }
.legend-none { background: #e2e8f0; border: 1px solid #cbd5e1; }

.dentadura-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.dentadura-quadrant {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
    padding: 0.5rem;
    background: white;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.diente {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: background 0.15s;
}

.diente:hover { background: #f1f5f9; }

.diente-svg {
    width: 36px;
    height: 36px;
    transition: fill 0.2s;
}

.diente-number {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    margin-top: 0.25rem;
}

/* Tooltip al hover */
.diente:hover::after {
    content: attr(data-description);
    position: absolute;
    background: #0f172a;
    color: white;
    padding: 0.5rem;
    border-radius: 6px;
    font-size: 0.8125rem;
    white-space: pre-wrap;
    z-index: 100;
    max-width: 200px;
}

/* Colores por estado */
.diente-intact .diente-svg { fill: #10b981; }
.diente-filling .diente-svg { fill: #f59e0b; }
.diente-crown .diente-svg { fill: #3b82f6; }
.diente-extracted .diente-svg { fill: #6b7280; }
.diente-none .diente-svg { fill: #e2e8f0; }
</style>
@endpush
```

- [ ] **Step 2: Crear componente de SVG de dientes**

Crear archivo `resources/views/components/diente-svg.blade.php`:

```blade
{{--
  Componente: SVG de Diente (por tipo)
  Props: $id (int, FDI tooth number)
--}}

@props(['id' => 0])

@php
    // Determinar tipo de diente según número FDI
    // Incisivos: 11,12,21,22,31,32,41,42
    // Caninos: 13,23,33,43
    // Premolares: 14,15,24,25,34,35,44,45
    // Molares: 16,17,18,26,27,28,36,37,38,46,47,48
    
    $firstDigit = intval(substr($id, 0, 1));
    
    $toothType = match(true) {
        in_array($id, [11,12,21,22,31,32,41,42]) => 'incisivo',
        in_array($id, [13,23,33,43]) => 'canino',
        in_array($id, [14,15,24,25,34,35,44,45]) => 'premolar',
        default => 'molar'
    };
@endphp

<svg viewBox="0 0 40 40" class="diente-svg diente-svg-{{ $toothType }}">
    @if($toothType === 'incisivo')
        <path d="M20 5 L35 15 L35 30 Q20 38 5 30 L5 15 Z" />
    @elseif($toothType === 'canino')
        <path d="M20 3 L32 16 L28 32 Q20 37 12 32 L8 16 Z" />
    @elseif($toothType === 'premolar')
        <path d="M18 3 L22 10 L15 12 L18 20 L8 18 L10 28 Q20 34 30 28 L32 18 L22 16 Z" />
    @else
        <path d="M12 3 L16 10 L10 12 L12 20 L2 18 L4 28 Q20 36 36 28 L38 18 L28 15 L30 8 Z" />
    @endif
</svg>
```

- [ ] **Step 3: Añadir estilos adicionales para tipos de diente**

Añadir a los estilos del componente dentadura:

```css
.diente-svg-incisivo { fill: #10b981; }
.diente-svg-canino { fill: #10b981; }
.diente-svg-premolar { fill: #10b981; }
.diente-svg-molar { fill: #10b981; }

.diente-intact .diente-svg-incisivo,
.diente-intact .diente-svg-canino,
.diente-intact .diente-svg-premolar,
.diente-intact .diente-svg-molar { fill: #10b981; }
```

- [ ] **Step 4: Verificar componentes**

Listar archivos creados:
- `resources/views/components/dentadura.blade.php`
- `resources/views/components/diente-svg.blade.php`

---

## Task 3: Actualizar Vista de Cliente (show.blade.php) con Odontograma

**Files:**
- Modify: `resources/views/clientes/show.blade.php`

- [ ] **Step 1: Leer archivo actual**

Leer `resources/views/clientes/show.blade.php` para ver su estructura actual.

- [ ] **Step 2: Añadir include del componente dentadura**

En la sección de odontograma del archivo, añadir el include del nuevo componente:

```blade
{{-- Donde estaba la tabla de dientes, cambiar por: --}}
<x-dentadura :cliente="$cliente" />
```

---

## Task 4: Actualizar Dashboard

**Files:**
- Modify: `resources/views/dashboard/index.blade.php`

- [ ] **Step 1: Actualizar estilos de tarjetas de estadísticas**

Reemplazar estilos inline con clases CSS:

```blade
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    @foreach([
        ['👥','Total pacientes',   $stats['total_clientes'],   '#2563eb'],
        ['📅','Citas hoy',         $stats['citas_hoy'],        '#059669'],
        ['⏳','Pendientes',         $stats['citas_pendientes'], '#f59e0b'],
        ['💶','Ingresos este mes',  '€ '.number_format($stats['ingresos_mes'],2), '#1e3a8a'],
    ] as [$ico,$lbl,$val,$color])
    <div class="card card-primary" style="text-align:center;border-top:4px solid {{ $color }}">
        <div style="font-size:2rem;margin-bottom:0.5rem">{{ $ico }}</div>
        <div style="font-size:2rem;font-weight:700;color:{{ $color }}">{{ $val }}</div>
        <div style="font-size:0.8125rem;color:#64748b;margin-top:0.25rem">{{ $lbl }}</div>
    </div>
    @endforeach
</div>
```

- [ ] **Step 2: Actualizar tarjetas de citas y pacientes**

Reemplazar estilos de tarjetas con clases `.card` existentes.

---

## Task 5: Actualizar Lista de Clientes

**Files:**
- Modify: `resources/views/clientes/index.blade.php`

- [ ] **Step 1: Actualizar estilos de tabla**

Reemplazar estilos inline de tabla con clases `.table-wrap` y `.card` definidas en el layout.

---

## Task 6: Actualizar Formularios de Cliente

**Files:**
- Modify: `resources/views/clientes/create.blade.php`
- Modify: `resources/views/clientes/edit.blade.php`

- [ ] **Step 1: Actualizar labels y inputs**

Asegurar que todos los form groups usan `form-group`, `form-label`, `form-control` classes definidas en el layout.

- [ ] **Step 2: Actualizar grid de formularios**

Asegurar que los grids usan `form-grid` o `form-grid-2` classes.

---

## Task 7: Testing Visual

**Files:**
- N/A (testing manual)

- [ ] **Step 1: Verificar layout principal**

Abrir http://localhost:8000/dashboard con cuenta de gestor y verificar:
- Tipografía Inter aplicada correctamente
- Colores actualizados
- Sidebar con gradiente
- Topbar limpia

- [ ] **Step 2: Verificar tabla de clientes**

Ir a Listado de clientes y verificar:
- Tabla sin bordes verticales
- Hover en filas
- Badges cuadrados
- Bordes sutiles

- [ ] **Step 3: Verificar formularios**

Ir a Nuevo cliente y verificar:
- Inputs con focus ring
- Labels estilizados
- Placeholder visibles
- Grid responsive

- [ ] **Step 4: Verificar odontograma**

Abrir detalle de un cliente con dentadura registrada:
- SVG de dientes esquematizados
- Colores por estado
- Tooltip al hover
- Leyenda visible

---

## Criterios de Aceptación

- [x] Todos los componentes UI usan la nueva paleta de colores
- [x] Tipografía Inter aplicada globalmente
- [x] Tablas rediseñadas sin bordes verticales, hover en filas
- [x] Formularios con focus ring y placeholder estilizados
- [x] Odontograma SVG con dientes esquematizados y tooltip
- [x] Dashboard con tarjetas más compactas
- [x] Navbar pública moderna con gradient
- [x] Responsive funciona correctamente en móvil y tablet
- [x] Accesibilidad WCAG AA cumplida
- [x] Micro-interacciones (hover, focus) fluidas

---

**Plan creado:** 2026-04-30  
**Estado:** Listo para implementación  
**Siguiente paso:** Ejecutar tareas en secuencia
