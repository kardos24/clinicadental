# Spec: Componentes del perfil de cliente

**Fecha:** 2026-05-08
**Rama:** `feature/odontograma-anatomico`

---

## Objetivo

Refactorizar la vista de perfil de cliente (`clientes/show.blade.php`) para que cada sección sea un componente Blade anónimo independiente con `@props` declaradas. Organizar todos los componentes dentro de `resources/views/components/` con una estructura jerárquica por dominio.

---

## Estructura final de archivos

```
resources/views/components/
└── cliente/
    ├── datos-personales.blade.php        <x-cliente.datos-personales>
    ├── historial.blade.php               <x-cliente.historial>
    ├── citas.blade.php                   <x-cliente.citas>
    ├── modales.blade.php                 <x-cliente.modales>
    └── odontograma/
        ├── oclusal/
        │   ├── index.blade.php           <x-cliente.odontograma.oclusal>
        │   └── diente.blade.php          <x-cliente.odontograma.oclusal.diente>
        └── anatomico/
            ├── index.blade.php           <x-cliente.odontograma.anatomico>
            └── diente.blade.php          <x-cliente.odontograma.anatomico.diente>
```

### Archivos eliminados tras la migración

| Archivo original | Motivo |
|---|---|
| `resources/views/clientes/partials/_datos-personales.blade.php` | Reemplazado por `cliente/datos-personales.blade.php` |
| `resources/views/clientes/partials/_historial.blade.php` | Reemplazado por `cliente/historial.blade.php` |
| `resources/views/clientes/partials/_citas.blade.php` | Reemplazado por `cliente/citas.blade.php` |
| `resources/views/clientes/partials/_modales.blade.php` | Reemplazado por `cliente/modales.blade.php` |
| `resources/views/clientes/partials/_odontograma.blade.php` | Dividido en `oclusal/index` + `anatomico/index` |
| `resources/views/components/_diente-inner.blade.php` | Reemplazado por `oclusal/diente.blade.php` |
| `resources/views/components/diente-cara.blade.php` | Reemplazado por `anatomico/diente.blade.php` |
| `resources/views/components/odontograma-bucal.blade.php` | Reemplazado por `anatomico/index.blade.php` |

---

## Props de cada componente

### `<x-cliente.datos-personales>`
```blade
@props(['cliente'])
```
Origen: `clientes/partials/_datos-personales.blade.php` (sin cambios de lógica).

### `<x-cliente.historial>`
```blade
@props(['cliente', 'historial'])
```
Origen: `clientes/partials/_historial.blade.php` (sin cambios de lógica).

### `<x-cliente.citas>`
```blade
@props(['cliente', 'citas'])
```
Origen: `clientes/partials/_citas.blade.php` (sin cambios de lógica).

### `<x-cliente.modales>`
```blade
@props(['cliente', 'dentadura'])
```
Origen: `clientes/partials/_modales.blade.php` (sin cambios de lógica).

### `<x-cliente.odontograma.oclusal>`
```blade
@props(['cliente', 'dentadura'])
```
Origen: primera mitad de `clientes/partials/_odontograma.blade.php`.
Contiene: card oclusal + leyendas + dos filas de dientes + `@push('styles')` + `@push('scripts')` con `window.dentaduraData/Url`.
Usa `<x-cliente.odontograma.oclusal.diente>` en lugar del antiguo `@include('components._diente-inner')`.

### `<x-cliente.odontograma.oclusal.diente>`
```blade
@props(['t', 'num'])
```
Origen: `components/_diente-inner.blade.php`.
Cambia de `@include` con variables ad-hoc a componente con props declaradas.

### `<x-cliente.odontograma.anatomico>`
```blade
@props(['cliente', 'dentadura', 'modoEdicion' => true])
```
Origen: `components/odontograma-bucal.blade.php` + card wrapper de `_odontograma.blade.php`.
El card wrapper (`<div class="card odontograma" id="odontograma-bucal-card">`) se incorpora dentro del componente para que sea autocontenido.

### `<x-cliente.odontograma.anatomico.diente>`
```blade
@props(['num' => null, 'dentadura' => null, 'arcada' => 'inferior'])
```
Origen: `components/diente-cara.blade.php` (sin cambios de lógica ni SVG).

---

## Vista `show.blade.php` resultante

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

---

## Decisiones de diseño

1. **`index.blade.php` como componente raíz de carpeta**: Laravel resuelve `oclusal/index.blade.php` como `<x-cliente.odontograma.oclusal>` — mismo mecanismo que el `index.blade.php` estándar de directorios.

2. **`@push` dentro de componentes**: Los `@push('styles')` y `@push('scripts')` funcionan correctamente dentro de componentes anónimos — Blade los propaga al stack del layout padre.

3. **Card wrapper en `anatomico`**: El `<div class="card odontograma" id="odontograma-bucal-card">` que envolvía el `<x-odontograma-bucal>` en el partial se incorpora dentro de `anatomico/index.blade.php`. Así el componente es completamente autocontenido, igual que el oclusal.

4. **Sin cambios de lógica**: Ningún PHP, CSS ni JS se modifica — es una reorganización pura de ficheros y sintaxis de inclusión.

5. **Carpeta `partials/` eliminada**: Una vez migrados todos los componentes, la carpeta `clientes/partials/` se borra completamente.

---

## Orden de implementación

1. Crear carpetas: `components/cliente/`, `components/cliente/odontograma/oclusal/`, `components/cliente/odontograma/anatomico/`
2. Crear `cliente/datos-personales.blade.php`
3. Crear `cliente/historial.blade.php`
4. Crear `cliente/citas.blade.php`
5. Crear `cliente/modales.blade.php`
6. Crear `odontograma/oclusal/diente.blade.php` (desde `_diente-inner`)
7. Crear `odontograma/oclusal/index.blade.php` (desde primera mitad de `_odontograma`)
8. Crear `odontograma/anatomico/diente.blade.php` (desde `diente-cara`)
9. Crear `odontograma/anatomico/index.blade.php` (desde `odontograma-bucal` + card wrapper)
10. Actualizar `show.blade.php`
11. Eliminar archivos obsoletos (`partials/`, `_diente-inner`, `diente-cara`, `odontograma-bucal`)
12. Verificar en navegador (http://127.0.0.1:8080)
