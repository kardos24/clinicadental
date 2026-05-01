# 🦷 Clínica Dental Mula — Guía completa de instalación y despliegue

## ─────────────────────────────────────────────────────────────
## ÍNDICE
## ─────────────────────────────────────────────────────────────
## 1. Requisitos del sistema
## 2. Instalación en LOCAL (desarrollo en Windows)
## 3. Despliegue en Webempresa (producción)
## 4. Configurar el Cron Job en Webempresa (recordatorios push)
## 5. Configurar Firebase Cloud Messaging (notificaciones Android)
## 6. Usuarios y credenciales por defecto
## 7. Estructura de carpetas del proyecto
## 8. API para la app Android
## 9. Próximos pasos (app Flutter)
## ─────────────────────────────────────────────────────────────


## 1. REQUISITOS DEL SISTEMA
────────────────────────────────────────────────

### Local (desarrollo)
- PHP >= 8.2
- Composer >= 2.x
- MySQL >= 8.0 (o MariaDB >= 10.5)
- Node.js >= 18 (opcional, solo si usas Vite/Mix)
- Git

### Webempresa (producción)
- Plan Profesional o superior (recomendado para SSH + Composer)
- PHP 8.2 activado en el panel (cPanel → Selector de PHP)
- MySQL incluido en el plan
- SSL activado (gratuito con Let's Encrypt en cPanel)
- Acceso SSH (solicitar en soporte de Webempresa si no está habilitado)
- Cron Jobs (disponible en cPanel)


## 2. INSTALACIÓN EN LOCAL
────────────────────────────────────────────────

### Paso 1: Copiar el proyecto a tu carpeta
Ruta del proyecto en tu equipo:
  C:\Users\Kardos\Desktop\Trabajo\Paginasweb\ClinicaMula

### Paso 2: Abrir una terminal en esa carpeta
Clic derecho en la carpeta → "Abrir en Terminal" (o usa VS Code)

### Paso 3: Instalar dependencias PHP
  composer install

### Paso 4: Configurar el entorno
  copy .env.example .env
  php artisan key:generate

Edita el archivo .env con tus datos locales:
  DB_DATABASE=clinica_mula
  DB_USERNAME=root
  DB_PASSWORD=tu_password_local

### Paso 5: Crear la base de datos
En MySQL Workbench o phpMyAdmin, crea una base de datos llamada:
  clinica_mula
  (charset: utf8mb4, collation: utf8mb4_unicode_ci)

### Paso 6: Ejecutar migraciones y datos de prueba
  php artisan migrate --seed

### Paso 7: Crear enlace de almacenamiento
  php artisan storage:link

### Paso 8: Arrancar el servidor de desarrollo
  php artisan serve

Accede en el navegador a: http://localhost:8000


## 3. DESPLIEGUE EN WEBEMPRESA
────────────────────────────────────────────────

### Opción A: Usando SSH (recomendado)

#### Paso 1: Conectar por SSH a tu hosting
  ssh tu_usuario@tudominio.com -p 22

#### Paso 2: Ir a la carpeta de tu dominio
  cd ~/public_html
  # O si tienes un subdominio o carpeta específica:
  cd ~/dominio.com/public_html

#### Paso 3: Clonar o subir el proyecto
Si usas Git:
  git clone https://github.com/tu-usuario/clinica-mula.git .

Si subes por FTP/SFTP:
  - Sube TODOS los archivos del proyecto EXCEPTO la carpeta /public
  - La carpeta /public va en public_html (raíz web)
  - El resto del proyecto (app, config, database...) sube UNA carpeta ARRIBA de public_html
    Estructura recomendada en Webempresa:
      /home/tu_usuario/clinicamula/        ← aquí va todo el proyecto
      /home/tu_usuario/clinicamula/public/ → enlaza con public_html

#### Paso 4: Instalar dependencias en el servidor
  cd ~/clinicamula
  composer install --no-dev --optimize-autoloader

#### Paso 5: Configurar .env en producción
  cp .env.example .env
  php artisan key:generate

Edita .env con los datos de producción:
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://tudominio.com

  DB_HOST=localhost
  DB_DATABASE=nombre_bd_webempresa
  DB_USERNAME=usuario_bd_webempresa
  DB_PASSWORD=password_bd_webempresa

  MAIL_MAILER=smtp
  MAIL_HOST=mail.tudominio.com
  MAIL_PORT=587
  MAIL_USERNAME=info@tudominio.com
  MAIL_PASSWORD=password_correo

Los datos de la BD los encuentras en:
  cPanel → Bases de datos MySQL → tu base de datos

#### Paso 6: Ejecutar migraciones
  php artisan migrate --seed --force

#### Paso 7: Optimizar para producción
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache

#### Paso 8: Permisos de carpetas (importante)
  chmod -R 775 storage bootstrap/cache
  chown -R www-data:www-data storage bootstrap/cache

#### Paso 9: Configurar DocumentRoot
En cPanel → Dominios → tu dominio → DocumentRoot, apuntar a:
  /home/tu_usuario/clinicamula/public

Si no puedes cambiar el DocumentRoot, mueve los archivos de /public
a public_html y actualiza el index.php para apuntar a la ruta correcta:
  require __DIR__.'/../clinicamula/vendor/autoload.php';
  ... (ajustar la ruta relativa)


### Opción B: Sin SSH (subida por FTP)

1. En tu equipo local, ejecuta: composer install --no-dev
2. Sube TODA la carpeta del proyecto por FTP a una carpeta privada (p.ej. /app)
3. Sube el contenido de /public a public_html
4. Edita public/index.php para corregir las rutas relativas si es necesario
5. Crea el .env en el servidor vía el editor de archivos de cPanel
6. Ejecuta las migraciones vía el comando de PHP en cPanel (o desde SSH)


## 4. CONFIGURAR CRON JOB EN WEBEMPRESA
────────────────────────────────────────────────

Este cron ejecuta los recordatorios automáticos de citas (cada minuto, Laravel
decide qué tareas correr según el schedule definido en routes/console.php).

En cPanel → Tareas Cron (Cron Jobs) → añadir:

  * * * * * /usr/local/bin/php /home/tu_usuario/clinicamula/artisan schedule:run >> /dev/null 2>&1

IMPORTANTE: Ajusta la ruta /home/tu_usuario/clinicamula/ a la ruta real de tu proyecto.
Para encontrar la ruta completa, en SSH ejecuta: pwd

La tarea programada enviará recordatorios push a las 9:00 cada día para
las citas del día siguiente que no hayan recibido aún recordatorio.


## 5. CONFIGURAR FIREBASE CLOUD MESSAGING
────────────────────────────────────────────────

Para que funcionen las notificaciones push en la app Android:

### Paso 1: Crear proyecto en Firebase
1. Ve a https://console.firebase.google.com
2. Crea un nuevo proyecto: "ClinicaDentalMula"
3. Añade una app Android con el package name que uses en Flutter
   (p.ej. es.clinicadentalmula.app)

### Paso 2: Obtener la Server Key
1. En Firebase Console → Configuración del proyecto → Cloud Messaging
2. Copia la "Clave de servidor" (Server Key / Legacy server key)

### Paso 3: Añadir al .env
  FCM_SERVER_KEY=AAAA...tu_server_key_aqui
  FCM_SENDER_ID=123456789

### Paso 4: Descargar google-services.json
Descárgalo desde Firebase Console y ponlo en:
  (proyecto Flutter)/android/app/google-services.json


## 6. USUARIOS Y CREDENCIALES POR DEFECTO
────────────────────────────────────────────────

Tras ejecutar `php artisan migrate --seed`:

  👨‍💼 GESTOR (acceso completo)
     Email:    admin@clinicamula.es
     Password: Admin1234!

  👤 CLIENTE DE PRUEBA
     Email:    paciente@example.com
     Password: Cliente1234!

⚠️ CAMBIA estas contraseñas inmediatamente en producción.
   Puedes hacerlo desde el panel de administración o con Tinker:
     php artisan tinker
     User::where('email','admin@clinicamula.es')->first()->update(['password' => bcrypt('NuevaPassword')]);


## 7. ESTRUCTURA DE CARPETAS DEL PROYECTO
────────────────────────────────────────────────

ClinicaMula/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ApiController.php          ← API REST para app móvil
│   │   │   ├── AuthController.php         ← Login / Registro / Logout
│   │   │   ├── CitaController.php         ← Gestión de citas + calendario
│   │   │   ├── ClienteController.php      ← CRUD de pacientes + odontograma
│   │   │   ├── ContactoController.php     ← Formulario de contacto público
│   │   │   ├── DashboardController.php    ← Dashboard del gestor
│   │   │   └── HistorialController.php    ← Historial clínico
│   │   └── Middleware/
│   │       └── EsGestor.php               ← Control de acceso por rol
│   ├── Models/
│   │   ├── Cita.php
│   │   ├── Cliente.php
│   │   ├── Dentadura.php                  ← Estado diente a diente (FDI)
│   │   ├── DispositivoPush.php            ← Tokens FCM para Android
│   │   ├── HistorialClinico.php
│   │   └── User.php
│   └── Services/
│       └── NotificacionService.php        ← Envío de push via FCM
├── bootstrap/
│   └── app.php                            ← Configuración Laravel 11
├── config/
│   └── services.php                       ← Config FCM y otros servicios
├── database/
│   ├── migrations/                        ← 6 migraciones
│   └── seeders/
│       └── DatabaseSeeder.php             ← Datos de demo
├── public/
│   ├── .htaccess                          ← Rewrite rules para Apache/Webempresa
│   └── index.php                          ← Entry point
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php                  ← Layout maestro (público + privado)
│   ├── auth/
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   ├── clientes/
│   │   ├── index.blade.php                ← Listado de pacientes
│   │   ├── show.blade.php                 ← Ficha + odontograma + historial
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   ├── citas/
│   │   ├── calendario.blade.php           ← Calendario FullCalendar
│   │   └── mis-citas.blade.php            ← Vista cliente
│   ├── dashboard/
│   │   └── index.blade.php                ← Dashboard gestor
│   └── public/
│       ├── home.blade.php                 ← Página de inicio
│       ├── servicios.blade.php
│       └── contacto.blade.php
├── routes/
│   ├── web.php                            ← Rutas web
│   ├── api.php                            ← Rutas API (Sanctum)
│   └── console.php                        ← Cron jobs / Schedule
├── .env.example                           ← Plantilla de configuración
└── composer.json                          ← Dependencias PHP


## 8. API PARA LA APP ANDROID
────────────────────────────────────────────────

La API REST está disponible en /api/ y usa Laravel Sanctum (tokens).

### Autenticación
  POST /api/login
  Body: { "email": "...", "password": "..." }
  Response: { "token": "...", "user": { "id", "name", "email", "role" } }

  POST /api/logout   (requiere Bearer token)

### Registrar dispositivo Android (para push)
  POST /api/dispositivo/token
  Body: { "token_fcm": "...", "plataforma": "android" }

### Endpoints del gestor
  GET  /api/clientes              ← Listado con búsqueda (?buscar=nombre)
  GET  /api/clientes/{id}         ← Ficha completa
  GET  /api/citas/mes?year=&month= ← Citas del mes
  POST /api/citas                 ← Crear cita
  PATCH /api/citas/{id}/estado    ← Cambiar estado
  GET  /api/clientes/{id}/historial ← Historial clínico

### Endpoints del cliente
  GET  /api/mis-citas             ← Mis citas (próximas y anteriores)
  POST /api/citas                 ← Solicitar cita

Todos los endpoints protegidos requieren:
  Header: Authorization: Bearer {token}

### Ejemplo desde Flutter/Dart
  ```dart
  final response = await http.post(
    Uri.parse('https://tudominio.com/api/login'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({'email': email, 'password': password}),
  );
  final data = jsonDecode(response.body);
  final token = data['token'];
  ```


## 9. PRÓXIMOS PASOS — APP FLUTTER ANDROID
────────────────────────────────────────────────

Para la app móvil Android se recomienda Flutter. Estructura sugerida:

  flutter_clinica_mula/
  ├── lib/
  │   ├── main.dart
  │   ├── services/
  │   │   ├── api_service.dart          ← HTTP requests a /api/
  │   │   └── notification_service.dart ← Firebase Messaging
  │   ├── screens/
  │   │   ├── login_screen.dart
  │   │   ├── calendario_screen.dart    ← Gestor: calendario citas
  │   │   ├── clientes_screen.dart      ← Gestor: lista pacientes
  │   │   ├── cliente_detalle_screen.dart
  │   │   └── mis_citas_screen.dart     ← Cliente: sus citas
  │   └── models/
  │       ├── cliente.dart
  │       ├── cita.dart
  │       └── historial.dart
  ├── android/app/google-services.json  ← Firebase (ver paso 5)
  └── pubspec.yaml

Dependencias Flutter recomendadas (pubspec.yaml):
  - http: ^1.2.0                        (llamadas a la API)
  - firebase_messaging: ^14.0.0         (notificaciones push)
  - table_calendar: ^3.0.9              (calendario visual)
  - flutter_local_notifications: ^17.0.0 (notificaciones locales)
  - shared_preferences: ^2.2.0          (guardar token sesión)
  - provider o riverpod                 (gestión de estado)


## ─────────────────────────────────────────────────────────────
## SOPORTE Y CONTACTO TÉCNICO
## ─────────────────────────────────────────────────────────────
## Proyecto generado con Laravel 11 + Blade + FullCalendar
## Notificaciones: Firebase Cloud Messaging
## Hosting: Webempresa (PHP 8.2 + MySQL)
## App móvil: Flutter (próxima fase)
## ─────────────────────────────────────────────────────────────
