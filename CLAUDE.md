# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Clínica Dental Mula — Laravel 11 application for dental clinic management (patients, appointments, clinical history, dental charts) with REST API for Android app (Flutter) and Firebase push notifications.

## Development Commands

```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database (create MySQL database named 'clinica_mula' first)
php artisan migrate --seed

# Development server
php artisan serve

# Code linting (Laravel Pint)
php artisan pint

# Debugging (Laravel Telescope — available in dev only)
# Visit /telescope on your dev server

# Run tests (no test suite exists yet)
php vendor/bin/phpunit
```

## Key Business Logic

- **Patient ID (`num_filiacion`)**: Auto-generated format `MUL-00001`
- **Dental chart**: Uses FDI notation for tooth identification
- **Appointment states**: `pendiente` → `confirmada` → `realizada` | `cancelada` | `no_presentado`
- **Soft deletes**: `Cliente` model uses soft deletes
- **Balance calculation**: `HistorialClinico.saldo` is auto-calculated as `debe - haber` in the model's `saving` boot hook — never set it manually
- **Role system**: `gestor` (full access) and `cliente` (limited access); check via `$user->isGestor()` / `$user->isCliente()` helpers on the `User` model

## Database Schema

1. **users** — app users with role system (`role`: gestor/cliente, `activo`)
2. **clientes** — patient records with soft deletes, includes `num_filiacion`
3. **historial_clinico** — yearly clinical records with balance tracking
4. **dentadura** — dental chart (tooth-by-tooth status using FDI notation)
5. **citas** — appointments with status workflow, reminder tracking
6. **dispositivos_push** — FCM tokens for Android push notifications

## Models & Relationships

```
User (role: gestor/cliente)
  ├── hasOne → Cliente (only cliente-role users have a linked Cliente record)
  ├── hasMany → DispositivoPush
  └── (gestor users are referenced in Citas.gestor_id, but no hasMany on User model)

Cliente (linked to User via user_id)
  ├── belongsTo → User
  ├── hasMany → HistorialClinico
  ├── hasMany → Dentadura
  └── hasMany → Citas

Cita
  ├── belongsTo → Cliente
  └── belongsTo → User (gestor_id)

HistorialClinico
  └── belongsTo → Cliente

DispositivoPush
  └── belongsTo → User
```

## Controllers

| Controller | Purpose |
|------------|---------|
| `AuthController` | Web login/register/logout |
| `ApiController` | REST API for mobile app (Laravel Sanctum) |
| `ClienteController` | Patient CRUD + dental chart update |
| `CitaController` | Appointments + calendar + API endpoints |
| `HistorialController` | Clinical history CRUD |
| `DashboardController` | Manager dashboard with stats |
| `ContactoController` | Public contact form |
| `EsGestor` (middleware) | Restricts routes to `role === 'gestor'` users |

## Routes Structure

### Web (`routes/web.php`)
- **Public**: home, services, contact form
- **Auth**: login/register (guest), logout (auth)
- **Protected** (auth): patient CRUD, appointments, clinical history
- **Manager-only** (gestor middleware): dashboard, calendar, full CRUD operations

### API (`routes/api.php`)
- **Public**: `POST /api/login` (returns Sanctum token)
- **Protected** (`Authorization: Bearer {token}`):
  - `POST /api/logout`
  - `POST /api/dispositivo/token` — Register FCM token
  - `GET /api/clientes`, `GET /api/clientes/{id}` — Patient listing/details
  - `GET /api/mis-citas` — Current's own appointments
  - `GET /api/citas/mes` — Manager's monthly appointments (note: web routes also define `GET /api/citas/mes` via `CitaController::apiMes` returning FullCalendar-formatted JSON for the calendar view — different response format)
  - `POST /api/citas` — Create appointment
  - `PATCH /api/citas/{id}/estado` — Update appointment status
  - `GET /api/clientes/{id}/historial` — Clinical history

### Scheduled Tasks (`routes/console.php`)
- **Daily at 09:00** — Sends push notifications for next-day appointments
- Requires system cron: `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`

## API Authentication Flow

1. Android app sends credentials to `POST /api/login`
2. Returns `{ token, user: { id, name, email, role } }`
3. Include `Authorization: Bearer {token}` header in subsequent requests
4. Android device registers FCM token via `POST /api/dispositivo/token`
5. Server sends push notifications via `NotificacionService` using FCM

## Files of Interest

- **routes/web.php** — Web route definitions
- **routes/api.php** — REST API route definitions (Laravel Sanctum)
- **routes/console.php** — Scheduled task definitions
- **app/Services/NotificacionService.php** — FCM push notification logic
- **app/Models/Cliente.php** — Patient model with dental chart, balance calculation
- **app/Models/Cita.php** — Appointment model with status logic
- **database/seeders/DatabaseSeeder.php** — Demo data (manager + test patient)

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

## Default Credentials (after `php artisan migrate --seed`)

- **Manager**: admin@clinicamula.es / Admin1234!
- **Test Patient**: paciente@example.com / Cliente1234!

## Deployment Notes

- Production: Webempresa hosting (PHP 8.2, cPanel)
- DocumentRoot points to `/public` directory
- Storage permissions: `chmod -R 775 storage bootstrap/cache`
- Run `php artisan migrate --seed --force` for production DB setup
- Cache commands for production: `php artisan config:cache`, `route:cache`, `view:cache`

## UI Modernization

### Completed UI Modernization (2026-04-30)

The application has been modernized with a clean, clinical/professional design:

#### Design System
- **Typography**: Inter (body) + JetBrains Mono (code)
- **Color Palette**:
  - Primary Blue: `#1e3a8a` (Slate-900)
  - Secondary Blue: `#2563eb` (Blue-600)
  - Success Green: `#059669` (Emerald-600)
  - Error Red: `#ef4444` (Red-500)
  - Background: `#f8fafc` (Slate-50)

#### Odontograma Component

**Files:**
- `resources/views/components/dentadura.blade.php` — Main odontograma component
- `resources/views/components/diente-svg.blade.php` — SVG tooth component

**Features:**
- SVG teeth with shape based on tooth type:
  - Incisivos (11,12,21,22,31,32,41,42): Square shape
  - Caninos (13,23,33,43): Triangular shape
  - Premolares (14,15,24,25,34,35,44,45): Two cusps
  - Molares (16,17,18,26,27,28,36,37,38,46,47,48): Complex multi-cusp
- Color-coded by state (actual `Dentadura::ESTADOS` values):
  - `sano` → Green
  - `picado` → Orange
  - `caries` → Red
  - `partido` → Purple
  - `caido` → Gray
  - `puente` → Blue
  - `sustituido` → Yellow
- Tooltip on hover showing tooth description
- FDI notation (1-48 numbering system)

#### Design Principles
- Clean spacing (1rem base)
- Subtle shadows (0-1px-3px)
- Smooth transitions (0.15s ease)
- Focus rings for accessibility
- Responsive grid layouts
- Consistent border-radius (6px-8px)

### Design Documentation

Full design specification and implementation plan available at:
- **Spec**: `docs/superpowers/specs/2026-04-30-ui-modernization.md`
- **Plan**: `docs/superpowers/plans/2026-04-30-ui-modernization-plan.md`
