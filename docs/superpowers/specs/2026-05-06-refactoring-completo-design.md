# Refactoring Completo — Clínica Dental Mula

**Fecha:** 2026-05-06  
**Rama:** `feature/refactoring-completo` (desde `develop`)  
**Estrategia:** Big Bang — todos los cambios en una sola rama, commits atómicos por capa  
**Tests:** se actualizan al final en un único commit; se acepta que estén rotos durante el proceso  
**Assets:** archivos estáticos simples (`public/css/`, `public/js/`), sin Vite/npm

---

## Contexto y motivación

El proyecto tiene bases sólidas (modelos con accessors, tests existentes, Sanctum para API) pero acumula deuda técnica en tres áreas principales:

1. **Frontend:** 973 líneas de CSS embebidas en el layout, JavaScript inline en vistas, estilos `style=""` masivos en vistas públicas, variables CSS con alias redundantes.
2. **Vistas:** `clientes/show.blade.php` tiene 629 líneas mezclando estructura, estilos, lógica y JavaScript. Componentes legacy que nadie usa.
3. **Backend:** Lógica de negocio duplicada entre controladores web y API, validación inline en lugar de FormRequests, `ApiController` monolítico de 202 líneas, métodos privados que deberían ser middlewares.

---

## Estructura de archivos resultante

```
public/
  css/
    app.css                  ← CSS extraído del layout (limpio, sin aliases)
  js/
    app.js                   ← sidebar toggle + navbar móvil
    odontograma.js           ← funciones JS del odontograma

app/
  Http/
    Controllers/
      Api/
        AuthApiController.php
        ClienteApiController.php
        CitaApiController.php
        DispositivoApiController.php
      ApiController.php      ← ELIMINADO
    Requests/
      StoreClienteRequest.php
      UpdateClienteRequest.php
      StoreCitaRequest.php
      UpdateCitaEstadoRequest.php
      StoreHistorialRequest.php
      UpdateHistorialRequest.php
      ContactoRequest.php
    Middleware/
      EsGestor.php           ← sin cambios
      EsGestorOPropietario.php  ← nuevo
  Services/
    NotificacionService.php  ← sin cambios
    DentaduraService.php     ← nuevo
    CitaService.php          ← nuevo

resources/views/
  layouts/
    app.blade.php            ← ~200 líneas, solo HTML shell
  clientes/
    show.blade.php           ← ~150 líneas, solo estructura + @includes
    partials/
      _datos-personales.blade.php
      _odontograma.blade.php
      _historial.blade.php
      _citas.blade.php
      _modales.blade.php
  components/
    _diente-inner.blade.php  ← sin cambios
    diente-cara.blade.php    ← sin cambios
    odontograma-bucal.blade.php ← sin cambios
    dentadura.blade.php      ← ELIMINADO (legacy)
    diente-svg.blade.php     ← ELIMINADO (legacy)
```

---

## Capa 1 — CSS y assets

### `public/css/app.css`

Contiene todo el CSS actualmente embebido en `layouts/app.blade.php`, con las siguientes limpiezas:

- **Eliminar aliases de variables** (`--azul`, `--azul-oscuro`, `--azul-claro`, `--verde`, `--verde-claro`, `--rojo`, `--gris-borde`, `--gris-fondo`, `--texto-med`, `--texto-muted`). Todas las vistas que los usen se actualizan para usar los nombres canónicos (`--primary`, `--gray-200`, `--text-light`, etc.).
- **Eliminar variables no usadas:** `--radius-xl`.
- **Organización en secciones** con comentarios de bloque:
  1. Variables (`:root`)
  2. Reset y base
  3. Tipografía
  4. Layout (navbar, sidebar, app-wrap, app-main, topbar)
  5. Botones
  6. Cards
  7. Tablas
  8. Formularios
  9. Alertas
  10. Badges
  11. Modales
  12. Utilidades (sidebar-toggle, overlay, mobile nav)
  13. Responsive (media queries)
- **Añadir clases CSS** para los patrones de layout más repetidos en vistas públicas (secciones con padding estándar, grids 2 columnas responsives), de modo que las vistas dejen de usar `style=""` para estos casos.

### `public/js/app.js`

Extrae el IIFE actual del layout:
- Toggle sidebar (privado) + overlay
- Toggle navbar móvil (público)
- Sin dependencias externas

### `public/js/odontograma.js`

Extrae de `clientes/show.blade.php`:
- `abrirModalDiente(num, caraResaltar)`
- `abrirModalCaraDiente(num, cara)`
- `guardarDiente()`
- Listeners de modal (cierre al clicar fuera, `Escape`)

El objeto `dentaduraData` sigue serializado desde PHP en el blade con `{!! json_encode(...) !!}` y se asigna a `window.dentaduraData` para que el JS externo pueda accederlo.

### `layouts/app.blade.php`

Queda reducido a ~200 líneas:
```html
<head>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('styles')
</head>
<body>
  <!-- sidebar / navbar según @auth -->
  @yield('content')
  <script src="{{ asset('js/app.js') }}"></script>
  @stack('scripts')
</body>
```

---

## Capa 2 — Vistas y componentes

### División de `clientes/show.blade.php`

La vista principal queda con esta estructura:

```blade
@extends('layouts.app')
@section('content')
<div class="cliente-grid">
    <div class="cliente-sidebar">
        @include('clientes.partials._datos-personales', ['cliente' => $cliente])
    </div>
    <div class="cliente-main">
        @include('clientes.partials._odontograma', ['cliente' => $cliente, 'dentadura' => $dentadura])
        @include('clientes.partials._historial', ['cliente' => $cliente, 'historial' => $historial])
        @include('clientes.partials._citas', ['cliente' => $cliente, 'citas' => $citas])
    </div>
</div>
@include('clientes.partials._modales', ['cliente' => $cliente, 'dentadura' => $dentadura])
@endsection
```

Cada partial es autocontenido: recibe solo las variables que necesita, define su propio `@push('styles')` si tiene CSS muy específico, y sus propios `@push('scripts')` si necesita JS mínimo de inicialización.

### `partials/_odontograma.blade.php`

Contiene la serialización PHP→JS de `dentaduraData`:
```blade
<script>
window.dentaduraData = {!! json_encode($dentaduraData) !!};
</script>
<script src="{{ asset('js/odontograma.js') }}"></script>
```

Incluye `@include('components._diente-inner', ...)` para cada diente del odontograma oclusal, y `<x-odontograma-bucal>` para el anatómico.

### Vistas públicas

Los `style=""` inline se reemplazan por clases definidas en `app.css`. Regla: si el mismo patrón de estilo aparece en más de un elemento, va a una clase. Si es un valor único irrepetible, puede quedar inline.

Clases nuevas a añadir en `app.css`:
- `.section-public` — padding estándar de secciones públicas
- `.section-hero` — hero con gradiente y padding mayor
- `.grid-2col` — grid 2 columnas con colapso responsive (ya existe lógica, solo formalizar la clase)
- `.cta-box` — caja de llamada a la acción (fondo azul, texto blanco, padding)

### Componentes eliminados

- `components/dentadura.blade.php` — legacy, no referenciado desde ninguna vista activa
- `components/diente-svg.blade.php` — legacy del sistema de estado único, no referenciado

---

## Capa 3 — FormRequests

Todos los FormRequests se crean en `app/Http/Requests/`. La autorización (`authorize()`) devuelve `true` en todos — el control de acceso se gestiona por middleware en las rutas.

### `StoreClienteRequest`
```php
rules(): ['apellidos' => 'required|string|max:100', 'nombre' => 'required|string|max:100',
          'edad' => 'nullable|integer|min:0|max:120', 'telefono' => 'nullable|string|max:20',
          'profesion' => 'nullable|string|max:100', 'direccion' => 'nullable|string|max:200',
          'cp' => 'nullable|string|max:5', 'observaciones' => 'nullable|string']
```

### `UpdateClienteRequest`
Idéntico a Store. No hay campo unique que ignorar (clientes no tienen email propio).

### `StoreCitaRequest`
```php
rules(): ['fecha_hora' => 'required|date|after:now', 'duracion_minutos' => 'required|integer|min:15|max:240',
          'motivo' => 'required|string|max:255', 'notas' => 'nullable|string',
          'cliente_id' => 'sometimes|required|exists:clientes,id']
```
`cliente_id` es `sometimes` porque en web se infiere del contexto; en API es obligatorio.

### `UpdateCitaEstadoRequest`
```php
rules(): ['estado' => 'required|in:pendiente,confirmada,realizada,cancelada,no_presentado']
```

### `StoreHistorialRequest` / `UpdateHistorialRequest`
Incluyen `withValidator()` para la validación de fecha con `checkdate()`:
```php
public function withValidator($validator): void {
    $validator->after(function ($v) {
        if (!checkdate($this->mes, $this->dia, $this->anio)) {
            $v->errors()->add('dia', 'La fecha no es válida.');
        }
    });
}
```

### `ContactoRequest`
```php
rules(): ['nombre' => 'required|string|max:100', 'contacto' => 'required|string|max:200',
          'asunto' => 'nullable|string', 'mensaje' => 'required|string|max:2000']
```

---

## Capa 4 — Servicios y controladores

### `DentaduraService`

```php
class DentaduraService {
    public function normalizarEstadoCara(string $valor): ?string;
    // 'sano' → null, otros valores → valor tal cual

    public function normalizarEstadoPieza(string $valor): ?string;
    // 'presente' → null, otros valores → valor tal cual

    public function actualizarDiente(Dentadura $diente, array $datos): void;
    // Aplica normalización y llama a $diente->update()
}
```

El método `normalizarEstadoCara()` se extrae de `ClienteController::actualizarDentadura()` donde actualmente vive como método privado. Los métodos de visualización (`colorCara`, `resumen`, `tienePatologia`, etc.) se quedan en el modelo `Dentadura` — son comportamiento del objeto, no lógica de aplicación.

### `CitaService`

```php
class CitaService {
    public function __construct(private NotificacionService $notificaciones) {}

    public function crearCita(array $datos, ?int $gestorId = null): Cita;
    // Crea la cita, dispara notificación push si hay gestor

    public function actualizarEstado(Cita $cita, string $estado): Cita;
    // Actualiza estado — sin validación de transición (misma lógica que el controlador actual)

    public function citasDelMes(int $year, int $month): Collection;
    // Retorna citas formateadas para FullCalendar
}
```

### Controladores web refactorizados

`ClienteController@actualizarDentadura` queda:
```php
public function actualizarDentadura(Request $request, Cliente $cliente): JsonResponse {
    $diente = $cliente->dentadura()->where('num_diente', $request->num_diente)->firstOrFail();
    $this->dentaduraService->actualizarDiente($diente, $request->all());
    return response()->json(['ok' => true]);
}
```

`CitaController@store` queda:
```php
public function store(StoreCitaRequest $request): RedirectResponse {
    $this->citaService->crearCita($request->validated(), auth()->id());
    return back()->with('success', 'Cita solicitada correctamente.');
}
```

### `ApiController` dividido

**`Api\AuthApiController`** — `login()`, `logout()`  
**`Api\ClienteApiController`** — `index()`, `show()`, `historial()`  
**`Api\CitaApiController`** — `index()` (mis-citas), `store()`, `actualizarEstado()`, `mes()`  
**`Api\DispositivoApiController`** — `registrarToken()`

Los métodos privados `requireGestor()` y `requireGestorOrOwner()` se eliminan — el control de acceso pasa a middleware en rutas.

### Middleware `EsGestorOPropietario`

```php
public function handle(Request $request, Closure $next): Response {
    $user = $request->user();
    if ($user->isGestor()) return $next($request);
    // Verifica que el recurso pertenece al usuario autenticado
    $clienteId = $request->route('id') ?? $request->input('cliente_id');
    if ($user->cliente?->id === (int)$clienteId) return $next($request);
    return response()->json(['error' => 'No autorizado.'], 403);
}
```

Se registra en `bootstrap/app.php` como `'gestor.o.propietario'`.

---

## Capa 5 — Rutas

### `routes/web.php`

```php
// Públicas (home y servicios actualmente son closures o vistas directas — se mantiene como está
// o se crea un PublicController mínimo con dos métodos que devuelven view())
Route::get('/', fn() => view('public.home'))->name('home');
Route::get('/servicios', fn() => view('public.servicios'))->name('servicios');
Route::get('/contacto', [ContactoController::class, 'show'])->name('contacto.show');
Route::post('/contacto', [ContactoController::class, 'send'])->name('contacto');

// Guest
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Autenticados
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
    Route::get('/mis-citas', [CitaController::class, 'misCitas'])->name('citas.mis-citas');
    Route::post('/citas', [CitaController::class, 'store'])->name('citas.store');

    // Solo gestor
    Route::middleware('gestor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('/clientes', ClienteController::class)->except('show');
        Route::post('/clientes/{cliente}/dentadura', [ClienteController::class, 'actualizarDentadura'])
             ->name('clientes.dentadura');
        Route::get('/citas/calendario', [CitaController::class, 'calendario'])->name('citas.calendario');
        Route::get('/api/citas/mes', [CitaController::class, 'apiMes'])->name('citas.mes');
    });
});
```

### `routes/api.php`

```php
Route::post('/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::post('/dispositivo/token', [DispositivoApiController::class, 'registrarToken']);
    Route::get('/mis-citas', [CitaApiController::class, 'index']);
    Route::get('/citas/mes', [CitaApiController::class, 'mes']);
    Route::post('/citas', [CitaApiController::class, 'store']);

    Route::middleware('gestor')->group(function () {
        Route::get('/clientes', [ClienteApiController::class, 'index']);
        Route::get('/clientes/{id}', [ClienteApiController::class, 'show']);
        Route::get('/clientes/{id}/historial', [ClienteApiController::class, 'historial']);
        Route::patch('/citas/{id}/estado', [CitaApiController::class, 'actualizarEstado']);
    });
});
```

---

## Capa 6 — Tests

### Tests que requieren actualización

- `Feature/Api/*` — actualizar namespaces de controladores importados
- `Feature/Clientes/ClienteCrudTest` — actualizar si referencia métodos movidos a servicio
- `Unit/Models/DentaduraTest` — eliminar asserts de métodos que se mueven a `DentaduraService`

### Tests nuevos

- `Unit/Services/DentaduraServiceTest` — cubre `normalizarEstadoCara()`, `normalizarEstadoPieza()`, `actualizarDiente()`
- `Unit/Services/CitaServiceTest` — cubre `crearCita()`, `actualizarEstado()`, `citasDelMes()`
- `Unit/Requests/StoreClienteRequestTest` — valida reglas del FormRequest
- `Unit/Requests/StoreHistorialRequestTest` — valida la regla `checkdate()` del `withValidator()`

### Criterio de finalización

`php vendor/bin/phpunit` pasa al 100% antes de hacer merge a `develop`.

---

## Orden de ejecución (commits)

1. `refactor: extraer CSS a public/css/app.css`
2. `refactor: extraer JS a public/js/app.js y odontograma.js`
3. `refactor: simplificar layouts/app.blade.php`
4. `refactor: dividir clientes/show.blade.php en partials`
5. `refactor: eliminar componentes legacy (dentadura, diente-svg)`
6. `refactor: convertir estilos inline de vistas públicas a clases CSS`
7. `refactor: crear FormRequests (Clientes, Citas, Historial, Contacto)`
8. `refactor: crear DentaduraService`
9. `refactor: crear CitaService`
10. `refactor: dividir ApiController en Api/*Controller`
11. `refactor: crear middleware EsGestorOPropietario`
12. `refactor: reorganizar routes/web.php y routes/api.php`
13. `test: actualizar tests existentes y añadir tests de servicios y requests`

---

## Qué NO cambia

- Esquema de base de datos — ninguna migración nueva
- Lógica de negocio — se mueve, no se altera
- Endpoints de la API — mismas URLs, mismos contratos JSON
- Modelo `Dentadura` — constantes y métodos de visualización se quedan
- `NotificacionService` — sin cambios
- `DatabaseSeeder` — sin cambios
