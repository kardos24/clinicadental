# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Clínica Dental Mula** — Laravel 11 application for dental clinic management (patients, appointments, clinical history, dental charts) with REST API for Android app (Flutter) and Firebase push notifications.

**Architecture**: Laravel 11 + MySQL 8.4 + PHP 8.3 + Laravel Sanctum (API auth) + Firebase FCM v1

**Key features**:
- Patient records with auto-generated `num_filiacion` (format: `MUL-00001`)
- Full dental chart with FDI ISO 3950 notation (32 adult teeth, 5-face analysis per tooth)
- Appointment calendar with FullCalendar.js integration
- Push notifications for Android devices via Firebase Cloud Messaging
- Role-based access: `gestor` (manager) vs `cliente` (patient)

## Launching with Laragon

The project runs via Laragon at **http://127.0.0.1:8080**.

Setup (one-time):
- Junction: `C:\laragon\www\ClinicaMula` → `C:\Users\Kardos\Desktop\Trabajo\Paginasweb\ClinicaMula`
- Apache config: `C:\laragon\etc\apache2\sites-enabled\clinicamula.conf` (port 8080, DocumentRoot → `/public`)
- Start/reload Apache: Laragon tray → **Menu → Apache → Reload**

### Manual start (if Laragon tray is not available)

```powershell
# Apache
& "C:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18\bin\httpd.exe"

# MySQL (data dir: C:\laragon\data\mysql-8.4)
& "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe" `
    --defaults-file="C:\laragon\bin\mysql\mysql-8.4.3-winx64\my.ini"

# PHP CLI
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan <command>
```

> **MySQL permission fix** (run once if ibdata1 is read-only):
> `icacls "C:\laragon\data\mysql-8.4" /grant "$env:USERNAME:(OI)(CI)F" /T`

## Development Commands

```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database (create MySQL database named 'clinica_mula' first)
php artisan migrate --seed

# Code formatting (Laravel Pint)
php artisan pint

# Clear compiled views (run after Blade changes)
php artisan view:clear

# Debugging (Laravel Telescope — available in dev only)
# Visit /telescope on your dev server

# Run tests
php vendor/bin/phpunit

# Run a single test
php vendor/bin/phpunit tests/Feature/ExampleTest.php

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Git Workflow

### Branch structure

| Branch | Purpose |
|--------|---------|
| `master` | Production — protected, requires PR with 1 approval to merge |
| `develop` | Stable development — merge here when a feature is ready |
| `feature/*` | One branch per feature/fix, always branched from `develop` |
| `main` | Legacy snapshot — do not use |

### Day-to-day flow

```bash
# 1. Start a new feature or fix
git checkout develop
git pull origin develop
git checkout -b feature/nombre-descriptivo

# 2. Work and commit normally
git add <files>
git commit -m "feat: descripción del cambio"

# 3. When the feature is stable, merge into develop
git checkout develop
git merge --no-ff feature/nombre-descriptivo
git push origin develop

# 4. Delete the feature branch
git branch -d feature/nombre-descriptivo
git push origin --delete feature/nombre-descriptivo
```

### Releasing to production

When `develop` is stable enough for production, open a PR on GitHub:
- **Base:** `master` ← **Compare:** `develop`
- Review and approve the PR yourself on GitHub
- Merge — `master` only advances via approved PRs

## Key Business Logic

- **Patient ID (`num_filiacion`)**: Auto-generated format `MUL-00001`
- **Dental chart**: Uses FDI ISO 3950 notation. Each tooth has a `estado_pieza` (overall condition) and up to 5 face columns (`cara_vestibular`, `cara_lingual`, `cara_mesial`, `cara_distal`, `cara_oclusal`). The oclusal face only applies to premolares and molares — see `Dentadura::DIENTES_CON_OCLUSAL`.
- **Null = sano/presente in DB**: `ClienteController::actualizarDentadura()` normalises `'sano'` → `null` for face columns and `'presente'` can also be stored as `null` for `estado_pieza`. All Dentadura model methods must use `?? 'sano'` / `?? 'presente'` guards accordingly.
- **Appointment states**: `pendiente` → `confirmada` → `realizada` | `cancelada` | `no_presentado`
- **Soft deletes**: `Cliente` model uses soft deletes only; all other models use hard deletes.
- **Balance calculation**: `HistorialClinico.saldo` is auto-calculated as `debe - haber` in the model's `saving` boot hook — never set it manually.
- **Role system**: `gestor` (full access) and `cliente` (limited access); check via `$user->isGestor()` / `$user->isCliente()` helpers on the `User` model.
- **HistorialClinico dates**: Stored as three separate columns — `dia`, `mes`, `anio` — not a single date column.
- **Tooth initialization**: Seeder creates all 32 adult teeth as `presente` for new patients via `DatabaseSeeder`.

## Database Schema

1. **users** — role (`gestor`/`cliente`), `activo` flag, Laravel Sanctum tokens
2. **clientes** — patient records with soft deletes, `num_filiacion`, search index on `[apellidos, nombre]`
3. **historial_clinico** — yearly clinical records; `saldo` auto-calculated; `dia`/`mes`/`anio` stored separately
4. **dentadura** — per-tooth record: `estado_pieza` + 5 face columns; unique constraint on `[cliente_id, num_diente]`
5. **citas** — appointments with status workflow, `recordatorio_enviado` flag
6. **dispositivos_push** — FCM tokens for Android push notifications

## Models & Relationships

```
User (role: gestor/cliente)
  ├── hasOne → Cliente (only cliente-role users have a linked Cliente record)
  ├── hasMany → DispositivoPush
  └── (gestor users are referenced in Citas.gestor_id, but no hasMany on User model)

Cliente (linked to User via user_id)
  ├── belongsTo → User
  ├── hasMany → HistorialClinico (ordered: year desc, month desc, day desc)
  ├── hasMany → Dentadura
  ├── hasMany → Cita (ordered: fecha_hora desc)
  └── hasMany → citasFuturas (custom — fecha_hora >= now, confirmada/pendiente)

Cita
  ├── belongsTo → Cliente
  └── belongsTo → User (gestor_id)

HistorialClinico
  ├── belongsTo → Cliente
  └── belongsTo → User (gestor_id)

DispositivoPush
  └── belongsTo → User
```

`Cliente` also has a `buscar($termino)` scope that searches `apellidos`, `nombre`, `num_filiacion`, and `telefono`.

### Important Model Helpers

**Dentadura**:
- `estaPresente()`: returns true if `estado_pieza` is 'presente' (null → 'presente')
- `estadoCara($cara)`: returns face state or 'sano' if null
- `colorCara($cara)`: returns color for face state
- `tienePatologia()`: returns true if any face has non-'sano' state
- `resumen()`: tooltip string with all non-sano conditions
- `mapaCliente($clienteId)`: static method returning `[num_diente => Dentadura]` map
- `carasAplicables()`: returns applicable faces for a tooth (5 for molars/premolars, 4 for others)

**HistorialClinico**:
- `saldo` auto-calculated in `boot()` hook — never set manually

**Cita**:
- `estadoLabel`: formatted status label (Pendiente, Confirmada, etc.)
- `estadoColor`: status color for calendar events
- Scopes: `proximas()`, `delDia($fecha)`, `delMes($year, $month)`

## Dentadura Model — Constants & Faces

The `Dentadura` model (`app/Models/Dentadura.php`) has three constant groups:

**`ESTADOS_PIEZA`** — overall tooth condition:
`presente`, `ausente`, `corona`, `puente`, `implante`, `endodoncia`

**`ESTADOS_CARA`** — per-face surface condition:
`sano`, `caries`, `obturacion`, `fractura`, `sellante`, `desgaste`

**`CARAS`** — five anatomical surfaces:
`vestibular` (outer), `lingual` (inner), `mesial` (toward midline), `distal` (away from midline), `oclusal` (chewing surface — premolares/molares only)

**`DIENTES_CON_OCLUSAL`** — FDI numbers that have an oclusal surface:
`[14,15,16,17,18, 24,25,26,27,28, 34,35,36,37,38, 44,45,46,47,48]`

**`DIENTES_SUPERIORES`** / **`DIENTES_INFERIORES`** — ordered arrays for rendering the two dental arches.

> **Null-safety rule**: `estaPresente()` uses `($this->estado_pieza ?? 'presente') === 'presente'` and `resumen()` normalises with `$estadoPieza = $this->estado_pieza ?? 'presente'` before any comparison or array lookup. Any new method that reads `estado_pieza` must follow the same pattern.

> **Note**: A legacy `ESTADOS` constant exists but is deprecated. Use `ESTADOS_PIEZA` + `ESTADOS_CARA` for new code.

**Tooth type mapping** (shared across both odontogram components):
```php
match(true) {
    in_array($num, [11,12,21,22,31,32,41,42]) => 'incisivo',
    in_array($num, [13,23,33,43])             => 'canino',
    in_array($num, [14,15,24,25,34,35,44,45]) => 'premolar',
    default                                   => 'molar',
}
```

## Controllers

| Controller | Purpose |
|------------|---------|
| `AuthController` | Web login/register/logout; redirects gestor→dashboard, cliente→mis-citas |
| `ClienteController` | Patient CRUD + `actualizarDentadura()` (JSON endpoint); uses `DentaduraService` |
| `CitaController` | Appointments + calendar view + `apiMes()` (FullCalendar JSON); uses `CitaService` |
| `HistorialController` | Clinical history CRUD; date validation in `StoreHistorialRequest`/`UpdateHistorialRequest` |
| `DashboardController` | Manager-only stats (totals, today's appointments, recent patients) |
| `ContactoController` | Public contact form with non-blocking email send |
| `Api\AuthApiController` | REST API: login, logout |
| `Api\CitaApiController` | REST API: citas CRUD, estado update, mes (FullCalendar JSON) |
| `Api\ClienteApiController` | REST API: clientes listing/show/historial |
| `Api\DispositivoApiController` | REST API: FCM token registration |
| `EsGestor` (middleware) | Returns 403 (JSON or HTML) for non-gestor users |
| `EsGestorOPropietario` (middleware) | Allows gestor OR the owner client; used on `/api/clientes/{id}` routes |

## Services

| Service | Purpose |
|---------|---------|
| `DentaduraService` | Normalises face/piece states (`sano`→null, `presente`→null) and updates tooth records |
| `CitaService` | Creates citas (triggers push via `NotificacionService`), updates estado, builds FullCalendar data |
| `NotificacionService` | FCM v1 push notification logic (OAuth2 JWT, graceful failure) |

## FormRequests

`app/Http/Requests/`: `StoreClienteRequest`, `UpdateClienteRequest`, `StoreCitaRequest`, `UpdateCitaEstadoRequest`, `StoreHistorialRequest` (with `checkdate()` validation), `UpdateHistorialRequest`, `ContactoRequest`.

## Routes Structure

### Web (`routes/web.php`)
- **Public**: home, services, contact form
- **Auth** (guest): login/register; (auth): logout
- **Manager-only** (gestor middleware): dashboard, `/clientes` CRUD, `POST /clientes/{cliente}/dentadura`, calendar, `GET /api/citas/mes` (FullCalendar JSON)
- **Protected** (auth): `GET /clientes/{cliente}`, `GET /mis-citas`, `POST /citas`

### API (`routes/api.php`)
- **Public**: `POST /api/login` (returns Sanctum token)
- **Protected** (`Authorization: Bearer {token}`):
  - `POST /api/logout`
  - `POST /api/dispositivo/token` — Register FCM token
  - `GET /api/mis-citas` — Own appointments
  - `POST /api/citas` — Create appointment
  - **Gestor only** (gestor middleware): `GET /api/clientes`, `GET /api/citas/mes`, `PATCH /api/citas/{id}/estado`
  - **Gestor or owner** (gestor.o.propietario middleware): `GET /api/clientes/{id}`, `GET /api/clientes/{id}/historial`

### Scheduled Tasks (`routes/console.php`)
- **Daily at 09:00** — Sends push notifications for next-day appointments (`recordatorio_enviado = false`, status confirmada/pendiente)
- Requires system cron: `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`

## API Authentication Flow

1. Android app sends credentials to `POST /api/login`
2. Returns `{ token, user: { id, name, email, role } }`
3. Include `Authorization: Bearer {token}` header in subsequent requests
4. Android device registers FCM token via `POST /api/dispositivo/token`
5. Server sends push notifications via `NotificacionService` using FCM v1 API with RS256 JWT (access token cached 58 min)

## Odontogram — Views & Components

The patient profile (`resources/views/clientes/show.blade.php`) displays **two odontogram cards**:

### 1. Odontograma oclusal (multi-cara) — inline in `show.blade.php`
- Top-down (occlusal) view with **5 individually clickable SVG sections** per tooth
- Each section maps to a clinical surface: vestibular, lingual, mesial, distal, oclusal
- Tooth outline shape uses anatomical `<clipPath>` per type (molar/premolar/canino/incisivo)
- Groove lines drawn decoratively on top of color fills
- Clicking a face calls `abrirModalCaraDiente(num, cara)` → opens the edit modal and focuses/highlights that face's `<select>`
- SVG shapes and click logic are extracted to **`resources/views/components/_diente-inner.blade.php`** (included via `@include`)

### 2. Odontograma bucal (anatómico) — `<x-odontograma-bucal>`
- Frontal (buccal) view: crown shape + roots per tooth, coloured by vestibular surface state
- File: **`resources/views/components/odontograma-bucal.blade.php`**
- Props: `$cliente`, `$dentadura` (keyed collection), `$modoEdicion` (bool, default true)
- Lower arch SVG is flipped vertically with `transform:scaleY(-1)` so roots point upward
- Clicking a tooth calls `abrirModalDiente(num)` (whole-tooth modal, no face pre-selection)
- Reference image: `resources/odontograma/odontograma.jpg`

### Edit modal (`modal-diente` in `show.blade.php`)
- JS: `abrirModalDiente(num, caraResaltar?)` — populates all fields, highlights a specific face if provided
- JS: `abrirModalCaraDiente(num, cara)` — delegates to `abrirModalDiente` with face pre-highlight
- JS: `guardarDiente()` — POSTs to `clientes.dentadura` route as JSON
- The `dentaduraData` JS object is serialised from PHP via `@php json_encode(...)` + `{!! ... !!}` (never use `@json()` with a closure — Blade's parenthesis counter mismatches brackets)

## Files of Interest

- **routes/web.php** / **routes/api.php** — Route definitions
- **routes/console.php** — Scheduled task definitions
- **app/Services/DentaduraService.php** — Normalises dental face/piece states and updates tooth records
- **app/Services/CitaService.php** — Appointment creation (with push), estado update, FullCalendar data
- **app/Services/NotificacionService.php** — FCM v1 push notification logic (OAuth2 JWT, graceful failure)
- **app/Http/Requests/** — 7 FormRequests: Clientes (store/update), Citas (store/estado), Historial (store/update), Contacto
- **app/Http/Controllers/Api/** — 4 split API controllers: Auth, Cita, Cliente, Dispositivo
- **app/Http/Middleware/EsGestorOPropietario.php** — Allows gestor OR client owner (used on API cliente routes)
- **public/css/app.css** — All application CSS (no inline styles, no alias variables)
- **public/js/app.js** — Sidebar/navbar toggle JS
- **public/js/odontograma.js** — Odontogram modal JS (uses `window.dentaduraData` / `window.dentaduraUrl`)
- **app/Models/Dentadura.php** — Dental chart model with all constants and helper methods
- **app/Models/Cliente.php** — Patient model (soft deletes, num_filiacion generation, buscar scope)
- **app/Models/HistorialClinico.php** — Clinical history (auto-saldo hook)
- **app/Models/Cita.php** — Appointment model (state machine, scopes, color accessors)
- **app/Models/User.php** — User model with `isGestor()`/`isCliente()` helpers
- **resources/views/clientes/show.blade.php** — Patient profile shell (~25 lines), delegates to 5 partials
- **resources/views/clientes/partials/** — 5 partials: `_datos-personales`, `_odontograma`, `_historial`, `_citas`, `_modales`
- **resources/views/components/_diente-inner.blade.php** — Reusable oclusal tooth SVG (clipPath + grooves + 5 faces)
- **resources/views/components/odontograma-bucal.blade.php** — Buccal anatomical odontogram component
- **database/seeders/DatabaseSeeder.php** — Demo data including 32 initialized adult teeth
- **database/migrations/** — Migration files

## Design System

- **Typography**: Inter (body) + JetBrains Mono (code)
- **Colors**: Primary `#1e3a8a`, Secondary `#2563eb`, Success `#059669`, Error `#ef4444`, Background `#f8fafc`
- **Spacing**: 1rem base, 6px–8px border-radius, 0–1px–3px shadows, 0.15s transitions

## Environment Variables

Critical `.env` configuration:

```env
# Database
DB_DATABASE=clinica_mula
DB_USERNAME=root
DB_PASSWORD=

# Firebase (required for push notifications)
FCM_SERVER_KEY=your_fcm_server_key_here
FCM_SENDER_ID=your_fcm_sender_id

# Mail (for future email notifications)
MAIL_MAILER=smtp
MAIL_HOST=mail.tudominio.com
MAIL_PORT=587
```

### Firebase Setup

To configure Firebase push notifications:
1. Create a Firebase project in [Firebase Console](https://console.firebase.google.com)
2. Create an Android app (or use existing one)
3. Generate a **Server SDK** (Firebase Admin SDK)
4. Copy the downloaded `google-services.json` contents to `.env`:
   ```env
   FCM_SERVER_KEY=<extracted from google-services.json>
   FCM_SENDER_ID=<extracted from google-services.json>
   ```
5. The full service account JSON should be stored at `.env` reference (`services.firebase.credentials`)

## Default Credentials (after `php artisan migrate --seed`)

- **Manager**: admin@clinicamula.es / Admin1234!
- **Test Patient**: paciente@example.com / Cliente1234!

## Deployment Notes

- Production: Webempresa hosting (PHP 8.2, cPanel)
- DocumentRoot points to `/public` directory
- Storage permissions: `chmod -R 775 storage bootstrap/cache`
- Run `php artisan migrate --seed --force` for production DB setup
- Cache commands for production: `php artisan config:cache`, `route:cache`, `view:cache`
- Disable Telescope in production
- Remove `--ansi` flag in production

## Testing

```bash
# Run all tests
php vendor/bin/phpunit

# Run a specific test
php vendor/bin/phpunit tests/Feature/CitaTest.php

# Run with coverage
php vendor/bin/phpunit --coverage
```

## API Reference

### Authentication

```
POST /api/login
{
  "email": "user@example.com",
  "password": "password"
}
// Response: { "token": "string", "user": { "id": ..., "name": ..., "email": ..., "role": "..." } }
```

### Create Appointment (API)

```
POST /api/citas
{
  "cliente_id": 1,
  "fecha_hora": "2026-05-15T10:00:00",
  "duracion_minutos": 45,
  "motivo": "Revisión semestral"
}
```

### Update Appointment Status

```
PATCH /api/citas/{id}/estado
{
  "estado": "confirmada"
}
```
