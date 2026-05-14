# Test Suite Design — Clínica Dental Mula

**Date:** 2026-05-03
**Branch:** feature/odontograma-anatomico

## Objetivo

Crear una suite de tests completa que cubra todos los casos de uso de la aplicación: autenticación web, gestión de clientes/citas/historial clínico, odontograma dental y la API REST para la app móvil.

---

## 1. Infraestructura

### Base de datos

- **Motor:** MySQL (misma instancia Laragon, base de datos separada `clinica_mula_test`)
- **Estrategia:** `RefreshDatabase` en todos los Feature tests — las migraciones reales se ejecutan en cada suite
- **Configuración:** `phpunit.xml` apunta a `DB_CONNECTION=mysql`, `DB_DATABASE=clinica_mula_test`; `.env.testing` contiene las credenciales

### Estructura de carpetas

```
tests/
  Feature/
    Auth/
      LoginTest.php
      RegisterTest.php
    Clientes/
      ClienteCrudTest.php
      DentaduraTest.php
    Citas/
      CitaTest.php
    Historial/
      HistorialTest.php
    Api/
      ApiAuthTest.php
      ApiCitasTest.php
      ApiClientesTest.php
  Unit/
    Models/
      DentaduraModelTest.php
      HistorialClinicoModelTest.php
      ClienteModelTest.php
      CitaModelTest.php
```

### Factories

| Factory | Campos relevantes |
|---------|------------------|
| `UserFactory` | `name`, `email`, `password` (hashed), `role` (`gestor`/`cliente`), `activo=true` |
| `ClienteFactory` | `apellidos`, `nombre`, `num_filiacion` secuencial (`MUL-00001`…), `telefono` nullable |
| `CitaFactory` | `fecha_hora` futura (+1 día), `estado=pendiente`, `motivo`, relaciones `cliente_id`/`gestor_id` |
| `HistorialClinicoFactory` | `dia`, `mes`, `anio` actuales, `diagnostico`, `debe=100`, `haber=0` (saldo calculado en boot) |
| `DentaduraFactory` | `num_diente` (ej. `'16'`), `estado_pieza='presente'`, todas las caras null |

Los Unit tests de modelos instancian los modelos directamente con `new Modelo([...])` o usando `make()` — sin tocar la BD donde sea posible.

---

## 2. Feature Tests

### 2.1 Auth

**`LoginTest`**
- Login correcto como gestor → redirección a `/dashboard`
- Login correcto como cliente → redirección a `/mis-citas`
- Login con credenciales incorrectas → vuelve al formulario con error en campo `email`
- Login con cuenta `activo=false` → vuelve al formulario con mensaje "cuenta desactivada"
- Usuario ya autenticado que accede a `/login` → redirección (guest middleware)

**`RegisterTest`**
- Register válido → crea usuario con `role='cliente'`, redirige a `/mis-citas`
- Register con email duplicado → error de validación
- Register con password sin confirmar → error de validación
- Register con password < 8 caracteres → error de validación

---

### 2.2 Clientes

**`ClienteCrudTest`**
- Gestor puede listar clientes (`GET /clientes` → 200)
- Listado soporta búsqueda por parámetro `buscar`
- Gestor puede ver formulario de creación (`GET /clientes/create` → 200)
- Gestor crea cliente válido → redirige a índice, existe en BD con `num_filiacion=MUL-00001`
- Al crear cliente se inicializan exactamente 32 registros en `dentadura`
- Gestor puede ver ficha de cualquier cliente (`GET /clientes/{id}` → 200)
- Cliente solo puede ver su propia ficha; intentar ver otra → 403
- Usuario no autenticado → redirige a login
- Gestor puede editar cliente (`POST /clientes/{id}` → redirige con success)
- Gestor puede eliminar cliente (soft delete → sigue en BD, `deleted_at` no null)
- Cliente no puede acceder a CRUD (gestor middleware → 403)
- Validación: `apellidos` y `nombre` son requeridos

**`DentaduraTest`**
- Gestor actualiza estado de un diente → 200 JSON `{ok: true}`
- `'sano'` en cara se guarda como `null` en BD
- Cara con valor real (ej. `'caries'`) se guarda correctamente
- Cliente no puede actualizar dentadura → 403
- Diente inexistente se crea (`updateOrCreate`)
- Validación: `estado_pieza` debe ser un valor de `ESTADOS_PIEZA`

---

### 2.3 Citas

**`CitaTest`**
- Gestor ve el calendario (`GET /citas` → 200)
- Cliente ve sus citas (`GET /mis-citas` → 200 con proximas y anteriores)
- Cliente sin ficha intentando ver mis-citas → 404
- Gestor crea cita → estado `confirmada`, redirige con success
- Cliente crea cita → estado `pendiente`, `cliente_id` forzado al propio cliente
- Cliente intenta crear cita para otro cliente → `cliente_id` se ignora, se asigna el propio
- Cita con `fecha_hora` pasada → falla validación (`after:now`)
- Gestor actualiza estado de cita → 200 o redirección con success
- Gestor elimina cita → ya no existe en BD
- `GET /api/citas/mes` (JSON) devuelve array con claves `id`, `title`, `start`, `end`, `color`

---

### 2.4 Historial Clínico

**`HistorialTest`**
- Gestor crea entrada de historial → redirige con success, saldo = `debe - haber`
- Fecha inválida (día 31 en febrero) → vuelve con error en campo `dia`
- Gestor actualiza entrada → campos persistidos correctamente
- Gestor elimina entrada → ya no existe en BD
- Cliente intenta crear/editar/borrar historial → 403

---

### 2.5 API

**`ApiAuthTest`**
- `POST /api/login` con credenciales correctas → 200 con `token` y `user`
- `POST /api/login` con credenciales incorrectas → 401
- `POST /api/login` con cuenta desactivada → 403
- `POST /api/logout` con token válido → 200 `{ok: true}`, token eliminado
- `POST /api/dispositivo/token` → registra token FCM; segunda llamada con mismo token hace upsert
- Rutas protegidas sin token → 401

**`ApiClientesTest`**
- `GET /api/clientes` como gestor → 200 con paginación
- `GET /api/clientes?buscar=xxx` → filtra resultados
- `GET /api/clientes` como cliente → 403
- `GET /api/clientes/{id}` como gestor → 200 con relaciones cargadas
- `GET /api/clientes/{id}` como el propio cliente → 200
- `GET /api/clientes/{id}` como otro cliente → 403
- `GET /api/clientes/{id}/historial` como gestor → 200 paginado
- `GET /api/clientes/{id}/historial` como propietario → 200
- `GET /api/clientes/{id}/historial` como otro cliente → 403

**`ApiCitasTest`**
- `GET /api/mis-citas` → 200 con claves `proximas` y `anteriores`
- `GET /api/mis-citas` sin ficha de cliente → 404
- `GET /api/citas/mes` como gestor → 200 con citas del mes
- `GET /api/citas/mes` como cliente → 403
- `POST /api/citas` como cliente → 201, `estado=pendiente`
- `POST /api/citas` como gestor → 201, `estado=confirmada`
- `POST /api/citas` con `fecha_hora` pasada → 422
- `PATCH /api/citas/{id}/estado` como gestor → 200 con estado actualizado
- `PATCH /api/citas/{id}/estado` como cliente → 403

---

## 3. Unit Tests

### `DentaduraModelTest`
- `estaPresente()`: `estado_pieza=null` → true; `='presente'` → true; `='ausente'` → false; `='corona'` → false
- `estadoCara('vestibular')`: columna null → `'sano'`; columna `'caries'` → `'caries'`
- `colorCara('vestibular')`: estado `'caries'` → `'#ef4444'`; estado `'sano'` → `'#4ade80'`
- `tieneOclusal()`: num_diente `'16'` (molar) → true; `'11'` (incisivo) → false; `'13'` (canino) → false
- `carasAplicables()`: molar → 5 caras; incisivo → 4 caras
- `tienePatologia()`: todas las caras null → false; una cara `'caries'` → true
- `resumen()`: diente ausente → `'Ausente'`; diente con todo sano → `'Sano'`; vestibular caries → `'Vestibular: Caries'`

### `HistorialClinicoModelTest`
- Boot hook: `debe=150`, `haber=50` → `saldo=100` al guardar
- Boot hook: `debe=0`, `haber=0` → `saldo=0`
- `getFechaFormateadaAttribute()`: dia=5, mes=3, anio=2026 → `'5/3/2026'`

### `ClienteModelTest`
- `generarNumFiliacion()`: id=1 → `'MUL-00001'`; id=99999 → `'MUL-99999'`
- `getNombreCompletoAttribute()`: apellidos=`'García'`, nombre=`'Juan'` → `'García, Juan'`
- `scopeBuscar()`: busca por apellidos, nombre, num_filiacion, teléfono (requiere BD)

### `CitaModelTest`
- `getEstadoLabelAttribute()`: cada uno de los 5 estados → etiqueta española correcta
- `getEstadoColorAttribute()`: cada estado → color hex correcto
- `scopeDelMes()`: solo devuelve citas del mes/año indicado
- `scopeProximas()`: excluye citas pasadas y citas con estado cancelada/realizada/no_presentado

---

## 4. Decisiones de diseño

- **NotificacionService** se mockea en Feature tests que crean citas (evita llamadas reales a Firebase)
- Los Unit tests de `Dentadura`, `Cita`, `HistorialClinico` usan instancias en memoria (`new Modelo([...])`) sin persistir salvo cuando el test requiere relaciones
- `scopeBuscar` y `scopeDelMes` requieren BD, van en Feature o en Unit con `RefreshDatabase`
- No se testean vistas Blade directamente — se comprueba que la respuesta tenga status 200 y contenga texto clave
- La notificación de recordatorio diaria (console.php) queda fuera del alcance de esta suite
