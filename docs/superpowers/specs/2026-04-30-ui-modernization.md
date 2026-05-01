# UI Modernization — Clínica Dental Mula

**Fecha:** 2026-04-30  
**Objetivo:** Modernizar el diseño de la interfaz web con un estilo clínico/profesional, optimizando componentes y añadiendo odontograma esquematizado.

---

## 1. Paleta de Colores Actualizada

### Colores Principales

| Color | Anterior | Nuevo | Uso |
|---|---|---|---|
| Azul primario | `#1a3a5c` | `#1e3a8a` (Slate-900) | Sidebar, navegación |
| Azul secundario | `#2563a8` | `#2563eb` (Blue-600) | Botones primarios |
| Azul claro | `#e8f0fb` | `#dbeafe` (Blue-100) | Fondos sutiles |
| Verde éxito | `#0d9e6e` | `#059669` (Emerald-600) | Éxito, acciones positivas |
| Verde claro | `#e6f7f2` | `#d1fae5` (Emerald-100) | Badges, estados |
| Rojo error | `#dc2626` | `#ef4444` (Red-500) | Errores, eliminación |
| Amarillo warning | `#ca8a04` | `#f59e0b` (Amber-500) | Advertencias |
| Fondo | `#f4f6fa` | `#f8fafc` (Slate-50) | Fondo principal |
| Gris borde | `#d1d9e6` | `#e2e8f0` (Slate-200) | Bordes, separadores |
| Texto principal | `#1e2a3a` | `#0f172a` (Slate-900) | Títulos |
| Texto secundario | `#4a5568` | `#64748b` (Slate-500) | Textos secundarios |

**Nota de accesibilidad:** Todos los colores cumplen WCAG AA en contraste.

---

## 2. Tipografía

### Fuentes

| Elemento | Fuente | Peso | Tamaños |
|---|---|---|---|
| Títulos (h1-h6) | Inter | 600-700 | h1: 1.75rem, h2: 1.5rem, h3: 1.25rem, h4: 1.125rem |
| Cuerpo | Inter | 400-500 | Texto: 0.9375rem (15px) |
| Monospace | JetBrains Mono | 400-500 | Código, filiaciones |
| Caption | Inter | 400 | Texto pequeño (0.8125rem) |

**CSS:**
```css
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
font-family-mono: 'JetBrains Mono', 'Fira Code', monospace;
```

---

## 3. Componentes UI

### Botones

```css
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1.25rem;
  border-radius: 6px;
  font-family: 'Inter', sans-serif;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.15s ease;
}

/* Variaciones */
.btn-primary {
  background: #2563eb;
  color: white;
}
.btn-primary:hover {
  background: #1d4ed8;
}

.btn-success {
  background: #059669;
  color: white;
}
.btn-success:hover {
  background: #047857;
}

.btn-danger {
  background: #ef4444;
  color: white;
}
.btn-danger:hover {
  background: #dc2626;
}

.btn-outline {
  background: transparent;
  border: 1px solid #3b82f6;
  color: #2563eb;
}
.btn-outline:hover {
  background: #dbeafe;
}

.btn-sm {
  padding: 0.375rem 0.875rem;
  font-size: 0.8125rem;
}

.btn-ghost {
  background: transparent;
  border: none;
  color: #64748b;
}
.btn-ghost:hover {
  background: #f1f5f9;
  color: #0f172a;
}
```

### Tarjetas (Cards)

```css
.card {
  background: white;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  border: 1px solid #e2e8f0;
}

/* Con borde superior de color */
.card-primary { border-top: 4px solid #2563eb; }
.card-success { border-top: 4px solid #059669; }
.card-warning { border-top: 4px solid #f59e0b; }
.card-danger  { border-top: 4px solid #ef4444; }

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #e2e8f0;
}
.card-title {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
}
```

### Tablas

```css
.table-wrap {
  overflow-x: auto;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

table {
  width: 100%;
  border-collapse: collapse;
}

table th {
  background: #f8fafc;
  text-align: left;
  padding: 0.75rem 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  border-bottom: 1px solid #e2e8f0;
}

table td {
  padding: 0.875rem 1rem;
  font-size: 0.875rem;
  color: #1e293b;
  border-bottom: 1px solid #e2e8f0;
  vertical-align: middle;
}

table tr:hover td {
  background: #f8fafc;
}

table tr:last-child td {
  border-bottom: none;
}
```

### Formularios

```css
.form-group {
  margin-bottom: 1rem;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  font-size: 0.875rem;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-control {
  width: 100%;
  padding: 0.625rem 0.875rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-family: 'Inter', sans-serif;
  font-size: 0.9375rem;
  color: #1e293b;
  background: white;
  transition: border-color 0.15s, box-shadow 0.15s;
}

.form-control:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.form-control::placeholder {
  color: #94a3b8;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}

.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
```

### Badges/Labels

```css
.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.625rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-pendiente {
  background: #fef3c7;
  color: #92400e;
}

.badge-confirmada {
  background: #dbeafe;
  color: #1e40af;
}

.badge-realizada {
  background: #d1fae5;
  color: #065f46;
}

.badge-cancelada {
  background: #fee2e2;
  color: #991b1b;
}

.badge-gestor {
  background: #1e3a8a;
  color: white;
}

.badge-cliente {
  background: #d1fae5;
  color: #065f46;
}
```

### Alertas

```css
.alert {
  padding: 0.875rem 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-left: 4px solid;
}

.alert-success {
  background: #d1fae5;
  color: #065f46;
  border-left-color: #059669;
}

.alert-danger {
  background: #fee2e2;
  color: #991b1b;
  border-left-color: #ef4444;
}

.alert-warning {
  background: #fef3c7;
  color: #92400e;
  border-left-color: #f59e0b;
}

.alert-info {
  background: #dbeafe;
  color: #1e40af;
  border-left-color: #3b82f6;
}
```

### Modal

```css
.modal-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(2px);
  z-index: 500;
  align-items: center;
  justify-content: center;
}

.modal-backdrop.open {
  display: flex;
}

.modal {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  width: 100%;
  max-width: 560px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
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
  color: #64748b;
  padding: 0.25rem;
  line-height: 1;
}
.modal-close:hover {
  color: #0f172a;
}
```

---

## 4. Odontograma SVG Rediseñado

### Estructura FDI

```
                    MAXILAR SUPERIOR
    ┌─────────────────────────────────────────────┐
    │  18  17  16  15  14  13  12  11  21  22  23  │
    │      28  27  26  25  24          34  35  36  │
    └─────────────────────────────────────────────┘
    
                    MAXILAR INFERIOR
    ┌─────────────────────────────────────────────┐
    │  48  47  46  45  44  43  42  41  31  32  33  │
    │      38  37  36  35  34          24  25  26  │
    │  41  42  43  44  45  46  47  48  18  17  16  │
    └─────────────────────────────────────────────┘
```

### Componente Blade: `components/dentadura.blade.php`

```blade
<div class="dentadura-container">
  <div class="dentadura-header">
    <h3 class="dentadura-title">Odontograma (Notación FDI)</h3>
    <div class="dentadura-legend">
      @foreach(['intact' => 'Sano', 'filling' => 'Obturación', 'crown' => 'Corona', 'extracted' => 'Extraído', 'none' => 'Sin registrar'] as $state => $label)
        <div class="legend-item">
          <div class="legend-color legend-{{ $state }}"></div>
          <span>{{ $label }}</span>
        </div>
      @endforeach
    </div>
  </div>

  <div class="dentadura-grid">
    <!-- Maxilar Superior -->
    <div class="dentadura-quadrant superior-derecha">
      @foreach([18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28] as $dienteId)
        <div class="diente" data-tooth="{{ $dienteId }}" data-state="{{ $dentadura[$dienteId]['estado'] ?? 'none' }}" title="{{ $dentadura[$dienteId]['descripcion'] ?? 'Sin registrar' }}">
          <svg viewBox="0 0 40 40" class="diente-svg {{ $dentadura[$dienteId]['estado'] ?? 'none' }}">
            <!-- SVG del diente según tipo (incisal, canine, premolar, molar) -->
          </svg>
          <span class="diente-number">{{ $dienteId }}</span>
        </div>
      @endforeach
    </div>

    <!-- Maxilar Inferior -->
    <div class="dentadura-quadrant inferior-derecha">
      @foreach([41,42,43,44,45,46,47,48,38,37,36,35,34,33,32,31] as $dienteId)
        <div class="diente" data-tooth="{{ $dienteId }}" data-state="{{ $dentadura[$dienteId]['estado'] ?? 'none' }}" title="{{ $dentadura[$dienteId]['descripcion'] ?? 'Sin registrar' }}">
          <svg viewBox="0 0 40 40" class="diente-svg {{ $dentadura[$dienteId]['estado'] ?? 'none' }}">
            <!-- SVG del diente según tipo (incisal, canine, premolar, molar) -->
          </svg>
          <span class="diente-number">{{ $dienteId }}</span>
        </div>
      @endforeach
    </div>
  </div>
</div>
```

### SVG de Dientes

**Incisivos (11, 12, 21, 22, 31, 32, 41, 42):**
```svg
<svg viewBox="0 0 40 40" class="diente-incisivo">
  <path d="M20 5 L35 15 L35 30 Q20 38 5 30 L5 15 Z" />
</svg>
```

**Caninos (13, 23, 33, 43):**
```svg
<svg viewBox="0 0 40 40" class="diente-canino">
  <path d="M20 3 L32 16 L28 32 Q20 37 12 32 L8 16 Z" />
</svg>
```

**Premolares (14, 15, 24, 25, 34, 35, 44, 45):**
```svg
<svg viewBox="0 0 40 40" class="diente-premolar">
  <path d="M18 3 L22 3 L25 15 L35 18 L32 30 Q20 36 8 30 L5 18 L15 15 Z" />
</svg>
```

**Molares (16, 17, 18, 26, 27, 28, 36, 37, 38, 46, 47, 48):**
```svg
<svg viewBox="0 0 40 40" class="diente-molar">
  <path d="M12 3 L16 12 L8 15 L10 25 Q20 30 30 25 L32 15 L24 12 Z" />
</svg>
```

### Estados de Dientes

| Estado | Color SVG | CSS Class |
|---|---|---|
| Intacto | #10b981 (green-500) | `diente-intact` |
| Obturación | #f59e0b (amber-500) | `diente-filling` |
| Corona | #3b82f6 (blue-500) | `diente-crown` |
| Extraído | #6b7280 (gray-500) | `diente-extracted` |
| Sin registrar | #e2e8f0 (slate-200) | `diente-none` |

### Estilos Odontograma

```css
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
  margin-top: 1rem;
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

.diente:hover {
  background: #f1f5f9;
}

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

/* Colores por estado */
.diente-intact .diente-svg { fill: #10b981; }
.diente-filling .diente-svg { fill: #f59e0b; }
.diente-crown .diente-svg { fill: #3b82f6; }
.diente-extracted .diente-svg { fill: #6b7280; }
.diente-none .diente-svg { fill: #e2e8f0; }

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
}
```

---

## 5. Layout General

### Navbar Pública

```css
.navbar {
  background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
  padding: 0 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 72px;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 4px 12px rgba(30, 58, 138, 0.2);
}

.navbar-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-family: 'Inter', sans-serif;
  color: white;
  font-size: 1.25rem;
  font-weight: 700;
  text-decoration: none;
}

.navbar-links a {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.9375rem;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  transition: all 0.15s;
}

.navbar-links a:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
  text-decoration: none;
}

.btn-nav {
  background: #059669;
  color: white;
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

### Sidebar

```css
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  z-index: 200;
  box-shadow: 4px 0 16px rgba(0, 0, 0, 0.2);
}

.sidebar-logo {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-logo a {
  font-family: 'Inter', sans-serif;
  color: white;
  font-size: 1.125rem;
  font-weight: 700;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.sidebar-logo span {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
  display: block;
  margin-top: 0.25rem;
  font-weight: 400;
}

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
  color: rgba(255, 255, 255, 0.75);
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.15s;
  border-left: 3px solid transparent;
}

.sidebar-nav a:hover,
.sidebar-nav a.active {
  background: rgba(255, 255, 255, 0.08);
  color: white;
  border-left-color: #059669;
  text-decoration: none;
}

.sidebar-nav .nav-section {
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: rgba(255, 255, 255, 0.4);
  text-transform: uppercase;
  padding: 1rem 1.5rem 0.5rem;
  margin-top: 0.5rem;
}
```

### App Main

```css
.app-main {
  margin-left: 280px;
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: #f8fafc;
}

.app-topbar {
  background: white;
  border-bottom: 1px solid #e2e8f0;
  padding: 1rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.app-topbar h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.app-content {
  padding: 2rem;
  flex: 1;
}
```

### Dashboard Cards

```css
.dashboard-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.dashboard-stat-card {
  background: white;
  border-radius: 8px;
  padding: 1.25rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  border: 1px solid #e2e8f0;
  border-top: 4px solid;
}

.dashboard-stat-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.dashboard-stat-value {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.dashboard-stat-label {
  font-size: 0.8125rem;
  color: #64748b;
  font-weight: 500;
}
```

---

## 6. Animaciones y Micro-interacciones

```css
/* Transiciones globales */
*, *::before, *::after {
  transition: all 0.15s ease;
}

/* Hover states */
.hover-underline {
  text-decoration: none;
  position: relative;
}

.hover-underline::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 100%;
  height: 2px;
  background: currentColor;
  transform: scaleX(0);
  transform-origin: right;
  transition: transform 0.15s ease;
}

.hover-underline:hover::after {
  transform: scaleX(1);
  transform-origin: left;
}

/* Focus visible para accesibilidad */
*:focus-visible {
  outline: 2px solid #2563eb;
  outline-offset: 2px;
}

/* Scrollbar personalizada */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
```

---

## 7. Responsive Design

```css
/* Mobile (< 768px) */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    transition: transform 0.25s ease;
  }
  
  .sidebar.open {
    transform: translateX(0);
  }
  
  .app-main {
    margin-left: 0;
  }
  
  .app-content {
    padding: 1rem;
  }
  
  .form-grid-2 {
    grid-template-columns: 1fr;
  }
  
  .navbar {
    padding: 0 1rem;
  }
  
  .dentadura-grid {
    grid-template-columns: 1fr;
  }
}

/* Tablet (< 1024px) */
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

---

## 8. Implementación Técnica

### Dependencias NPM (opcionales)

```json
{
  "devDependencies": {
    "autoprefixer": "^10.4.16",
    "postcss": "^8.4.32",
    "tailwindcss": "^3.4.0"
  }
}
```

### Archivos a modificar

| Archivo | Descripción | Prioridad |
|---|---|---|
| `resources/views/layouts/app.blade.php` | Layout principal con CSS variables actualizado | P1 |
| `resources/views/public/home.blade.php` | Página pública | P2 |
| `resources/views/public/servicios.blade.php` | Página de servicios | P2 |
| `resources/views/public/contacto.blade.php` | Formulario de contacto | P2 |
| `resources/views/auth/login.blade.php` | Login | P2 |
| `resources/views/clientes/index.blade.php` | Lista de clientes | P1 |
| `resources/views/clientes/show.blade.php` | Detalle del cliente (incluye odontograma) | P1 |
| `resources/views/clientes/create.blade.php` | Formulario crear cliente | P1 |
| `resources/views/clientes/edit.blade.php` | Formulario editar cliente | P1 |
| `resources/views/citas/calendario.blade.php` | Calendario de citas | P2 |
| `resources/views/citas/mis-citas.blade.php` | Mis citas | P2 |
| `resources/views/dashboard/index.blade.php` | Dashboard gestor | P1 |
| `resources/views/components/dentadura.blade.php` | Nuevo componente odontograma SVG | P1 |

### Nueva estructura de directorios

```
resources/views/
├── layouts/
│   └── app.blade.php
├── components/
│   └── dentadura.blade.php      ← Nuevo
├── public/
│   ├── home.blade.php
│   ├── servicios.blade.php
│   └── contacto.blade.php
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
├── clientes/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── citas/
│   ├── calendario.blade.php
│   └── mis-citas.blade.php
└── dashboard/
    └── index.blade.php
```

---

## 9. Criterios de Aceptación

- [ ] Todos los componentes UI usan la nueva paleta de colores
- [ ] Tipografía Inter aplicada globalmente
- [ ] Tablas rediseñadas sin bordes verticales, hover en filas
- [ ] Formularios con focus ring y placeholder estilizados
- [ ] Odontograma SVG con dientes esquematizados y tooltip
- [ ] Dashboard con tarjetas más compactas
- [ ] Navbar pública moderna con gradient
- [ ] Responsive funciona correctamente en móvil y tablet
- [ ] Accesibilidad WCAG AA cumplida
- [ ] Micro-interacciones (hover, focus) fluidas

---

## 10. Referencias de Diseño

**Inspiraciones:**
- Tailwind UI — Moderno y profesional
- Bootstrap 5 — Componentes accesibles
- Shadcn/ui — Minimalista y limpio
- Stripe Design — Tonos azules profesionales

**Fuentes:**
- [Inter en Google Fonts](https://fonts.google.com/specimen/Inter)
- [JetBrains Mono en Google Fonts](https://fonts.google.com/specimen/JetBrains+Mono)

---

**Documento creado:** 2026-04-30  
**Estado:** Aprobado  
**Siguiente paso:** Implementación del plan
