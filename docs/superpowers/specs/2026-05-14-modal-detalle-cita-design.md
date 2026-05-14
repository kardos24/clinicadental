# Modal detalle de cita — spec

**Fecha:** 2026-05-14
**Contexto:** Calendario FullCalendar en `resources/views/citas/calendario.blade.php`

## Problema

`eventClick` abre un `alert()` nativo. No hay interfaz para ver el detalle de una cita existente ni para cambiar su estado o eliminarla. La ruta `PUT /citas/{cita}` y `DELETE /citas/{cita}` ya existen pero no tienen interfaz web.

## Objetivo

Flujo rápido al hacer clic en una cita del calendario:
- Ver los datos clave (paciente, motivo, notas, fecha/hora)
- Cambiar el estado con un solo clic
- Eliminar la cita con confirmación de doble clic
- Sin recarga de página; FullCalendar se actualiza en tiempo real

## Diseño visual

```
┌─────────────────────────────────────────────────┐
│  🟡 Pendiente  ·  Martes 20 mayo · 10:00–10:45  │  ← header con color de estado
│                                               ×  │
├─────────────────────────────────────────────────┤
│  Paciente    García López, Juan              →   │  ← link a ficha (nueva pestaña)
│  Motivo      Revisión semestral                  │
│  Duración    45 min                              │
│  Notas       —                                   │
├─────────────────────────────────────────────────┤
│  CAMBIAR ESTADO                                  │
│  [Confirmar]  [Realizada]  [No presentado]       │
│  [Cancelar]                                      │
├─────────────────────────────────────────────────┤
│                          [🗑 Eliminar cita]      │
└─────────────────────────────────────────────────┘
```

- El estado actual **no** aparece como botón (ya está indicado en el header)
- El header usa el mismo color que el evento en el calendario

## Componentes

### HTML (`calendario.blade.php`)

Nuevo `<div class="modal-backdrop" id="modal-detalle-cita">` con la estructura del diseño visual. Sin `<form>`, toda la comunicación es vía `fetch`.

### JS — abrir modal (`abrirModalDetalle(fcEvent)`)

- Rellena los campos desde `fcEvent.extendedProps` (datos ya en memoria: `cliente_id`, `cliente_nombre`, `estado`, `motivo`) y `fcEvent.start`/`fcEvent.end`
- Sin llamada al servidor al abrir — todo disponible en el evento FullCalendar
- Guarda `fcEvent` en una variable de cierre para usarla en las acciones

### JS — cambiar estado

- Botones generados dinámicamente: los 5 estados menos el estado actual
- Al pulsar: `fetch('/citas/{id}', { method: 'PUT', body: JSON.stringify({ estado }) })`
- Headers: `Content-Type: application/json` + `X-CSRF-TOKEN` desde `<meta name="csrf-token">`
- Mientras carga: botón deshabilitado con texto "Guardando…"
- Al éxito: `fcEvent.setExtendedProp('estado', nuevoEstado)` + `fcEvent.setProp('color', COLORES_ESTADO[nuevoEstado])`, cierra modal

### JS — eliminar

- Primer clic: texto cambia a "¿Seguro? Clic para confirmar" (timeout 3 s para revertir)
- Segundo clic dentro de los 3 s: `DELETE /citas/{id}` con `fetch`
- Al éxito: `fcEvent.remove()`, cierra modal

### Mapa de colores de estado (JS)

```js
const COLORES_ESTADO = {
    pendiente:     '#f97316',
    confirmada:    '#3b82f6',
    realizada:     '#4ade80',
    cancelada:     '#ef4444',
    no_presentado: '#6b7280',
};
```

Coincide con `$estadoColor` del modelo `Cita.php`.

### Labels de estado (JS)

```js
const LABELS_ESTADO = {
    pendiente:     'Pendiente',
    confirmada:    'Confirmada',
    realizada:     'Realizada',
    cancelada:     'Cancelada',
    no_presentado: 'No se presentó',
};
```

## Cambios en backend

**Ninguno.** `CitaController@update` y `CitaController@destroy` ya son correctos y aceptan peticiones JSON.

## Cambios en frontend

Solo `calendario.blade.php`:
1. Nuevo bloque HTML: modal `#modal-detalle-cita`
2. Nuevos estilos CSS: header de color, sección de estado, botón eliminar
3. `eventClick`: sustituir `alert()` por llamada a `abrirModalDetalle(info.event)`
4. Nuevas funciones JS: `abrirModalDetalle`, `cambiarEstadoCita`, `eliminarCita`

## Fuera de alcance

- Edición de fecha/hora, duración o motivo desde este modal (flujo rápido solicitado)
- Notificaciones push al cambiar estado desde el calendario
- Historial de cambios de estado
