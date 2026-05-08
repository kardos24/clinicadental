# Refactoring Completo — Plan de Implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Eliminar la deuda técnica del proyecto: extraer CSS/JS a archivos estáticos, dividir vistas monolíticas en partials, crear FormRequests y Services, y descomponer el ApiController monolítico en cuatro controladores especializados.

**Architecture:** Refactoring puro — ningún comportamiento cambia, solo se reorganiza el código. CSS y JS salen de las vistas a `public/css/app.css` y `public/js/*.js`. La validación pasa a FormRequests. La lógica de negocio del odontograma y las citas pasa a Services inyectados por el contenedor de Laravel.

**Tech Stack:** Laravel 11, PHP 8.3, Blade templates, archivos estáticos servidos por Apache (sin Vite/npm), PHPUnit.

---

## Mapa de archivos

```
public/
  css/app.css                     ← CSS extraído del layout
  js/app.js                       ← sidebar toggle + navbar móvil
  js/odontograma.js               ← funciones JS del odontograma

app/Http/
  Controllers/Api/
    AuthApiController.php         ← login, logout
    ClienteApiController.php      ← index, show, historial
    CitaApiController.php         ← index, store, actualizarEstado, mes
    DispositivoApiController.php  ← registrarToken
  Controllers/ApiController.php  ← ELIMINADO
  Requests/
    StoreClienteRequest.php
    UpdateClienteRequest.php
    StoreCitaRequest.php
    UpdateCitaEstadoRequest.php
    StoreHistorialRequest.php
    UpdateHistorialRequest.php
    ContactoRequest.php
  Middleware/EsGestorOPropietario.php  ← nuevo
Services/
  DentaduraService.php            ← nuevo
  CitaService.php                 ← nuevo

resources/views/
  layouts/app.blade.php           ← ~180 líneas, solo HTML shell
  clientes/
    show.blade.php                ← ~30 líneas, solo estructura + @includes
    partials/
      _datos-personales.blade.php
      _odontograma.blade.php
      _historial.blade.php
      _citas.blade.php
      _modales.blade.php
  components/
    dentadura.blade.php           ← ELIMINADO (legacy)
    diente-svg.blade.php          ← ELIMINADO (legacy)
```

---

## Task 1: Crear rama de trabajo

**Files:**
- Git branch: `feature/refactoring-completo`

- [ ] **Step 1: Crear la rama desde develop**

```bash
git checkout develop
git pull origin develop
git checkout -b feature/refactoring-completo
```

Expected: `Switched to a new branch 'feature/refactoring-completo'`

- [ ] **Step 2: Confirmar tests en verde antes de empezar**

```bash
php vendor/bin/phpunit --stop-on-failure
```

Expected: todos los tests pasan (0 failures).

---

## Task 2: Extraer CSS a public/css/app.css

**Files:**
- Create: `public/css/app.css`

El CSS se copia de `resources/views/layouts/app.blade.php` (todo el contenido del `<style>` tag, líneas ~18-990), pero con dos cambios clave:
1. El bloque `:root` se escribe SIN las variables alias.
2. Se añaden cuatro clases de utilidad al final.

- [ ] **Step 1: Crear `public/css/app.css`**

Copia el contenido del `<style>` de `layouts/app.blade.php` y escríbelo en `public/css/app.css`. El bloque `:root` debe quedar EXACTAMENTE así (sin el bloque de aliases al final):

```css
:root {
    --primary:         #0d47a1;
    --primary-dark:    #002171;
    --primary-light:   #e3f2fd;
    --secondary:       #0288d1;
    --secondary-dark:  #0277bd;
    --accent:          #00acc1;
    --accent-light:    #e0f7fa;
    --success:         #10b981;
    --success-dark:    #059669;
    --success-light:   #d1fae5;
    --warning:         #f59e0b;
    --warning-dark:    #d97706;
    --danger:          #ef4444;
    --danger-dark:     #dc2626;
    --danger-light:    #fee2e2;
    --gray-50:         #f9fafb;
    --gray-100:        #f3f4f6;
    --gray-200:        #e5e7eb;
    --gray-300:        #d1d5db;
    --gray-500:        #6b7280;
    --gray-700:        #374151;
    --gray-900:        #111827;
    --text:            #111827;
    --text-light:      #4b5563;
    --text-muted:      #9ca3af;
    --white:           #ffffff;
    --sidebar-width:   280px;
    --shadow-sm:       0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md:       0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg:       0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --radius-sm:       4px;
    --radius-md:       6px;
    --radius-lg:       8px;
    /* Aliases temporales — se eliminan en Task 7 */
    --azul:        var(--primary);
    --azul-oscuro: var(--primary-dark);
    --azul-claro:  var(--primary-light);
    --verde:       var(--success);
    --verde-claro: var(--success-light);
    --rojo:        var(--danger);
    --gris-borde:  var(--gray-200);
    --gris-fondo:  var(--gray-50);
    --texto-med:   var(--text-light);
    --texto-muted: var(--text-muted);
}
```

Copia el resto del CSS (reset, tipografía, layout, botones, cards, tablas, formularios, alertas, badges, modales, sidebar-toggle, responsive) sin cambios.

Añade al final del archivo las cuatro clases de utilidad para vistas públicas:

```css
/* ── Utilidades vistas públicas ──────────────────────────────────────────── */
.section-public {
    padding: 4rem 2rem;
    max-width: 1000px;
    margin: 0 auto;
}

.section-hero {
    background: linear-gradient(135deg, #1a3a5c 0%, #2563a8 60%, #0d9e6e 100%);
    color: #fff;
    padding: clamp(3rem, 8vw, 6rem) clamp(1rem, 4vw, 2rem);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.grid-2col {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr));
    gap: 2rem;
    align-items: start;
}

.cta-box {
    background: var(--primary);
    color: #fff;
    border-radius: 14px;
    padding: 2.5rem;
    text-align: center;
    margin-top: 2.5rem;
}

.cta-box h2 { color: #fff; margin-bottom: 0.75rem; }
.cta-box p  { color: rgba(255,255,255,.8); margin-bottom: 1.5rem; }
```

- [ ] **Step 2: Commit**

```bash
git add public/css/app.css
git commit -m "refactor: extraer CSS a public/css/app.css"
```

---

## Task 3: Extraer JS a public/js/app.js y public/js/odontograma.js

**Files:**
- Create: `public/js/app.js`
- Create: `public/js/odontograma.js`

- [ ] **Step 1: Crear `public/js/app.js`**

Contiene el IIFE que actualmente vive al final de `layouts/app.blade.php`:

```javascript
(function () {
    'use strict';

    // Sidebar toggle — páginas privadas
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebar       = document.getElementById('sidebar');
    var overlay       = document.getElementById('sidebarOverlay');
    if (sidebarToggle && sidebar && overlay) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        });
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
            });
        });
    }

    // Mobile nav — páginas públicas
    var navToggle = document.getElementById('navbarToggle');
    var navMenu   = document.getElementById('navbarMobileMenu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            navMenu.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (navMenu.classList.contains('open') &&
                !navToggle.contains(e.target) &&
                !navMenu.contains(e.target)) {
                navMenu.classList.remove('open');
            }
        });
    }
})();
```

- [ ] **Step 2: Crear `public/js/odontograma.js`**

Funciones del odontograma; usan `window.dentaduraData` y `window.dentaduraUrl` que se inyectan desde Blade en el partial `_odontograma.blade.php`:

```javascript
'use strict';

var dienteActual = null;

function abrirModalDiente(num, caraResaltar) {
    dienteActual = num;
    var d = window.dentaduraData[String(num)] || {};

    document.getElementById('diente-num-label').textContent = num;
    document.getElementById('diente-pieza').value      = d.estado_pieza    || 'presente';
    document.getElementById('diente-vestibular').value = d.cara_vestibular || 'sano';
    document.getElementById('diente-lingual').value    = d.cara_lingual    || 'sano';
    document.getElementById('diente-mesial').value     = d.cara_mesial     || 'sano';
    document.getElementById('diente-distal').value     = d.cara_distal     || 'sano';
    document.getElementById('diente-oclusal').value    = d.cara_oclusal    || 'sano';
    document.getElementById('diente-notas').value      = d.notas           || '';

    var oclusalField  = document.getElementById('campo-oclusal');
    var oclusalSelect = document.getElementById('diente-oclusal');
    if (d.tiene_oclusal) {
        oclusalField.style.display = '';
        oclusalSelect.disabled = false;
    } else {
        oclusalField.style.display = 'none';
        oclusalSelect.disabled = true;
        oclusalSelect.value = 'sano';
    }

    toggleCaras();

    document.querySelectorAll('.cara-field').forEach(function (f) {
        f.classList.remove('cara-highlight');
    });

    document.getElementById('modal-diente').classList.add('open');

    if (caraResaltar) {
        setTimeout(function () {
            var campo = document.getElementById('diente-' + caraResaltar);
            if (campo && !campo.disabled) {
                var field = campo.closest('.cara-field');
                if (field) {
                    field.classList.add('cara-highlight');
                    campo.focus();
                    setTimeout(function () { field.classList.remove('cara-highlight'); }, 2500);
                }
            }
        }, 60);
    }
}

function abrirModalCaraDiente(num, cara) {
    abrirModalDiente(num, cara);
}

function toggleCaras() {
    var pieza   = document.getElementById('diente-pieza').value;
    var section = document.getElementById('caras-section');
    if (pieza !== 'presente') {
        section.classList.add('caras-disabled');
    } else {
        section.classList.remove('caras-disabled');
    }
}

function abrirModalCita() {
    document.getElementById('modal-cita').classList.add('open');
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('open');
}

function guardarDiente() {
    var payload = {
        num_diente:      String(dienteActual),
        estado_pieza:    document.getElementById('diente-pieza').value,
        cara_vestibular: document.getElementById('diente-vestibular').value,
        cara_lingual:    document.getElementById('diente-lingual').value,
        cara_mesial:     document.getElementById('diente-mesial').value,
        cara_distal:     document.getElementById('diente-distal').value,
        cara_oclusal:    document.getElementById('diente-oclusal').value,
        notas:           document.getElementById('diente-notas').value,
    };

    fetch(window.dentaduraUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ dientes: [payload] }),
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.ok) { cerrarModal('modal-diente'); location.reload(); }
        else { alert(data.message || 'Error al guardar.'); }
    })
    .catch(function () { alert('Error al guardar. Inténtalo de nuevo.'); });
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.modal-backdrop').forEach(function (b) {
        b.addEventListener('click', function (e) {
            if (e.target === b) b.classList.remove('open');
        });
    });
});
```

- [ ] **Step 3: Commit**

```bash
git add public/js/app.js public/js/odontograma.js
git commit -m "refactor: extraer JS a public/js/app.js y odontograma.js"
```

---

## Task 4: Simplificar layouts/app.blade.php

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

Eliminar el `<style>` completo (líneas 17-990) y el `<script>` IIFE al final. Enlazar los archivos estáticos.

- [ ] **Step 1: Reescribir `layouts/app.blade.php`**

El archivo resultante (conserva todo el HTML estructural pero reemplaza el bloque `<style>` y el IIFE):

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Clínica Dental Mula')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>

@auth
    <div class="app-wrap">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <a href="{{ route('home') }}">
                    <svg viewBox="0 0 32 32" fill="none"><path d="M8 6c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2v4c0 5.5-3.6 10.2-8.5 11.8V26h2.5a1 1 0 010 2h-7a1 1 0 010-2H13.5v-4.2C8.6 20.2 5 15.5 5 10V6z" fill="white" fill-opacity=".9"/></svg>
                    <div>
                        Dental Mula
                        <span>Panel de gestión</span>
                    </div>
                </a>
            </div>
            <nav class="sidebar-nav">
                @if(auth()->user()->isGestor())
                    <div class="nav-section">Principal</div>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">📊 Dashboard</a>
                    <div class="nav-section">Pacientes</div>
                    <a href="{{ route('clientes.index') }}" class="{{ request()->routeIs('clientes.*') ? 'active' : '' }}">👥 Listado de clientes</a>
                    <a href="{{ route('clientes.create') }}">➕ Nuevo cliente</a>
                    <div class="nav-section">Agenda</div>
                    <a href="{{ route('citas.calendario') }}" class="{{ request()->routeIs('citas.calendario') ? 'active' : '' }}">📅 Calendario de citas</a>
                @else
                    <div class="nav-section">Mi área</div>
                    <a href="{{ route('citas.mis-citas') }}" class="{{ request()->routeIs('citas.mis-citas') ? 'active' : '' }}">📅 Mis citas</a>
                    @if(auth()->user()->cliente)
                    <a href="{{ route('clientes.show', auth()->user()->cliente) }}">📋 Mi historial</a>
                    @endif
                @endif
            </nav>
            <div class="sidebar-footer">
                <strong>{{ auth()->user()->name }}</strong>
                <span class="badge {{ auth()->user()->isGestor() ? 'badge-gestor' : 'badge-cliente' }}">
                    {{ auth()->user()->isGestor() ? 'Gestor' : 'Cliente' }}
                </span>
                <form method="POST" action="{{ route('logout') }}" style="margin-top:.5rem">
                    @csrf
                    <button type="submit" style="background:none;border:none;color:rgba(255,255,255,.6);cursor:pointer;font-size:.8rem;padding:0;">
                        🚪 Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <div class="app-main">
            <div class="app-topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menú">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <h2>@yield('page-title', 'Inicio')</h2>
                </div>
                <div class="topbar-actions">@yield('topbar-actions')</div>
            </div>
            <div class="app-content">
                @if(session('success'))
                    <div class="alert alert-success">✅ {{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">❌ {{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Corrige los siguientes errores:</strong>
                        <ul style="margin:.5rem 0 0 1rem">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>

@else
    <nav class="navbar">
        <a href="{{ route('home') }}" class="navbar-brand">
            <svg viewBox="0 0 32 32" fill="none"><path d="M8 6c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2v4c0 5.5-3.6 10.2-8.5 11.8V26h2.5a1 1 0 010 2h-7a1 1 0 010-2H13.5v-4.2C8.6 20.2 5 15.5 5 10V6z" fill="white"/></svg>
            Clínica Dental Mula
        </a>
        <div class="navbar-links">
            <a href="{{ route('home') }}">Inicio</a>
            <a href="{{ route('servicios') }}">Servicios</a>
            <a href="{{ route('contacto') }}">Contacto</a>
            <a href="{{ route('login') }}" class="btn-nav">Acceder</a>
        </div>
        <button class="navbar-mobile-toggle" id="navbarToggle" aria-label="Abrir menú">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
        <div class="navbar-mobile-menu" id="navbarMobileMenu">
            <a href="{{ route('home') }}">🏠 Inicio</a>
            <a href="{{ route('servicios') }}">🦷 Servicios</a>
            <a href="{{ route('contacto') }}">📞 Contacto</a>
            <a href="{{ route('login') }}" class="btn-nav">Acceder →</a>
        </div>
    </nav>

    @yield('content')

    <footer style="background:var(--primary);color:rgba(255,255,255,.6);text-align:center;padding:2rem;margin-top:4rem;font-size:.85rem;">
        <p>© {{ date('Y') }} Clínica Dental Mula · C/ Mayor, 1 · 30170 Mula, Murcia · Tel: 968 66 00 00</p>
        <p style="margin-top:.4rem;font-size:.75rem;">Datos protegidos según RGPD y LOPDGDD</p>
    </footer>
@endauth

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
```

- [ ] **Step 2: Limpiar cache de vistas y verificar en el navegador**

```bash
php artisan view:clear
```

Abrir `http://127.0.0.1:8080` en el navegador y comprobar:
- Estilos cargan (sin flash de contenido sin estilos)
- Sidebar toggle funciona en móvil
- Navbar pública funciona

- [ ] **Step 3: Commit**

```bash
git add resources/views/layouts/app.blade.php
git commit -m "refactor: simplificar layouts/app.blade.php — enlazar assets estáticos"
```

---

## Task 5: Dividir clientes/show.blade.php en 5 partials

**Files:**
- Modify: `resources/views/clientes/show.blade.php`
- Create: `resources/views/clientes/partials/_datos-personales.blade.php`
- Create: `resources/views/clientes/partials/_odontograma.blade.php`
- Create: `resources/views/clientes/partials/_historial.blade.php`
- Create: `resources/views/clientes/partials/_citas.blade.php`
- Create: `resources/views/clientes/partials/_modales.blade.php`

- [ ] **Step 1: Reescribir `clientes/show.blade.php`**

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
        @include('clientes.partials._datos-personales', ['cliente' => $cliente])
    </div>
    <div class="cliente-main">
        @include('clientes.partials._odontograma', ['cliente' => $cliente, 'dentadura' => $dentadura])
        @include('clientes.partials._historial', ['cliente' => $cliente, 'historial' => $historial])
        @include('clientes.partials._citas', ['cliente' => $cliente, 'citas' => $citasFuturas])
    </div>
</div>
@include('clientes.partials._modales', ['cliente' => $cliente, 'dentadura' => $dentadura])
@endsection
```

Añadir en `public/css/app.css` (después de `.odontograma` si existe, o al final) las clases del grid:

```css
/* ── Layout ficha de cliente ─────────────────────────────────────────────── */
.cliente-grid {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 1.5rem;
    align-items: start;
}
.cliente-sidebar { display: flex; flex-direction: column; gap: 1.5rem; }
.cliente-main    { display: flex; flex-direction: column; gap: 1.5rem; }
@media (max-width: 900px) {
    .cliente-grid { grid-template-columns: 1fr; }
}
```

- [ ] **Step 2: Crear `_datos-personales.blade.php`**

```blade
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

- [ ] **Step 3: Crear `_citas.blade.php`**

```blade
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

- [ ] **Step 4: Crear `_historial.blade.php`**

```blade
<div class="card" style="padding:0;">
    <div class="card-header" style="padding:1.25rem 1.5rem;">
        <h3 class="card-title">📋 Historial clínico</h3>
        @if(auth()->user()->isGestor())
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('modal-historial').classList.add('open')">
            + Añadir registro
        </button>
        @endif
    </div>
    @php
        $totalDebe  = $historial->sum('debe');
        $totalHaber = $historial->sum('haber');
        $saldo      = $totalDebe - $totalHaber;
    @endphp
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-bottom:1px solid var(--gray-200);">
        @foreach([
            ['Debe total',  '€ ' . number_format($totalDebe,2),  'var(--danger)'],
            ['Haber total', '€ ' . number_format($totalHaber,2), 'var(--success)'],
            ['Saldo',       '€ ' . number_format($saldo,2),      $saldo > 0 ? 'var(--danger)' : 'var(--success)'],
        ] as [$l, $v, $c])
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

- [ ] **Step 5: Crear `_modales.blade.php`**

Contiene las tres modales que actualmente están en `show.blade.php` líneas 346-500 (solo visibles para gestor). Copiar verbatim esas líneas con este wrapper:

```blade
@if(auth()->user()->isGestor())
{{-- Modal: Editar diente (multi-cara) --}}
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

{{-- Modal: Nuevo registro historial --}}
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

{{-- Modal: Nueva cita --}}
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

- [ ] **Step 6: Crear `_odontograma.blade.php`**

Contiene el CSS específico del odontograma (copiado del `@push('styles')` de `show.blade.php`), la sección oclusal (líneas 139-258 de `show.blade.php` movidas verbatim), el componente bucal (líneas 260-273), y la inyección de datos JS:

```blade
@push('styles')
<style>
/* ── ODONTOGRAMA MULTI-CARA ─────────────────────────────────────────────── */
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
.diente-svg .cara-svg {
    stroke:#94a3b8; stroke-width:.7; cursor:pointer;
    transition:filter .1s, stroke .1s;
}
.diente-svg .cara-svg:hover {
    filter:brightness(.72) saturate(1.3);
    stroke:#1e3a8a; stroke-width:1.2;
}
.diente-svg .cara-nooclusal { stroke:#cbd5e1; stroke-width:.5; stroke-dasharray:2,2; pointer-events:none; }
.cara-field.cara-highlight > select {
    outline:2px solid #2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.2);
}
.diente-pieza-icon {
    position:absolute; top:50%; left:50%; transform:translate(-50%,-52%);
    font-size:.75rem; font-weight:800; color:#fff; text-shadow:0 1px 3px rgba(0,0,0,.65);
    pointer-events:none; line-height:1;
}
.diente-separador { width:12px; flex-shrink:0; }
.leyenda-grupo { margin-bottom:.4rem; }
.leyenda-titulo { font-size:.66rem; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:.5px; margin-bottom:.2rem; }
.leyenda-items { display:flex; gap:.5rem; flex-wrap:wrap; }
.leyenda-item { display:flex; align-items:center; gap:.3rem; font-size:.72rem; color:#475569; }
.leyenda-color { width:12px; height:12px; border-radius:2px; flex-shrink:0; border:1px solid #cbd5e1; }
.pieza-estado-section { border-bottom:1px solid var(--gray-200); padding-bottom:.75rem; margin-bottom:.75rem; }
.cara-grid { display:grid; grid-template-columns:1fr 1fr; gap:.6rem; margin-top:.5rem; }
.cara-field label { font-size:.78rem; font-weight:600; color:var(--text-light); display:block; margin-bottom:.15rem; }
.cara-field select { width:100%; }
.cara-field.full-width { grid-column:1/-1; }
.caras-disabled { opacity:.35; pointer-events:none; }
#modal-diente select.form-control { width:100%; }
</style>
@endpush

{{-- ODONTOGRAMA OCLUSAL (multi-cara) --}}
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
        $colorCara = fn($cara) => $esPresente ? ($d?->colorCara($cara) ?? $sanoColor) : $sanoColor;
        return compact('d','pieza','esPresente','tieneOcl','mesialDer','tipo','colorCara');
    };
@endphp

{{-- Copia verbatim el contenido del card "Odontograma (vista oclusal)" de show.blade.php líneas 139-258 --}}
{{-- (el bloque @php + dientes-row superior, separador, dientes-row inferior, leyenda) --}}

{{-- ODONTOGRAMA BUCAL --}}
<div class="card odontograma" id="odontograma-bucal-card">
    <div class="card-header" style="flex-direction:column;align-items:flex-start;">
        <h3 class="card-title" style="margin-bottom:.25rem;">🦷 Odontograma anatómico</h3>
        <p style="font-size:.75rem;color:var(--text-light);margin:0;">
            Vista bucal (frontal) — corona coloreada según estado de la cara vestibular.
        </p>
    </div>
    <x-odontograma-bucal :cliente="$cliente" :dentadura="$dentadura" :modoEdicion="true" />
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

**Nota importante sobre `_odontograma.blade.php`:** El bloque del odontograma oclusal (cards con las dos arcadas) debe copiarse verbatim de `clientes/show.blade.php` líneas 138-258. En el archivo de destino, se sitúa entre el `@php ... @endphp` del `$toothData` y el card del odontograma bucal.

- [ ] **Step 7: Limpiar caché y verificar en el navegador**

```bash
php artisan view:clear
```

Abrir la ficha de un paciente (`/clientes/{id}`) y verificar:
- Odontograma oclusal se renderiza
- Odontograma anatómico se renderiza
- Modal de diente abre y guarda correctamente
- Modal de historial abre
- Modal de cita abre

- [ ] **Step 8: Commit**

```bash
git add resources/views/clientes/show.blade.php \
        resources/views/clientes/partials/ \
        public/css/app.css
git commit -m "refactor: dividir clientes/show.blade.php en 5 partials"
```

---

## Task 6: Eliminar componentes legacy

**Files:**
- Delete: `resources/views/components/dentadura.blade.php`
- Delete: `resources/views/components/diente-svg.blade.php`

- [ ] **Step 1: Verificar que no hay referencias activas**

```bash
# Buscar referencias a x-dentadura o x-diente-svg en vistas
grep -r "x-dentadura\|x-diente-svg\|dentadura\.blade\|diente-svg\.blade" resources/views/
```

Expected: sin resultados (solo deben aparecer en los propios archivos legacy).

- [ ] **Step 2: Eliminar archivos**

```bash
rm resources/views/components/dentadura.blade.php
rm resources/views/components/diente-svg.blade.php
```

- [ ] **Step 3: Limpiar caché y verificar**

```bash
php artisan view:clear
php vendor/bin/phpunit --stop-on-failure
```

Expected: sin errores.

- [ ] **Step 4: Commit**

```bash
git add -u resources/views/components/
git commit -m "refactor: eliminar componentes Blade legacy (dentadura, diente-svg)"
```

---

## Task 7: Eliminar aliases CSS y convertir inline styles en vistas públicas

**Files:**
- Modify: `public/css/app.css`
- Modify: `resources/views/public/home.blade.php`
- Modify: `resources/views/public/servicios.blade.php`
- Modify: `resources/views/public/contacto.blade.php`

Los aliases temporales del `:root` se eliminan. Las vistas públicas reemplazan los patrones de `style=""` más repetidos por clases CSS.

- [ ] **Step 1: Eliminar aliases de `public/css/app.css`**

En el bloque `:root`, eliminar estas líneas:
```css
/* Eliminar este bloque completo de aliases temporales: */
--azul:        var(--primary);
--azul-oscuro: var(--primary-dark);
--azul-claro:  var(--primary-light);
--verde:       var(--success);
--verde-claro: var(--success-light);
--rojo:        var(--danger);
--gris-borde:  var(--gray-200);
--gris-fondo:  var(--gray-50);
--texto-med:   var(--text-light);
--texto-muted: var(--text-muted);
```

- [ ] **Step 2: Buscar todos los usos de variables alias en las vistas**

```bash
grep -rn "var(--azul\|var(--verde\|var(--rojo\|var(--gris\|var(--texto" resources/views/
```

Para cada ocurrencia, reemplazar por el nombre canónico:

| Alias           | Canónico            |
|-----------------|---------------------|
| `var(--azul)`   | `var(--primary)`    |
| `var(--azul-oscuro)` | `var(--primary-dark)` |
| `var(--azul-claro)` | `var(--primary-light)` |
| `var(--verde)`  | `var(--success)`    |
| `var(--verde-claro)` | `var(--success-light)` |
| `var(--rojo)`   | `var(--danger)`     |
| `var(--gris-borde)` | `var(--gray-200)` |
| `var(--gris-fondo)` | `var(--gray-50)`  |
| `var(--texto-med)` | `var(--text-light)` |
| `var(--texto-muted)` | `var(--text-muted)` |

- [ ] **Step 3: Actualizar `public/home.blade.php`**

Reemplazar el hero section (que usa `style=""` masivos) por la clase `.section-hero`:

```blade
{{-- ANTES --}}
<section style="background:linear-gradient(135deg,#1a3a5c 0%,#2563a8 60%,#0d9e6e 100%);color:#fff;padding:clamp(3rem,8vw,6rem) clamp(1rem,4vw,2rem);text-align:center;position:relative;overflow:hidden;">

{{-- DESPUÉS --}}
<section class="section-hero">
```

Reemplazar la sección de servicios:
```blade
{{-- ANTES --}}
<section style="padding:5rem 2rem;max-width:1100px;margin:0 auto;">

{{-- DESPUÉS --}}
<section class="section-public" style="padding-top:5rem;">
```

Reemplazar el grid de "Por qué elegirnos":
```blade
{{-- ANTES --}}
<div style="max-width:900px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,340px),1fr));gap:3rem;align-items:center;">

{{-- DESPUÉS --}}
<div class="grid-2col" style="max-width:900px;margin:0 auto;gap:3rem;align-items:center;">
```

- [ ] **Step 4: Actualizar `public/servicios.blade.php`**

```blade
{{-- ANTES --}}
<section style="padding:4rem 2rem;max-width:1000px;margin:0 auto;">

{{-- DESPUÉS --}}
<section class="section-public">
```

El div `.cta-box` del final:
```blade
{{-- ANTES --}}
<div style="background:var(--azul);color:#fff;border-radius:14px;padding:2.5rem;text-align:center;margin-top:2.5rem;">

{{-- DESPUÉS --}}
<div class="cta-box">
```

- [ ] **Step 5: Actualizar `public/contacto.blade.php`**

```blade
{{-- ANTES --}}
<section style="padding:4rem 2rem;max-width:900px;margin:0 auto;">

{{-- DESPUÉS --}}
<section class="section-public">
```

El grid de dos columnas:
```blade
{{-- ANTES --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,340px),1fr));gap:2rem;align-items:start;">

{{-- DESPUÉS --}}
<div class="grid-2col">
```

- [ ] **Step 6: Verificar en el navegador**

Visitar `/`, `/servicios`, y `/contacto` y comprobar que la maquetación es idéntica a antes.

- [ ] **Step 7: Commit**

```bash
php artisan view:clear
git add public/css/app.css resources/views/public/
git commit -m "refactor: eliminar aliases CSS y convertir inline styles en vistas públicas"
```

---

## Task 8: Crear 7 FormRequests

**Files:**
- Create: `app/Http/Requests/StoreClienteRequest.php`
- Create: `app/Http/Requests/UpdateClienteRequest.php`
- Create: `app/Http/Requests/StoreCitaRequest.php`
- Create: `app/Http/Requests/UpdateCitaEstadoRequest.php`
- Create: `app/Http/Requests/StoreHistorialRequest.php`
- Create: `app/Http/Requests/UpdateHistorialRequest.php`
- Create: `app/Http/Requests/ContactoRequest.php`

- [ ] **Step 1: Crear `StoreClienteRequest.php`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'apellidos'     => 'required|string|max:100',
            'nombre'        => 'required|string|max:100',
            'edad'          => 'nullable|integer|min:0|max:150',
            'profesion'     => 'nullable|string|max:100',
            'direccion'     => 'nullable|string|max:200',
            'cp'            => 'nullable|string|max:10',
            'telefono'      => 'nullable|string|max:20',
            'observaciones' => 'nullable|string',
        ];
    }
}
```

- [ ] **Step 2: Crear `UpdateClienteRequest.php`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'apellidos'     => 'required|string|max:100',
            'nombre'        => 'required|string|max:100',
            'edad'          => 'nullable|integer|min:0|max:150',
            'profesion'     => 'nullable|string|max:100',
            'direccion'     => 'nullable|string|max:200',
            'cp'            => 'nullable|string|max:10',
            'telefono'      => 'nullable|string|max:20',
            'observaciones' => 'nullable|string',
        ];
    }
}
```

- [ ] **Step 3: Crear `StoreCitaRequest.php`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cliente_id'       => 'sometimes|required|exists:clientes,id',
            'fecha_hora'       => 'required|date|after:now',
            'duracion_minutos' => 'nullable|integer|min:15|max:240',
            'motivo'           => 'required|string|max:200',
            'notas'            => 'nullable|string',
        ];
    }
}
```

- [ ] **Step 4: Crear `UpdateCitaEstadoRequest.php`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCitaEstadoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'estado' => 'required|in:pendiente,confirmada,realizada,cancelada,no_presentado',
            'notas'  => 'nullable|string',
        ];
    }
}
```

- [ ] **Step 5: Crear `StoreHistorialRequest.php`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreHistorialRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'dia'                  => ['required', 'integer', 'min:1', 'max:31'],
            'mes'                  => ['required', 'integer', 'min:1', 'max:12'],
            'anio'                 => ['required', 'integer', 'min:1900', 'max:2100'],
            'signo'                => 'nullable|string|max:50',
            'diagnostico'          => 'required|string',
            'num_sesiones'         => 'nullable|integer|min:1',
            'importe'              => 'nullable|numeric|min:0',
            'tratamiento_realizado'=> 'nullable|string',
            'recibo'               => 'nullable|string|max:50',
            'debe'                 => 'nullable|numeric|min:0',
            'haber'                => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (!checkdate($this->mes, $this->dia, $this->anio)) {
                $v->errors()->add('dia', 'La fecha introducida no es válida.');
            }
        });
    }
}
```

- [ ] **Step 6: Crear `UpdateHistorialRequest.php`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateHistorialRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'dia'                  => ['required', 'integer', 'min:1', 'max:31'],
            'mes'                  => ['required', 'integer', 'min:1', 'max:12'],
            'anio'                 => ['required', 'integer', 'min:1900', 'max:2100'],
            'signo'                => 'nullable|string|max:50',
            'diagnostico'          => 'required|string',
            'num_sesiones'         => 'nullable|integer|min:1',
            'importe'              => 'nullable|numeric|min:0',
            'tratamiento_realizado'=> 'nullable|string',
            'recibo'               => 'nullable|string|max:50',
            'debe'                 => 'nullable|numeric|min:0',
            'haber'                => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (!checkdate($this->mes, $this->dia, $this->anio)) {
                $v->errors()->add('dia', 'La fecha introducida no es válida.');
            }
        });
    }
}
```

- [ ] **Step 7: Crear `ContactoRequest.php`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'   => 'required|string|max:100',
            'contacto' => 'required|string|max:150',
            'asunto'   => 'nullable|string|max:100',
            'mensaje'  => 'required|string|max:2000',
        ];
    }
}
```

- [ ] **Step 8: Commit**

```bash
git add app/Http/Requests/
git commit -m "refactor: crear 7 FormRequests (Clientes, Citas, Historial, Contacto)"
```

---

## Task 9: Actualizar controladores web para usar FormRequests

**Files:**
- Modify: `app/Http/Controllers/ClienteController.php`
- Modify: `app/Http/Controllers/CitaController.php`
- Modify: `app/Http/Controllers/HistorialController.php`
- Modify: `app/Http/Controllers/ContactoController.php`

- [ ] **Step 1: Actualizar `ClienteController.php`**

Cambiar imports en la cabecera:
```php
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
```

Reemplazar el método `store()`:
```php
public function store(StoreClienteRequest $request)
{
    DB::transaction(function () use ($request) {
        $cliente = Cliente::create($request->validated());
        $cliente->num_filiacion = $cliente->generarNumFiliacion();
        $cliente->save();

        $dientes = array_merge(Dentadura::DIENTES_SUPERIORES, Dentadura::DIENTES_INFERIORES);
        foreach ($dientes as $num) {
            Dentadura::create([
                'cliente_id'          => $cliente->id,
                'num_diente'          => (string) $num,
                'estado_pieza'        => 'presente',
                'fecha_actualizacion' => now(),
            ]);
        }
    });

    return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
}
```

Reemplazar el método `update()`:
```php
public function update(UpdateClienteRequest $request, Cliente $cliente)
{
    $cliente->update($request->validated());
    return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente actualizado.');
}
```

- [ ] **Step 2: Actualizar `CitaController.php`**

Añadir imports:
```php
use App\Http\Requests\StoreCitaRequest;
```

Reemplazar el método `store()`:
```php
public function store(StoreCitaRequest $request)
{
    $data = $request->validated();

    if (auth()->user()->isCliente()) {
        $clienteRecord = auth()->user()->cliente;
        if (!$clienteRecord) abort(422, 'No tienes ficha de paciente.');
        $data['cliente_id'] = $clienteRecord->id;
        $data['estado']     = 'pendiente';
    } else {
        $data['estado'] = 'confirmada';
    }

    $data['gestor_id'] = auth()->user()->isGestor() ? auth()->id() : null;

    $cita = Cita::create($data);
    $this->notificaciones->citaCreada($cita);

    if ($request->expectsJson()) {
        return response()->json(['ok' => true, 'cita' => $cita->load('cliente')]);
    }

    return back()->with('success', 'Cita registrada correctamente.');
}
```

- [ ] **Step 3: Actualizar `HistorialController.php`**

Añadir imports:
```php
use App\Http\Requests\StoreHistorialRequest;
use App\Http\Requests\UpdateHistorialRequest;
```

Reemplazar `store()`:
```php
public function store(StoreHistorialRequest $request, Cliente $cliente)
{
    $data = $request->validated();
    $data['cliente_id'] = $cliente->id;
    $data['gestor_id']  = auth()->id();
    HistorialClinico::create($data);
    return back()->with('success', 'Registro añadido al historial.');
}
```

Reemplazar `update()`:
```php
public function update(UpdateHistorialRequest $request, HistorialClinico $historial)
{
    $historial->update($request->validated());
    return back()->with('success', 'Registro actualizado.');
}
```

- [ ] **Step 4: Actualizar `ContactoController.php`**

```php
use App\Http\Requests\ContactoRequest;

public function send(ContactoRequest $request)
{
    $data = $request->validated();

    try {
        Mail::raw(
            "Mensaje de contacto web\n\nNombre: {$data['nombre']}\nContacto: {$data['contacto']}\nAsunto: {$data['asunto']}\n\nMensaje:\n{$data['mensaje']}",
            function ($m) use ($data) {
                $m->to(config('mail.from.address'))
                  ->subject("Web: {$data['asunto']} — {$data['nombre']}");
            }
        );
    } catch (\Throwable $e) {
        logger()->error('Error enviando email de contacto: ' . $e->getMessage());
    }

    return back()->with('contacto_ok', true);
}
```

- [ ] **Step 5: Ejecutar tests**

```bash
php vendor/bin/phpunit --stop-on-failure
```

Expected: todos los tests pasan.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/ClienteController.php \
        app/Http/Controllers/CitaController.php \
        app/Http/Controllers/HistorialController.php \
        app/Http/Controllers/ContactoController.php
git commit -m "refactor: usar FormRequests en ClienteController, CitaController, HistorialController, ContactoController"
```

---

## Task 10: Crear DentaduraService y actualizar ClienteController

**Files:**
- Create: `app/Services/DentaduraService.php`
- Modify: `app/Http/Controllers/ClienteController.php`

- [ ] **Step 1: Crear `app/Services/DentaduraService.php`**

```php
<?php

namespace App\Services;

use App\Models\Dentadura;

class DentaduraService
{
    public function normalizarEstadoCara(?string $estado): ?string
    {
        return ($estado === null || $estado === 'sano') ? null : $estado;
    }

    public function normalizarEstadoPieza(?string $estado): ?string
    {
        return ($estado === null || $estado === 'presente') ? null : $estado;
    }

    public function actualizarDiente(Dentadura $diente, array $datos): void
    {
        $campos = ['fecha_actualizacion' => now()];

        if (isset($datos['estado_pieza'])) {
            $campos['estado_pieza'] = $datos['estado_pieza'];
        }

        foreach (['cara_vestibular', 'cara_lingual', 'cara_mesial', 'cara_distal', 'cara_oclusal'] as $cara) {
            if (array_key_exists($cara, $datos)) {
                $campos[$cara] = $this->normalizarEstadoCara($datos[$cara]);
            }
        }

        if (array_key_exists('notas', $datos)) {
            $campos['notas'] = $datos['notas'];
        }

        $diente->update($campos);
    }
}
```

- [ ] **Step 2: Actualizar `ClienteController` para inyectar y usar `DentaduraService`**

Añadir import:
```php
use App\Services\DentaduraService;
```

Añadir inyección en el constructor:
```php
public function __construct(private DentaduraService $dentaduraService)
{
    $this->middleware('auth');
    $this->middleware('gestor')->except(['show']);
}
```

Reemplazar el método `actualizarDentadura()`:
```php
public function actualizarDentadura(Request $request, Cliente $cliente)
{
    if (!auth()->user()->isGestor()) abort(403);

    $estadosPieza = implode(',', array_keys(Dentadura::ESTADOS_PIEZA));
    $estadosCara  = 'sano,' . implode(',', array_keys(Dentadura::ESTADOS_CARA));

    $data = $request->validate([
        'dientes'                    => 'required|array',
        'dientes.*.num_diente'       => 'required|string',
        'dientes.*.estado_pieza'     => "nullable|in:{$estadosPieza}",
        'dientes.*.cara_vestibular'  => "nullable|in:{$estadosCara}",
        'dientes.*.cara_lingual'     => "nullable|in:{$estadosCara}",
        'dientes.*.cara_mesial'      => "nullable|in:{$estadosCara}",
        'dientes.*.cara_distal'      => "nullable|in:{$estadosCara}",
        'dientes.*.cara_oclusal'     => "nullable|in:{$estadosCara}",
        'dientes.*.notas'            => 'nullable|string',
    ]);

    foreach ($data['dientes'] as $dienteData) {
        $diente = Dentadura::firstOrCreate(
            ['cliente_id' => $cliente->id, 'num_diente' => $dienteData['num_diente']],
            ['fecha_actualizacion' => now()]
        );
        $this->dentaduraService->actualizarDiente($diente, $dienteData);
    }

    return response()->json(['ok' => true, 'message' => 'Dentadura actualizada.']);
}
```

Eliminar el método privado `normalizarEstadoCara()` (ya no existe en el controlador).

- [ ] **Step 3: Ejecutar tests**

```bash
php vendor/bin/phpunit --stop-on-failure
```

- [ ] **Step 4: Commit**

```bash
git add app/Services/DentaduraService.php app/Http/Controllers/ClienteController.php
git commit -m "refactor: crear DentaduraService y actualizar ClienteController"
```

---

## Task 11: Crear CitaService y actualizar CitaController

**Files:**
- Create: `app/Services/CitaService.php`
- Modify: `app/Http/Controllers/CitaController.php`

- [ ] **Step 1: Crear `app/Services/CitaService.php`**

```php
<?php

namespace App\Services;

use App\Models\Cita;
use Illuminate\Support\Collection;

class CitaService
{
    public function __construct(private NotificacionService $notificaciones) {}

    public function crearCita(array $datos, ?int $gestorId = null): Cita
    {
        $cita = Cita::create($datos);
        $this->notificaciones->citaCreada($cita);
        return $cita;
    }

    public function actualizarEstado(Cita $cita, string $estado, ?string $notas = null): Cita
    {
        $campos = ['estado' => $estado];
        if ($notas !== null) {
            $campos['notas'] = $notas;
        }
        $cita->update($campos);
        return $cita->fresh();
    }

    public function citasDelMes(int $year, int $month): Collection
    {
        return Cita::with('cliente:id,apellidos,nombre')
            ->delMes($year, $month)
            ->get()
            ->map(fn($c) => [
                'id'             => $c->id,
                'title'          => $c->cliente->nombre_completo . ' — ' . $c->motivo,
                'start'          => $c->fecha_hora->toIso8601String(),
                'end'            => $c->fecha_hora->addMinutes($c->duracion_minutos)->toIso8601String(),
                'color'          => $c->estado_color,
                'estado'         => $c->estado,
                'cliente_id'     => $c->cliente_id,
                'cliente_nombre' => $c->cliente->nombre_completo,
            ]);
    }
}
```

- [ ] **Step 2: Actualizar `CitaController.php`**

Añadir import:
```php
use App\Services\CitaService;
```

Actualizar constructor:
```php
public function __construct(
    private NotificacionService $notificaciones,
    private CitaService $citaService
) {
    $this->middleware('auth');
}
```

Reemplazar `store()`:
```php
public function store(StoreCitaRequest $request)
{
    $data = $request->validated();

    if (auth()->user()->isCliente()) {
        $clienteRecord = auth()->user()->cliente;
        if (!$clienteRecord) abort(422, 'No tienes ficha de paciente.');
        $data['cliente_id'] = $clienteRecord->id;
        $data['estado']     = 'pendiente';
    } else {
        $data['estado']    = 'confirmada';
        $data['gestor_id'] = auth()->id();
    }

    $cita = $this->citaService->crearCita($data);

    if ($request->expectsJson()) {
        return response()->json(['ok' => true, 'cita' => $cita->load('cliente')]);
    }

    return back()->with('success', 'Cita registrada correctamente.');
}
```

Reemplazar `apiMes()`:
```php
public function apiMes(Request $request)
{
    $year  = $request->get('year',  now()->year);
    $month = $request->get('month', now()->month);
    return response()->json($this->citaService->citasDelMes($year, $month));
}
```

- [ ] **Step 3: Ejecutar tests**

```bash
php vendor/bin/phpunit --stop-on-failure
```

- [ ] **Step 4: Commit**

```bash
git add app/Services/CitaService.php app/Http/Controllers/CitaController.php
git commit -m "refactor: crear CitaService y actualizar CitaController"
```

---

## Task 12: Crear middleware EsGestorOPropietario

**Files:**
- Create: `app/Http/Middleware/EsGestorOPropietario.php`
- Modify: `bootstrap/app.php`

- [ ] **Step 1: Crear `app/Http/Middleware/EsGestorOPropietario.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EsGestorOPropietario
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        if ($user->isGestor()) {
            return $next($request);
        }

        $clienteId = $request->route('id') ?? $request->route('cliente')?->id ?? $request->input('cliente_id');

        if ($clienteId && $user->cliente?->id === (int) $clienteId) {
            return $next($request);
        }

        return response()->json(['error' => 'No autorizado.'], 403);
    }
}
```

- [ ] **Step 2: Registrar en `bootstrap/app.php`**

Localizar el bloque `->withMiddleware(...)` y añadir el alias:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'gestor'              => \App\Http\Middleware\EsGestor::class,
        'gestor.o.propietario'=> \App\Http\Middleware\EsGestorOPropietario::class,
    ]);
})
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Middleware/EsGestorOPropietario.php bootstrap/app.php
git commit -m "refactor: crear middleware EsGestorOPropietario"
```

---

## Task 13: Crear 4 controladores Api/* y actualizar routes/api.php

**Files:**
- Create: `app/Http/Controllers/Api/AuthApiController.php`
- Create: `app/Http/Controllers/Api/ClienteApiController.php`
- Create: `app/Http/Controllers/Api/CitaApiController.php`
- Create: `app/Http/Controllers/Api/DispositivoApiController.php`
- Modify: `routes/api.php`
- Delete: `app/Http/Controllers/ApiController.php`

- [ ] **Step 1: Crear `Api/AuthApiController.php`**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($data)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        if (!auth()->user()->activo) {
            Auth::logout();
            return response()->json(['message' => 'Esta cuenta está desactivada.'], 403);
        }

        $user  = Auth::user();
        $token = $user->createToken('app-movil')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['ok' => true]);
    }
}
```

- [ ] **Step 2: Crear `Api/DispositivoApiController.php`**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DispositivoPush;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DispositivoApiController extends Controller
{
    public function registrarToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token_fcm'  => 'required|string',
            'plataforma' => 'nullable|in:android,ios',
        ]);

        DispositivoPush::updateOrCreate(
            ['user_id' => auth()->id(), 'token_fcm' => $data['token_fcm']],
            ['plataforma' => $data['plataforma'] ?? 'android']
        );

        return response()->json(['ok' => true]);
    }
}
```

- [ ] **Step 3: Crear `Api/ClienteApiController.php`**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clientes = Cliente::query()
            ->when($request->buscar, fn($q) => $q->buscar($request->buscar))
            ->orderBy('apellidos')
            ->paginate(50);

        return response()->json($clientes);
    }

    public function show(Request $request, Cliente $cliente): JsonResponse
    {
        return response()->json($cliente->load(['historialClinico', 'dentadura', 'citasFuturas']));
    }

    public function historial(Request $request, Cliente $cliente): JsonResponse
    {
        $historial = $cliente->historialClinico()->paginate(20);
        return response()->json($historial);
    }
}
```

- [ ] **Step 4: Crear `Api/CitaApiController.php`**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCitaRequest;
use App\Http\Requests\UpdateCitaEstadoRequest;
use App\Models\Cita;
use App\Services\CitaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CitaApiController extends Controller
{
    public function __construct(private CitaService $citaService) {}

    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $cliente = $user->cliente;

        if (!$cliente) {
            return response()->json(['message' => 'No tienes ficha de cliente.'], 404);
        }

        $proximas   = $cliente->citasFuturas()->with('gestor:id,name')->get();
        $anteriores = $cliente->citas()
                              ->where('fecha_hora', '<', now())
                              ->orderByDesc('fecha_hora')
                              ->limit(20)
                              ->get();

        return response()->json(compact('proximas', 'anteriores'));
    }

    public function store(StoreCitaRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($user->isCliente()) {
            $cliente = $user->cliente;
            if (!$cliente) {
                return response()->json(['message' => 'No tienes ficha de paciente.'], 422);
            }
            $data['cliente_id'] = $cliente->id;
            $data['estado']     = 'pendiente';
        } else {
            $data['estado']    = 'confirmada';
            $data['gestor_id'] = $user->id;
        }

        $cita = $this->citaService->crearCita($data);
        return response()->json($cita->load('cliente'), 201);
    }

    public function actualizarEstado(UpdateCitaEstadoRequest $request, Cita $cita): JsonResponse
    {
        $data = $request->validated();
        $cita = $this->citaService->actualizarEstado($cita, $data['estado'], $data['notas'] ?? null);
        return response()->json($cita->load('cliente'));
    }

    public function mes(Request $request): JsonResponse
    {
        $year  = $request->get('year',  now()->year);
        $month = $request->get('month', now()->month);
        return response()->json($this->citaService->citasDelMes($year, $month));
    }
}
```

- [ ] **Step 5: Actualizar `routes/api.php`**

```php
<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CitaApiController;
use App\Http\Controllers\Api\ClienteApiController;
use App\Http\Controllers\Api\DispositivoApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::post('/dispositivo/token', [DispositivoApiController::class, 'registrarToken']);

    Route::get('/mis-citas', [CitaApiController::class, 'index']);
    Route::get('/citas/mes', [CitaApiController::class, 'mes']);
    Route::post('/citas',    [CitaApiController::class, 'store']);

    Route::middleware('gestor')->group(function () {
        Route::get('/clientes',                    [ClienteApiController::class, 'index']);
        Route::get('/clientes/{cliente}',          [ClienteApiController::class, 'show']);
        Route::get('/clientes/{cliente}/historial',[ClienteApiController::class, 'historial']);
        Route::patch('/citas/{cita}/estado',       [CitaApiController::class, 'actualizarEstado']);
    });
});
```

- [ ] **Step 6: Eliminar `ApiController.php`**

```bash
rm app/Http/Controllers/ApiController.php
```

- [ ] **Step 7: Ejecutar tests**

```bash
php vendor/bin/phpunit --stop-on-failure
```

Expected: los tests de API pueden fallar por imports obsoletos — se corrigen en Task 14.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Api/ routes/api.php
git rm app/Http/Controllers/ApiController.php
git commit -m "refactor: dividir ApiController en 4 controladores Api/* y actualizar routes/api.php"
```

---

## Task 14: Actualizar tests existentes y añadir tests de servicios y requests

**Files:**
- Modify: `tests/Feature/Api/` (actualizar imports)
- Create: `tests/Unit/Services/DentaduraServiceTest.php`
- Create: `tests/Unit/Services/CitaServiceTest.php`
- Create: `tests/Unit/Requests/StoreClienteRequestTest.php`
- Create: `tests/Unit/Requests/StoreHistorialRequestTest.php`

- [ ] **Step 1: Actualizar imports en tests de API**

Buscar todos los tests que referencien `ApiController`:

```bash
grep -rn "ApiController" tests/
```

Para cada archivo encontrado, reemplazar el import de `ApiController` por los nuevos controladores según el método que testea:
- Métodos de auth → `AuthApiController`
- Métodos de citas → `CitaApiController`
- Métodos de clientes → `ClienteApiController`
- Token FCM → `DispositivoApiController`

Los tests no deberían referenciar controladores directamente (usan rutas HTTP), pero si hay alguna referencia directa, actualizarla.

- [ ] **Step 2: Crear `tests/Unit/Services/DentaduraServiceTest.php`**

```php
<?php

namespace Tests\Unit\Services;

use App\Models\Dentadura;
use App\Services\DentaduraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DentaduraServiceTest extends TestCase
{
    use RefreshDatabase;

    private DentaduraService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DentaduraService();
    }

    public function test_normalizar_estado_cara_convierte_sano_a_null(): void
    {
        $this->assertNull($this->service->normalizarEstadoCara('sano'));
    }

    public function test_normalizar_estado_cara_convierte_null_a_null(): void
    {
        $this->assertNull($this->service->normalizarEstadoCara(null));
    }

    public function test_normalizar_estado_cara_preserva_otros_estados(): void
    {
        $this->assertSame('caries',    $this->service->normalizarEstadoCara('caries'));
        $this->assertSame('obturacion',$this->service->normalizarEstadoCara('obturacion'));
        $this->assertSame('fractura',  $this->service->normalizarEstadoCara('fractura'));
    }

    public function test_normalizar_estado_pieza_convierte_presente_a_null(): void
    {
        $this->assertNull($this->service->normalizarEstadoPieza('presente'));
        $this->assertNull($this->service->normalizarEstadoPieza(null));
    }

    public function test_normalizar_estado_pieza_preserva_otros_estados(): void
    {
        $this->assertSame('ausente',   $this->service->normalizarEstadoPieza('ausente'));
        $this->assertSame('corona',    $this->service->normalizarEstadoPieza('corona'));
        $this->assertSame('implante',  $this->service->normalizarEstadoPieza('implante'));
    }

    public function test_actualizar_diente_normaliza_caras_y_guarda(): void
    {
        $cliente  = \App\Models\Cliente::factory()->create();
        $diente   = Dentadura::factory()->create([
            'cliente_id'     => $cliente->id,
            'num_diente'     => '16',
            'cara_vestibular'=> null,
        ]);

        $this->service->actualizarDiente($diente, [
            'cara_vestibular' => 'sano',
            'cara_lingual'    => 'caries',
        ]);

        $diente->refresh();
        $this->assertNull($diente->cara_vestibular);
        $this->assertSame('caries', $diente->cara_lingual);
    }
}
```

- [ ] **Step 3: Crear `tests/Unit/Services/CitaServiceTest.php`**

```php
<?php

namespace Tests\Unit\Services;

use App\Models\Cita;
use App\Models\Cliente;
use App\Services\CitaService;
use App\Services\NotificacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CitaServiceTest extends TestCase
{
    use RefreshDatabase;

    private CitaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $notificaciones = Mockery::mock(NotificacionService::class);
        $notificaciones->shouldReceive('citaCreada')->andReturnNull();
        $this->service = new CitaService($notificaciones);
    }

    public function test_crear_cita_persiste_en_base_de_datos(): void
    {
        $cliente = Cliente::factory()->create();

        $cita = $this->service->crearCita([
            'cliente_id'       => $cliente->id,
            'fecha_hora'       => now()->addDay()->toDateTimeString(),
            'duracion_minutos' => 30,
            'motivo'           => 'Revisión',
            'estado'           => 'confirmada',
        ]);

        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'motivo' => 'Revisión']);
    }

    public function test_actualizar_estado_cambia_estado(): void
    {
        $cita = Cita::factory()->create(['estado' => 'pendiente']);

        $actualizada = $this->service->actualizarEstado($cita, 'confirmada');

        $this->assertSame('confirmada', $actualizada->estado);
        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'estado' => 'confirmada']);
    }

    public function test_citas_del_mes_retorna_formato_fullcalendar(): void
    {
        $cliente = Cliente::factory()->create();
        Cita::factory()->create([
            'cliente_id' => $cliente->id,
            'fecha_hora' => now()->startOfMonth()->addDays(5),
            'motivo'     => 'Test',
            'estado'     => 'confirmada',
        ]);

        $resultado = $this->service->citasDelMes(now()->year, now()->month);

        $this->assertCount(1, $resultado);
        $this->assertArrayHasKey('title', $resultado->first());
        $this->assertArrayHasKey('start', $resultado->first());
        $this->assertArrayHasKey('color', $resultado->first());
    }
}
```

- [ ] **Step 4: Crear `tests/Unit/Requests/StoreClienteRequestTest.php`**

```php
<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreClienteRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreClienteRequestTest extends TestCase
{
    private function validate(array $data): bool
    {
        $request = new StoreClienteRequest();
        $validator = Validator::make($data, $request->rules());
        return $validator->passes();
    }

    public function test_valida_datos_minimos_correctos(): void
    {
        $this->assertTrue($this->validate([
            'apellidos' => 'García López',
            'nombre'    => 'Juan',
        ]));
    }

    public function test_falla_sin_apellidos(): void
    {
        $this->assertFalse($this->validate(['nombre' => 'Juan']));
    }

    public function test_falla_sin_nombre(): void
    {
        $this->assertFalse($this->validate(['apellidos' => 'García']));
    }

    public function test_falla_con_edad_negativa(): void
    {
        $this->assertFalse($this->validate([
            'apellidos' => 'García',
            'nombre'    => 'Juan',
            'edad'      => -1,
        ]));
    }

    public function test_acepta_todos_los_campos_opcionales(): void
    {
        $this->assertTrue($this->validate([
            'apellidos'     => 'García',
            'nombre'        => 'Juan',
            'edad'          => 35,
            'profesion'     => 'Médico',
            'direccion'     => 'Calle Mayor 1',
            'cp'            => '30170',
            'telefono'      => '600000000',
            'observaciones' => 'Alérgico a penicilina',
        ]));
    }
}
```

- [ ] **Step 5: Crear `tests/Unit/Requests/StoreHistorialRequestTest.php`**

```php
<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreHistorialRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreHistorialRequestTest extends TestCase
{
    private function makeRequest(array $data): StoreHistorialRequest
    {
        $request = StoreHistorialRequest::create('/', 'POST', $data);
        return StoreHistorialRequest::createFrom($request);
    }

    public function test_valida_fecha_correcta(): void
    {
        $req       = $this->makeRequest(['dia' => 15, 'mes' => 6, 'anio' => 2025]);
        $validator = Validator::make($req->all(), $req->rules());
        $validator->after(fn($v) => $req->withValidator($v));
        $this->assertTrue($validator->passes());
    }

    public function test_rechaza_fecha_imposible(): void
    {
        // 31 de febrero no existe
        $req       = $this->makeRequest(['dia' => 31, 'mes' => 2, 'anio' => 2025, 'diagnostico' => 'Test']);
        $validator = Validator::make($req->all(), $req->rules());
        $req->withValidator($validator);
        $validator->validate();

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('dia'));
    }

    public function test_requiere_diagnostico(): void
    {
        $req       = $this->makeRequest(['dia' => 15, 'mes' => 6, 'anio' => 2025]);
        $validator = Validator::make($req->all(), $req->rules());
        $this->assertFalse($validator->passes());
    }
}
```

- [ ] **Step 6: Ejecutar la suite completa**

```bash
php vendor/bin/phpunit
```

Expected: 0 failures, 0 errors. Si hay fallos en tests de API por cambios de namespace, corregirlos ahora.

- [ ] **Step 7: Commit final**

```bash
git add tests/
git commit -m "test: actualizar tests existentes y añadir DentaduraServiceTest, CitaServiceTest, StoreClienteRequestTest, StoreHistorialRequestTest"
```

---

## Merge a develop

- [ ] **Merge y push**

```bash
git checkout develop
git merge --no-ff feature/refactoring-completo
git push origin develop
git branch -d feature/refactoring-completo
git push origin --delete feature/refactoring-completo
```

Expected: `develop` actualizado con todos los cambios del refactoring.
