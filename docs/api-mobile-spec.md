# Clínica Dental Mula — Especificación API para App Móvil

**Versión del backend:** 1.0  
**Base URL (producción):** `https://tudominio.com/api`  
**Base URL (desarrollo):** `http://127.0.0.1:8080/api`  
**Autenticación:** Laravel Sanctum — Bearer Token  
**Formato:** JSON (`Content-Type: application/json`, `Accept: application/json`)

---

## Índice

1. [Autenticación](#1-autenticación)
2. [Roles y permisos](#2-roles-y-permisos)
3. [Notificaciones push (FCM)](#3-notificaciones-push-fcm)
4. [Pacientes (Clientes)](#4-pacientes-clientes)
5. [Historial clínico](#5-historial-clínico)
6. [Odontograma (Dentadura)](#6-odontograma-dentadura)
7. [Citas](#7-citas)
8. [Modelos de datos](#8-modelos-de-datos)
9. [Catálogos y enumerados](#9-catálogos-y-enumerados)
10. [Gestión de errores](#10-gestión-de-errores)
11. [Reglas de negocio](#11-reglas-de-negocio)

---

## 1. Autenticación

### 1.1 Login

```
POST /api/login
```

No requiere token. Devuelve un Bearer Token de Sanctum válido hasta que se llame a logout.

**Request:**
```json
{
  "email": "admin@clinicamula.es",
  "password": "Admin1234!"
}
```

**Response 200:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "Dr. García",
    "email": "admin@clinicamula.es",
    "role": "gestor"
  }
}
```

**Errores:**
- `401` — Credenciales incorrectas
- `403` — Cuenta desactivada (`activo = false`)
- `422` — Validación (email requerido, password requerido)

El campo `role` determina qué funcionalidades mostrar en la app (ver sección 2).

---

### 1.2 Logout

```
POST /api/logout
Authorization: Bearer {token}
```

Invalida el token actual en servidor. La app debe eliminar el token almacenado localmente.

**Response 200:**
```json
{ "ok": true }
```

---

### 1.3 Gestión del token en la app

- Guardar el token en almacenamiento seguro (Keychain / EncryptedSharedPreferences).
- Incluirlo en todas las peticiones como header: `Authorization: Bearer {token}`.
- Ante una respuesta `401`, redirigir a la pantalla de login y limpiar el token almacenado.
- No hay refresh token — si el token expira o es invalidado, el usuario debe volver a loguearse.

---

## 2. Roles y permisos

El sistema tiene dos roles: **`gestor`** y **`cliente`**.

| Acción | gestor | cliente |
|--------|--------|---------|
| Listar todos los pacientes | ✅ | ❌ |
| Buscar pacientes | ✅ | ❌ |
| Ver perfil de cualquier paciente | ✅ | ❌ |
| Ver su propio perfil | ✅ | ✅ |
| Crear paciente | ✅ | ❌ |
| Editar paciente | ✅ | ❌ |
| Eliminar paciente | ✅ | ❌ |
| Ver historial de cualquier paciente | ✅ | ❌ |
| Ver su propio historial | ✅ | ✅ |
| Crear/editar/eliminar historial | ✅ | ❌ |
| Ver odontograma de cualquier paciente | ✅ | ❌ |
| Ver su propio odontograma | ✅ | ✅ |
| Actualizar odontograma | ✅ | ❌ |
| Ver todas las citas (calendario) | ✅ | ❌ |
| Ver sus propias citas | ✅ | ✅ |
| Crear cita (para cualquier paciente) | ✅ | ❌ |
| Crear cita (para sí mismo) | ❌ | ✅ |
| Editar cita completa | ✅ | ❌ |
| Cambiar estado de cita | ✅ | ❌ |
| Eliminar cita | ✅ | ❌ |

Un usuario `cliente` solo puede acceder a sus propios datos: su ficha, historial, odontograma y citas. Intentar acceder a datos de otro paciente devuelve `403`.

---

## 3. Notificaciones push (FCM)

### 3.1 Registrar token FCM

Debe llamarse tras el login y cada vez que el sistema operativo entregue un nuevo token FCM.

```
POST /api/dispositivo/token
Authorization: Bearer {token}
```

**Request:**
```json
{
  "token_fcm": "dkj3h4kj2h3k4j2h3k4...",
  "plataforma": "android"
}
```

| Campo | Tipo | Requerido | Valores |
|-------|------|-----------|---------|
| `token_fcm` | string | ✅ | Token FCM del dispositivo |
| `plataforma` | string | ❌ | `android` (por defecto), `ios` |

**Response 200:**
```json
{ "ok": true }
```

---

### 3.2 Notificaciones que envía el servidor

El backend envía notificaciones automáticamente en estos eventos:

| Evento | Disparador | Destinatario |
|--------|-----------|--------------|
| `cita_creada` | Gestor crea una cita para un paciente | El paciente |
| `recordatorio` | Tarea programada — 09:00 del día anterior | Pacientes con cita confirmada/pendiente al día siguiente |

**Estructura del payload FCM recibido:**
```json
{
  "notification": {
    "title": "Nueva cita registrada",
    "body": "Tu cita para el 15/05/2026 10:00 ha sido registrada. Motivo: Revisión"
  },
  "data": {
    "tipo": "cita_creada",
    "cita_id": "42"
  }
}
```

**Tipos de notificación (`data.tipo`):**

| Tipo | Acción sugerida en la app |
|------|--------------------------|
| `cita_creada` | Navegar a detalle de la cita (`cita_id`) |
| `recordatorio` | Navegar a detalle de la cita (`cita_id`) |

---

## 4. Pacientes (Clientes)

### 4.1 Listar pacientes

```
GET /api/clientes
Authorization: Bearer {token}    (gestor)
```

Soporta búsqueda por texto y paginación.

**Query params opcionales:**

| Param | Tipo | Descripción |
|-------|------|-------------|
| `buscar` | string | Busca en apellidos, nombre, num_filiacion y teléfono |
| `page` | integer | Página (50 resultados por página) |

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "num_filiacion": "MUL-00001",
      "apellidos": "García López",
      "nombre": "Juan",
      "edad": 35,
      "profesion": "Ingeniero",
      "direccion": "Calle Mayor 1",
      "cp": "30170",
      "telefono": "600123456",
      "observaciones": null,
      "user_id": null,
      "created_at": "2026-01-01T10:00:00.000000Z",
      "updated_at": "2026-01-01T10:00:00.000000Z"
    }
  ],
  "current_page": 1,
  "last_page": 3,
  "per_page": 50,
  "total": 120
}
```

---

### 4.2 Ver perfil de un paciente

```
GET /api/clientes/{id}
Authorization: Bearer {token}    (gestor o el propio paciente)
```

**Response 200:**
```json
{
  "id": 1,
  "num_filiacion": "MUL-00001",
  "apellidos": "García López",
  "nombre": "Juan",
  "edad": 35,
  "profesion": "Ingeniero",
  "direccion": "Calle Mayor 1",
  "cp": "30170",
  "telefono": "600123456",
  "observaciones": null,
  "user_id": 5,
  "historial_clinico": [...],
  "dentadura": [...],
  "citas_futuras": [...]
}
```

---

### 4.3 Crear paciente

```
POST /api/clientes
Authorization: Bearer {token}    (gestor)
```

Al crear un paciente, el backend inicializa automáticamente los 32 dientes adultos como `presente` en el odontograma.

**Request:**
```json
{
  "apellidos": "García López",
  "nombre": "Juan",
  "edad": 35,
  "profesion": "Ingeniero",
  "direccion": "Calle Mayor 1",
  "cp": "30170",
  "telefono": "600123456",
  "observaciones": "Alérgico a la penicilina"
}
```

| Campo | Tipo | Requerido |
|-------|------|-----------|
| `apellidos` | string (max 100) | ✅ |
| `nombre` | string (max 100) | ✅ |
| `edad` | integer (0–150) | ❌ |
| `profesion` | string (max 100) | ❌ |
| `direccion` | string (max 200) | ❌ |
| `cp` | string (max 10) | ❌ |
| `telefono` | string (max 20) | ❌ |
| `observaciones` | string | ❌ |

**Response 201:** Devuelve el paciente recién creado con `num_filiacion` asignado (formato `MUL-00001`).

---

### 4.4 Actualizar paciente

```
PUT /api/clientes/{id}
Authorization: Bearer {token}    (gestor)
```

Los campos son los mismos que en la creación. Todos son requeridos en PUT (enviar los valores actuales para los que no cambien).

**Response 200:** Devuelve el paciente actualizado.

---

### 4.5 Eliminar paciente

```
DELETE /api/clientes/{id}
Authorization: Bearer {token}    (gestor)
```

Soft delete: el registro no se borra físicamente. Los datos históricos se conservan.

**Response 200:**
```json
{ "ok": true }
```

---

## 5. Historial clínico

El historial clínico es una lista de registros asociados a un paciente. Cada registro representa una visita o tratamiento.

### 5.1 Listar historial de un paciente

```
GET /api/clientes/{id}/historial
Authorization: Bearer {token}    (gestor o el propio paciente)
```

Devuelve los registros ordenados por fecha descendente (año, mes, día), paginados (20 por página).

**Response 200:**
```json
{
  "data": [
    {
      "id": 10,
      "cliente_id": 1,
      "gestor_id": 2,
      "dia": 10,
      "mes": 5,
      "anio": 2026,
      "signo": "Dolor leve",
      "diagnostico": "Caries en diente 16",
      "num_sesiones": 2,
      "importe": "150.00",
      "tratamiento_realizado": "Obturación con composite",
      "recibo": "REC-001",
      "debe": "150.00",
      "haber": "150.00",
      "saldo": "0.00",
      "created_at": "2026-05-10T10:00:00.000000Z",
      "updated_at": "2026-05-10T10:00:00.000000Z"
    }
  ],
  "current_page": 1,
  "last_page": 1,
  "per_page": 20,
  "total": 1
}
```

**Nota sobre `saldo`:** Se calcula automáticamente como `debe - haber`. No enviarlo al crear o actualizar.

---

### 5.2 Crear registro de historial

```
POST /api/clientes/{id}/historial
Authorization: Bearer {token}    (gestor)
```

**Request:**
```json
{
  "dia": 10,
  "mes": 5,
  "anio": 2026,
  "signo": "Dolor leve",
  "diagnostico": "Caries en diente 16",
  "num_sesiones": 2,
  "importe": 150.00,
  "tratamiento_realizado": "Obturación con composite",
  "recibo": "REC-001",
  "debe": 150.00,
  "haber": 150.00
}
```

| Campo | Tipo | Requerido | Notas |
|-------|------|-----------|-------|
| `dia` | integer (1–31) | ✅ | Se valida que la fecha sea real (`checkdate`) |
| `mes` | integer (1–12) | ✅ | |
| `anio` | integer (1900–2100) | ✅ | |
| `diagnostico` | string | ✅ | |
| `signo` | string (max 50) | ❌ | |
| `num_sesiones` | integer (≥1) | ❌ | |
| `importe` | decimal (≥0) | ❌ | |
| `tratamiento_realizado` | string | ❌ | |
| `recibo` | string (max 50) | ❌ | |
| `debe` | decimal (≥0) | ❌ | Lo que el paciente debe |
| `haber` | decimal (≥0) | ❌ | Lo que el paciente ha pagado |

**Response 201:** Devuelve el registro creado (con `saldo` calculado).

**Error `422`:** Si la fecha no es válida (ej. 31 de febrero), el error vendrá en el campo `dia`.

---

### 5.3 Actualizar registro de historial

```
PUT /api/historial/{id}
Authorization: Bearer {token}    (gestor)
```

Los campos son los mismos que en la creación.

**Response 200:** Devuelve el registro actualizado.

---

### 5.4 Eliminar registro de historial

```
DELETE /api/historial/{id}
Authorization: Bearer {token}    (gestor)
```

Hard delete (se elimina definitivamente).

**Response 200:**
```json
{ "ok": true }
```

---

## 6. Odontograma (Dentadura)

### 6.1 Notación FDI (ISO 3950)

El sistema usa numeración FDI. Cada diente tiene un número de 2 dígitos:

```
Arcada superior (adulto):
  ← Izquierda del paciente    Derecha del paciente →
  18 17 16 15 14 13 12 11 | 21 22 23 24 25 26 27 28

Arcada inferior (adulto):
  48 47 46 45 44 43 42 41 | 31 32 33 34 35 36 37 38
```

**Tipo de diente por número:**

| Tipo | Números FDI |
|------|-------------|
| Incisivo | x1, x2 (11,12,21,22,31,32,41,42) |
| Canino | x3 (13,23,33,43) |
| Premolar | x4, x5 (14,15,24,25,34,35,44,45) |
| Molar | x6, x7, x8 (16,17,18,26,27,28,36,37,38,46,47,48) |

**Caras del diente:**

| Cara | Descripción | Tiene oclusal |
|------|-------------|---------------|
| `vestibular` | Exterior (labial/bucal) | Todos |
| `lingual` | Interior (lingual/palatino) | Todos |
| `mesial` | Lateral hacia línea media | Todos |
| `distal` | Lateral opuesto a línea media | Todos |
| `oclusal` | Superficie de masticación | Solo premolares y molares |

Los incisivos y caninos **no tienen cara oclusal**.

---

### 6.2 Ver odontograma de un paciente

```
GET /api/clientes/{id}/dentadura
Authorization: Bearer {token}    (gestor o el propio paciente)
```

**Response 200:** Array con los 32 registros del odontograma, ordenados por `num_diente`.

```json
[
  {
    "id": 1,
    "cliente_id": 1,
    "num_diente": "11",
    "estado_pieza": null,
    "cara_vestibular": null,
    "cara_lingual": null,
    "cara_mesial": null,
    "cara_distal": null,
    "cara_oclusal": null,
    "notas": null,
    "fecha_actualizacion": "2026-01-01"
  },
  {
    "id": 2,
    "cliente_id": 1,
    "num_diente": "16",
    "estado_pieza": "corona",
    "cara_vestibular": null,
    "cara_lingual": "caries",
    "cara_mesial": null,
    "cara_distal": null,
    "cara_oclusal": "obturacion",
    "notas": "Revisada en 2025",
    "fecha_actualizacion": "2026-03-15"
  }
]
```

**Importante — valor `null`:**
- `estado_pieza = null` significa **presente** (diente sano/normal).
- Cualquier cara `= null` significa **sana** (sin patología).

Al renderizar, tratar `null` como `"presente"` para estado de pieza y como `"sano"` para caras.

---

### 6.3 Actualizar odontograma

```
POST /api/clientes/{id}/dentadura
Authorization: Bearer {token}    (gestor)
```

Enviar solo los dientes que se quieran actualizar. Se usa upsert: si el diente no existe se crea, si existe se actualiza.

**Request:**
```json
{
  "dientes": [
    {
      "num_diente": "16",
      "estado_pieza": "corona",
      "cara_vestibular": null,
      "cara_lingual": "caries",
      "cara_mesial": null,
      "cara_distal": null,
      "cara_oclusal": "obturacion",
      "notas": "Revisada en 2025"
    },
    {
      "num_diente": "26",
      "estado_pieza": "ausente"
    }
  ]
}
```

| Campo | Tipo | Requerido |
|-------|------|-----------|
| `dientes` | array | ✅ |
| `dientes[].num_diente` | string | ✅ |
| `dientes[].estado_pieza` | string\|null | ❌ |
| `dientes[].cara_vestibular` | string\|null | ❌ |
| `dientes[].cara_lingual` | string\|null | ❌ |
| `dientes[].cara_mesial` | string\|null | ❌ |
| `dientes[].cara_distal` | string\|null | ❌ |
| `dientes[].cara_oclusal` | string\|null | ❌ |
| `dientes[].notas` | string\|null | ❌ |

Los valores `"sano"` y `"presente"` se almacenan como `null` internamente. Puedes enviar `null` directamente.

**Response 200:**
```json
{ "ok": true, "message": "Dentadura actualizada." }
```

---

## 7. Citas

### 7.1 Mis citas (paciente)

```
GET /api/mis-citas
Authorization: Bearer {token}    (cliente)
```

Solo para usuarios con rol `cliente`. Devuelve las citas del paciente vinculado a su cuenta.

**Response 200:**
```json
{
  "proximas": [
    {
      "id": 5,
      "cliente_id": 1,
      "gestor_id": 2,
      "fecha_hora": "2026-06-15T10:00:00.000000Z",
      "duracion_minutos": 45,
      "motivo": "Revisión semestral",
      "estado": "confirmada",
      "notas": null,
      "recordatorio_enviado": false,
      "gestor": {
        "id": 2,
        "name": "Dr. García"
      }
    }
  ],
  "anteriores": [...]
}
```

- `proximas`: citas futuras con estado `pendiente` o `confirmada`, ordenadas por fecha ascendente.
- `anteriores`: las últimas 20 citas pasadas, ordenadas por fecha descendente.

Si el usuario no tiene ficha de paciente vinculada, devuelve `404`.

---

### 7.2 Citas del mes (gestor — calendario)

```
GET /api/citas/mes
Authorization: Bearer {token}    (gestor)
```

Devuelve citas en un rango de fechas formateadas para FullCalendar. Útil para mostrar un calendario mensual en la app de gestor.

**Query params opcionales:**

| Param | Tipo | Descripción | Por defecto |
|-------|------|-------------|-------------|
| `start` | date (YYYY-MM-DD) | Inicio del rango | Primer día del mes actual |
| `end` | date (YYYY-MM-DD) | Fin del rango (exclusivo) | Primer día del mes siguiente |

**Response 200:** Array de eventos.
```json
[
  {
    "id": 5,
    "title": "García López, Juan — Revisión",
    "start": "2026-05-15T10:00:00+02:00",
    "end": "2026-05-15T10:45:00+02:00",
    "color": "#3b82f6",
    "extendedProps": {
      "estado": "confirmada",
      "motivo": "Revisión semestral",
      "notas": null,
      "cliente_id": 1,
      "cliente_nombre": "García López, Juan"
    }
  }
]
```

---

### 7.3 Crear cita

```
POST /api/citas
Authorization: Bearer {token}    (gestor o cliente)
```

**El comportamiento varía según el rol:**

| Campo | Gestor | Cliente |
|-------|--------|---------|
| `cliente_id` | Requerido (especifica el paciente) | Ignorado (se usa el suyo propio) |
| `estado` inicial | `confirmada` | `pendiente` |
| Notificación push | Sí, al paciente | No |

**Request (gestor):**
```json
{
  "cliente_id": 1,
  "fecha_hora": "2026-06-15T10:00:00",
  "duracion_minutos": 45,
  "motivo": "Revisión semestral",
  "notas": "Traer radiografías anteriores"
}
```

**Request (cliente — omite `cliente_id`):**
```json
{
  "fecha_hora": "2026-06-15T10:00:00",
  "motivo": "Me duele el diente 16"
}
```

| Campo | Tipo | Requerido | Validación |
|-------|------|-----------|------------|
| `cliente_id` | integer | Solo gestor | Debe existir en BD |
| `fecha_hora` | datetime | ✅ | Debe ser en el futuro (`after:now`) |
| `duracion_minutos` | integer | ❌ | 15–240, por defecto 30 |
| `motivo` | string (max 200) | ✅ | |
| `notas` | string | ❌ | |

**Response 201:** La cita creada con el paciente cargado.

**Error `422`:** Si la fecha es pasada o falta el motivo.
**Error `422`:** Si el cliente no tiene ficha vinculada (solo para usuarios cliente).

---

### 7.4 Actualizar cita completa

```
PUT /api/citas/{id}
Authorization: Bearer {token}    (gestor)
```

Permite modificar cualquier campo de la cita. Todos son opcionales (`sometimes`).

**Request:**
```json
{
  "fecha_hora": "2026-06-16T11:00:00",
  "duracion_minutos": 60,
  "motivo": "Revisión + limpieza",
  "estado": "confirmada",
  "notas": "Paciente llegará tarde"
}
```

| Campo | Tipo | Validación |
|-------|------|------------|
| `fecha_hora` | datetime | Fecha válida |
| `duracion_minutos` | integer | 15–240 |
| `motivo` | string (max 200) | |
| `estado` | string | Valores válidos (ver catálogo) |
| `notas` | string\|null | |

**Response 200:** La cita actualizada con el paciente cargado.

---

### 7.5 Cambiar estado de cita

```
PATCH /api/citas/{id}/estado
Authorization: Bearer {token}    (gestor)
```

Endpoint específico para cambios de estado en el flujo de trabajo.

**Request:**
```json
{
  "estado": "realizada",
  "notas": "Tratamiento completado sin incidencias"
}
```

| Campo | Tipo | Requerido |
|-------|------|-----------|
| `estado` | string | ✅ |
| `notas` | string | ❌ |

**Response 200:** La cita actualizada con el paciente cargado.

---

### 7.6 Eliminar cita

```
DELETE /api/citas/{id}
Authorization: Bearer {token}    (gestor)
```

Hard delete.

**Response 200:**
```json
{ "ok": true }
```

---

## 8. Modelos de datos

### 8.1 Cliente (Paciente)

```
id               integer    PK, autoincremental
num_filiacion    string     Formato "MUL-00001", generado al crear
apellidos        string     max 100
nombre           string     max 100
edad             integer?   0–150
profesion        string?    max 100
direccion        string?    max 200
cp               string?    max 10
telefono         string?    max 20
observaciones    text?
user_id          integer?   FK → users (si tiene cuenta de acceso)
deleted_at       datetime?  Soft delete
created_at       datetime
updated_at       datetime
```

### 8.2 Cita

```
id                   integer    PK
cliente_id           integer    FK → clientes
gestor_id            integer?   FK → users (gestor que la creó/gestionó)
fecha_hora           datetime
duracion_minutos     integer    Por defecto 30
motivo               string     max 200
estado               string     pendiente|confirmada|realizada|cancelada|no_presentado
notas                text?
recordatorio_enviado boolean    Por defecto false
created_at           datetime
updated_at           datetime
```

### 8.3 HistorialClinico

```
id                    integer    PK
cliente_id            integer    FK → clientes
gestor_id             integer?   FK → users
dia                   integer    1–31
mes                   integer    1–12
anio                  integer
signo                 string?    max 50
diagnostico           text
num_sesiones          integer?
importe               decimal?   2 decimales
tratamiento_realizado text?
recibo                string?    max 50
debe                  decimal?   2 decimales
haber                 decimal?   2 decimales
saldo                 decimal    Auto: debe - haber (no enviar)
created_at            datetime
updated_at            datetime
```

### 8.4 Dentadura (registro por diente)

```
id                   integer    PK
cliente_id           integer    FK → clientes
num_diente           string     Número FDI (ej. "16")
estado_pieza         string?    null = presente
cara_vestibular      string?    null = sano
cara_lingual         string?    null = sano
cara_mesial          string?    null = sano
cara_distal          string?    null = sano
cara_oclusal         string?    null = sano (solo molares/premolares)
notas                text?
fecha_actualizacion  date
created_at           datetime
updated_at           datetime
```

### 8.5 User

```
id       integer    PK
name     string
email    string
role     string     gestor|cliente
activo   boolean    Por defecto true
```

---

## 9. Catálogos y enumerados

### 9.1 Estados de cita

| Valor | Etiqueta | Color |
|-------|----------|-------|
| `pendiente` | Pendiente | `#f97316` (naranja) |
| `confirmada` | Confirmada | `#3b82f6` (azul) |
| `realizada` | Realizada | `#4ade80` (verde) |
| `cancelada` | Cancelada | `#ef4444` (rojo) |
| `no_presentado` | No se presentó | `#6b7280` (gris) |

**Flujo de estados:**
```
pendiente → confirmada → realizada
                       → cancelada
                       → no_presentado
pendiente → cancelada
```

### 9.2 Estados de pieza dental (`estado_pieza`)

| Valor | Etiqueta | Color | Icono |
|-------|----------|-------|-------|
| `null` / `presente` | Presente | `#4ade80` | ✓ |
| `ausente` | Ausente | `#6b7280` | ○ |
| `corona` | Corona | `#a855f7` | ♛ |
| `puente` | Puente | `#3b82f6` | P |
| `implante` | Implante | `#eab308` | I |
| `endodoncia` | Endodoncia | `#f97316` | E |
| `no_erupcionado` | No erupcionado | `#fef3c7` | ? |
| `extrac_indicada` | Extracción indicada | `#fca5a5` | ! |
| `extraido` | Extraído | `#9ca3af` | ✕ |
| `temporal` | Temporal (deciduo) | `#f9a8d4` | T |
| `pulpitis` | Pulpitis | `#f97316` | Pu |
| `necrosis` | Necrosis pulpar | `#1f2937` | N |
| `apicectomia` | Apicectomía | `#0f766e` | Ap |
| `incluido` | Incluido/Retenido | `#7c3aed` | R |
| `supernumerario` | Supernumerario | `#db2777` | S |
| `movilidad_1` | Movilidad Grado I | `#facc15` | M1 |
| `movilidad_2` | Movilidad Grado II | `#ea580c` | M2 |
| `movilidad_3` | Movilidad Grado III | `#b91c1c` | M3 |
| `carilla` | Carilla | `#93c5fd` | Ca |
| `pilar_puente` | Pilar de puente | `#a78bfa` | Pp |
| `pontico` | Póntico de puente | `#c4b5fd` | Po |
| `prot_removible` | Prótesis removible | `#fb923c` | Pr |
| `giroversion` | Giroversión | `#84cc16` | G |
| `migracion` | Migración | `#22d3ee` | → |
| `diastema` | Diastema | `#e879f9` | ◁▷ |
| `fluorosis` | Fluorosis | `#a3e635` | F |
| `agenesia` | Agenesia | `#94a3b8` | ∅ |

### 9.3 Estados de cara dental (`cara_*`)

| Valor | Etiqueta | Color |
|-------|----------|-------|
| `null` / `sano` | Sano | `#4ade80` |
| `caries` | Caries | `#ef4444` |
| `obturacion` | Obturación | `#f97316` |
| `fractura` | Fractura | `#a855f7` |
| `sellante` | Sellante | `#06b6d4` |
| `desgaste` | Desgaste | `#78716c` |
| `caries_det` | Caries detenida | `#f59e0b` |
| `composite` | Composite | `#3b82f6` |
| `amalgama` | Amalgama | `#64748b` |
| `erosion` | Erosión | `#d97706` |
| `tincion` | Tinción | `#92400e` |
| `fisura` | Fisura | `#374151` |
| `reconstruccion` | Reconstrucción | `#059669` |

---

## 10. Gestión de errores

### Códigos HTTP

| Código | Significado | Acción en la app |
|--------|-------------|-----------------|
| `200` | OK | Procesar respuesta |
| `201` | Creado | Procesar respuesta, actualizar lista |
| `401` | No autenticado | Redirigir a login, limpiar token |
| `403` | Sin permisos | Mostrar mensaje de acceso denegado |
| `404` | No encontrado | Mostrar error o volver atrás |
| `422` | Validación fallida | Mostrar errores campo a campo |
| `500` | Error servidor | Mostrar mensaje genérico |

### Estructura de error de validación (`422`)

```json
{
  "message": "The apellidos field is required.",
  "errors": {
    "apellidos": ["El campo apellidos es obligatorio."],
    "fecha_hora": ["La fecha debe ser posterior a ahora."]
  }
}
```

### Estructura de error genérico

```json
{
  "message": "Descripción del error."
}
```

---

## 11. Reglas de negocio

### Identificador de paciente (`num_filiacion`)
- Se genera automáticamente al crear el paciente: `MUL-00001`, `MUL-00002`, etc.
- No puede modificarse una vez asignado.

### Odontograma inicial
- Al crear un paciente, el backend crea automáticamente los 32 dientes adultos con `estado_pieza = null` (presente).
- La app no necesita inicializar el odontograma manualmente.

### Valores nulos en odontograma
- `estado_pieza = null` → mostrar como **presente** (diente normal).
- `cara_* = null` → mostrar como **sana** (sin patología).
- Al enviar actualizaciones, se puede enviar `null` para "limpiar" un estado.

### Saldo del historial
- `saldo = debe - haber`, calculado automáticamente por el servidor.
- Nunca enviarlo en peticiones de creación/actualización.
- Un saldo positivo indica deuda del paciente.

### Citas — estados permitidos
- Un cliente solo puede crear citas para sí mismo con estado `pendiente`.
- Un gestor crea citas en estado `confirmada`.
- El cambio de estado lo gestiona el gestor.

### Citas — notificaciones
- Cuando el gestor crea una cita para un paciente que tiene cuenta de usuario, el servidor envía automáticamente una notificación push.
- El servidor envía recordatorios a las 09:00 del día anterior para citas con estado `confirmada` o `pendiente`.
- Si el paciente no tiene la app instalada o no ha registrado un token FCM, las notificaciones simplemente no se entregan (sin error).

### Soft delete de pacientes
- Los pacientes eliminados no aparecen en listados ni búsquedas.
- Sus datos históricos (historial, odontograma, citas) se conservan en base de datos.
- No hay endpoint de restauración en la API actual.

### Sesiones y tokens
- Los tokens de Sanctum no expiran automáticamente.
- Cada login genera un token nuevo. Los anteriores siguen siendo válidos hasta que se haga logout.
- Para multi-dispositivo, cada dispositivo debe hacer su propio login.

---

## Credenciales de prueba (entorno de desarrollo)

| Rol | Email | Contraseña |
|-----|-------|------------|
| Gestor | admin@clinicamula.es | Admin1234! |
| Paciente | paciente@example.com | Cliente1234! |
