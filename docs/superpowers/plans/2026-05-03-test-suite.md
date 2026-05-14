# Test Suite — Clínica Dental Mula — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Crear una suite completa de Feature + Unit tests con PHPUnit 11 y MySQL, cubriendo todos los casos de uso de la aplicación.

**Architecture:** Feature tests usan `RefreshDatabase` contra `clinica_mula_test` (MySQL) y prueban rutas HTTP con `actingAs`. Unit tests de modelos prueban lógica pura sin necesidad de persistencia, excepto `HistorialClinico`, `Cliente` (scope) y `Cita` (scopes) que sí necesitan BD.

**Tech Stack:** PHPUnit 11, Laravel 11, MySQL 8.4, Laravel Sanctum, Mockery (incluido en `mockery/mockery`)

---

## Mapa de archivos

| Acción | Archivo |
|--------|---------|
| Crear | `phpunit.xml` |
| Crear | `.env.testing` |
| Crear | `tests/TestCase.php` |
| Crear | `database/factories/UserFactory.php` |
| Crear | `database/factories/ClienteFactory.php` |
| Crear | `database/factories/CitaFactory.php` |
| Crear | `database/factories/HistorialClinicoFactory.php` |
| Crear | `database/factories/DentaduraFactory.php` |
| Crear | `tests/Unit/Models/DentaduraModelTest.php` |
| Crear | `tests/Unit/Models/HistorialClinicoModelTest.php` |
| Crear | `tests/Unit/Models/ClienteModelTest.php` |
| Crear | `tests/Unit/Models/CitaModelTest.php` |
| Crear | `tests/Feature/Auth/LoginTest.php` |
| Crear | `tests/Feature/Auth/RegisterTest.php` |
| Crear | `tests/Feature/Clientes/ClienteCrudTest.php` |
| Crear | `tests/Feature/Clientes/DentaduraTest.php` |
| Crear | `tests/Feature/Citas/CitaTest.php` |
| Crear | `tests/Feature/Historial/HistorialTest.php` |
| Crear | `tests/Feature/Api/ApiAuthTest.php` |
| Crear | `tests/Feature/Api/ApiClientesTest.php` |
| Crear | `tests/Feature/Api/ApiCitasTest.php` |

---

## Task 1: Infraestructura — phpunit.xml, .env.testing, TestCase

**Files:**
- Create: `phpunit.xml`
- Create: `.env.testing`
- Create: `tests/TestCase.php`

- [ ] **Paso 1: Crear la base de datos de tests en MySQL**

Ejecutar en MySQL (Laragon):
```sql
CREATE DATABASE IF NOT EXISTS clinica_mula_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Forma rápida desde PowerShell:
```powershell
& "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS clinica_mula_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

- [ ] **Paso 2: Crear `phpunit.xml`**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory suffix=".php">./app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV"                value="testing"/>
        <env name="APP_MAINTENANCE_DRIVER" value="file"/>
        <env name="BCRYPT_ROUNDS"          value="4"/>
        <env name="CACHE_STORE"            value="array"/>
        <env name="DB_CONNECTION"          value="mysql"/>
        <env name="DB_DATABASE"            value="clinica_mula_test"/>
        <env name="MAIL_MAILER"            value="array"/>
        <env name="QUEUE_CONNECTION"       value="sync"/>
        <env name="SESSION_DRIVER"         value="array"/>
        <env name="TELESCOPE_ENABLED"      value="false"/>
    </php>
</phpunit>
```

- [ ] **Paso 3: Crear `.env.testing`**

```
APP_ENV=testing
APP_KEY=

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clinica_mula_test
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
```

- [ ] **Paso 4: Crear `tests/TestCase.php`**

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
}
```

- [ ] **Paso 5: Verificar que PHP puede ver el entorno de tests**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit --version
```

Resultado esperado: `PHPUnit 11.x.x`

- [ ] **Paso 6: Commit**

```bash
git add phpunit.xml .env.testing tests/TestCase.php
git commit -m "test: configurar infraestructura de tests con MySQL"
```

---

## Task 2: Factories — UserFactory + ClienteFactory

**Files:**
- Create: `database/factories/UserFactory.php`
- Create: `database/factories/ClienteFactory.php`

- [ ] **Paso 1: Crear `database/factories/UserFactory.php`**

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
            'role'              => 'cliente',
            'activo'            => true,
        ];
    }

    public function gestor(): static
    {
        return $this->state(['role' => 'gestor']);
    }

    public function inactivo(): static
    {
        return $this->state(['activo' => false]);
    }
}
```

- [ ] **Paso 2: Crear `database/factories/ClienteFactory.php`**

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        return [
            'apellidos'     => fake()->lastName(),
            'nombre'        => fake()->firstName(),
            'num_filiacion' => 'MUL-' . str_pad($counter, 5, '0', STR_PAD_LEFT),
            'edad'          => fake()->optional(0.7)->numberBetween(1, 90),
            'telefono'      => fake()->optional(0.7)->numerify('6########'),
            'direccion'     => fake()->optional(0.5)->streetAddress(),
            'cp'            => fake()->optional(0.5)->postcode(),
            'profesion'     => fake()->optional(0.5)->jobTitle(),
            'observaciones' => null,
            'user_id'       => null,
        ];
    }
}
```

- [ ] **Paso 3: Verificar — ejecutar tinker y crear instancias**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit --filter "will never match" 2>&1
```

Si no hay errores de autoload, las factories están bien cargadas.

- [ ] **Paso 4: Commit**

```bash
git add database/factories/UserFactory.php database/factories/ClienteFactory.php
git commit -m "test: agregar UserFactory y ClienteFactory"
```

---

## Task 3: Factories — CitaFactory + HistorialClinicoFactory + DentaduraFactory

**Files:**
- Create: `database/factories/CitaFactory.php`
- Create: `database/factories/HistorialClinicoFactory.php`
- Create: `database/factories/DentaduraFactory.php`

- [ ] **Paso 1: Crear `database/factories/CitaFactory.php`**

```php
<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class CitaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id'           => Cliente::factory(),
            'gestor_id'            => null,
            'fecha_hora'           => now()->addDays(fake()->numberBetween(1, 30))->format('Y-m-d H:i:s'),
            'duracion_minutos'     => fake()->randomElement([30, 45, 60]),
            'motivo'               => fake()->sentence(4),
            'estado'               => 'pendiente',
            'notas'                => null,
            'recordatorio_enviado' => false,
        ];
    }

    public function confirmada(): static
    {
        return $this->state(['estado' => 'confirmada']);
    }

    public function pasada(): static
    {
        return $this->state([
            'fecha_hora' => now()->subDays(fake()->numberBetween(1, 30))->format('Y-m-d H:i:s'),
        ]);
    }
}
```

- [ ] **Paso 2: Crear `database/factories/HistorialClinicoFactory.php`**

```php
<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistorialClinicoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id'             => Cliente::factory(),
            'gestor_id'              => null,
            'dia'                    => fake()->numberBetween(1, 28),
            'mes'                    => fake()->numberBetween(1, 12),
            'anio'                   => now()->year,
            'signo'                  => null,
            'diagnostico'            => fake()->sentence(),
            'num_sesiones'           => 1,
            'importe'                => fake()->randomFloat(2, 10, 500),
            'tratamiento_realizado'  => null,
            'recibo'                 => null,
            'debe'                   => 100.00,
            'haber'                  => 0.00,
            // 'saldo' se calcula automáticamente en el boot hook del modelo
        ];
    }
}
```

- [ ] **Paso 3: Crear `database/factories/DentaduraFactory.php`**

```php
<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class DentaduraFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id'          => Cliente::factory(),
            'num_diente'          => '16',
            'estado_pieza'        => 'presente',
            'cara_vestibular'     => null,
            'cara_lingual'        => null,
            'cara_mesial'         => null,
            'cara_distal'         => null,
            'cara_oclusal'        => null,
            'notas'               => null,
            'fecha_actualizacion' => now()->toDateString(),
        ];
    }
}
```

- [ ] **Paso 4: Commit**

```bash
git add database/factories/CitaFactory.php database/factories/HistorialClinicoFactory.php database/factories/DentaduraFactory.php
git commit -m "test: agregar CitaFactory, HistorialClinicoFactory y DentaduraFactory"
```

---

## Task 4: Unit — DentaduraModelTest

**Files:**
- Create: `tests/Unit/Models/DentaduraModelTest.php`

- [ ] **Paso 1: Crear `tests/Unit/Models/DentaduraModelTest.php`**

```php
<?php

namespace Tests\Unit\Models;

use App\Models\Dentadura;
use Tests\TestCase;

class DentaduraModelTest extends TestCase
{
    private function diente(array $attrs = []): Dentadura
    {
        $d = new Dentadura();
        foreach ($attrs as $k => $v) {
            $d->$k = $v;
        }
        return $d;
    }

    // ─── estaPresente ────────────────────────────────────────────────────────

    public function test_esta_presente_cuando_estado_es_null(): void
    {
        $this->assertTrue($this->diente(['num_diente' => '16'])->estaPresente());
    }

    public function test_esta_presente_cuando_estado_es_presente(): void
    {
        $this->assertTrue($this->diente(['num_diente' => '16', 'estado_pieza' => 'presente'])->estaPresente());
    }

    public function test_no_presente_cuando_ausente(): void
    {
        $this->assertFalse($this->diente(['num_diente' => '16', 'estado_pieza' => 'ausente'])->estaPresente());
    }

    public function test_no_presente_cuando_corona(): void
    {
        $this->assertFalse($this->diente(['num_diente' => '16', 'estado_pieza' => 'corona'])->estaPresente());
    }

    // ─── estadoCara ──────────────────────────────────────────────────────────

    public function test_estado_cara_devuelve_sano_cuando_null(): void
    {
        $d = $this->diente(['num_diente' => '16', 'cara_vestibular' => null]);
        $this->assertSame('sano', $d->estadoCara('vestibular'));
    }

    public function test_estado_cara_devuelve_valor_real(): void
    {
        $d = $this->diente(['num_diente' => '16', 'cara_vestibular' => 'caries']);
        $this->assertSame('caries', $d->estadoCara('vestibular'));
    }

    // ─── colorCara ───────────────────────────────────────────────────────────

    public function test_color_cara_caries(): void
    {
        $d = $this->diente(['num_diente' => '16', 'cara_vestibular' => 'caries']);
        $this->assertSame('#ef4444', $d->colorCara('vestibular'));
    }

    public function test_color_cara_sano(): void
    {
        $d = $this->diente(['num_diente' => '16', 'cara_vestibular' => null]);
        $this->assertSame('#4ade80', $d->colorCara('vestibular'));
    }

    // ─── tieneOclusal ────────────────────────────────────────────────────────

    public function test_tiene_oclusal_molar(): void
    {
        $this->assertTrue($this->diente(['num_diente' => '16'])->tieneOclusal());
    }

    public function test_no_tiene_oclusal_incisivo(): void
    {
        $this->assertFalse($this->diente(['num_diente' => '11'])->tieneOclusal());
    }

    public function test_no_tiene_oclusal_canino(): void
    {
        $this->assertFalse($this->diente(['num_diente' => '13'])->tieneOclusal());
    }

    // ─── carasAplicables ─────────────────────────────────────────────────────

    public function test_molar_tiene_5_caras(): void
    {
        $caras = $this->diente(['num_diente' => '16'])->carasAplicables();
        $this->assertCount(5, $caras);
        $this->assertContains('oclusal', $caras);
    }

    public function test_incisivo_tiene_4_caras(): void
    {
        $caras = $this->diente(['num_diente' => '11'])->carasAplicables();
        $this->assertCount(4, $caras);
        $this->assertNotContains('oclusal', $caras);
    }

    // ─── tienePatologia ──────────────────────────────────────────────────────

    public function test_no_tiene_patologia_cuando_todo_sano(): void
    {
        $this->assertFalse($this->diente(['num_diente' => '16'])->tienePatologia());
    }

    public function test_tiene_patologia_cuando_una_cara_tiene_caries(): void
    {
        $d = $this->diente(['num_diente' => '16', 'cara_vestibular' => 'caries']);
        $this->assertTrue($d->tienePatologia());
    }

    // ─── resumen ─────────────────────────────────────────────────────────────

    public function test_resumen_diente_ausente(): void
    {
        $d = $this->diente(['num_diente' => '16', 'estado_pieza' => 'ausente']);
        $this->assertSame('Ausente', $d->resumen());
    }

    public function test_resumen_diente_sano(): void
    {
        $d = $this->diente(['num_diente' => '16']);
        $this->assertSame('Sano', $d->resumen());
    }

    public function test_resumen_lista_patologias(): void
    {
        $d = $this->diente(['num_diente' => '16', 'cara_vestibular' => 'caries']);
        $this->assertStringContainsString('Vestibular: Caries', $d->resumen());
    }
}
```

- [ ] **Paso 2: Ejecutar solo este test**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit tests/Unit/Models/DentaduraModelTest.php --testdox
```

Resultado esperado: todos en verde, sin hits a la BD.

- [ ] **Paso 3: Commit**

```bash
git add tests/Unit/Models/DentaduraModelTest.php
git commit -m "test: agregar DentaduraModelTest (17 tests)"
```

---

## Task 5: Unit — HistorialClinicoModelTest

**Files:**
- Create: `tests/Unit/Models/HistorialClinicoModelTest.php`

- [ ] **Paso 1: Crear `tests/Unit/Models/HistorialClinicoModelTest.php`**

```php
<?php

namespace Tests\Unit\Models;

use App\Models\Cliente;
use App\Models\HistorialClinico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistorialClinicoModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_saldo_se_calcula_al_guardar(): void
    {
        $cliente   = Cliente::factory()->create();
        $historial = HistorialClinico::factory()->create([
            'cliente_id' => $cliente->id,
            'debe'       => 150,
            'haber'      => 50,
        ]);

        $this->assertEquals(100, $historial->saldo);
    }

    public function test_saldo_cero_cuando_debe_igual_haber(): void
    {
        $cliente   = Cliente::factory()->create();
        $historial = HistorialClinico::factory()->create([
            'cliente_id' => $cliente->id,
            'debe'       => 0,
            'haber'      => 0,
        ]);

        $this->assertEquals(0, $historial->saldo);
    }

    public function test_saldo_se_recalcula_al_actualizar(): void
    {
        $cliente   = Cliente::factory()->create();
        $historial = HistorialClinico::factory()->create([
            'cliente_id' => $cliente->id,
            'debe'       => 100,
            'haber'      => 0,
        ]);

        $historial->update(['debe' => 200, 'haber' => 80]);

        $this->assertEquals(120, $historial->fresh()->saldo);
    }

    public function test_fecha_formateada_attribute(): void
    {
        $historial = new HistorialClinico(['dia' => 5, 'mes' => 3, 'anio' => 2026]);
        $this->assertSame('5/3/2026', $historial->fecha_formateada);
    }
}
```

- [ ] **Paso 2: Ejecutar el test**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit tests/Unit/Models/HistorialClinicoModelTest.php --testdox
```

Resultado esperado: 4 tests en verde.

- [ ] **Paso 3: Commit**

```bash
git add tests/Unit/Models/HistorialClinicoModelTest.php
git commit -m "test: agregar HistorialClinicoModelTest (4 tests)"
```

---

## Task 6: Unit — ClienteModelTest

**Files:**
- Create: `tests/Unit/Models/ClienteModelTest.php`

- [ ] **Paso 1: Crear `tests/Unit/Models/ClienteModelTest.php`**

```php
<?php

namespace Tests\Unit\Models;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_generar_num_filiacion_con_id_1(): void
    {
        $cliente     = Cliente::factory()->create();
        $numGenerado = $cliente->generarNumFiliacion();

        $this->assertSame('MUL-' . str_pad($cliente->id, 5, '0', STR_PAD_LEFT), $numGenerado);
    }

    public function test_generar_num_filiacion_con_id_grande(): void
    {
        $cliente     = new Cliente();
        $cliente->id = 99999;

        $this->assertSame('MUL-99999', $cliente->generarNumFiliacion());
    }

    public function test_nombre_completo_accessor(): void
    {
        $cliente = new Cliente(['apellidos' => 'García', 'nombre' => 'Juan']);
        $this->assertSame('García, Juan', $cliente->nombre_completo);
    }

    public function test_scope_buscar_por_apellidos(): void
    {
        Cliente::factory()->create(['apellidos' => 'García', 'nombre' => 'Ana']);
        Cliente::factory()->create(['apellidos' => 'López',  'nombre' => 'Luis']);

        $resultado = Cliente::buscar('García')->get();

        $this->assertCount(1, $resultado);
        $this->assertSame('García', $resultado->first()->apellidos);
    }

    public function test_scope_buscar_por_nombre(): void
    {
        Cliente::factory()->create(['nombre' => 'María']);
        Cliente::factory()->create(['nombre' => 'Carlos']);

        $resultado = Cliente::buscar('María')->get();

        $this->assertCount(1, $resultado);
    }

    public function test_scope_buscar_por_num_filiacion(): void
    {
        $cliente = Cliente::factory()->create(['num_filiacion' => 'MUL-00042']);
        Cliente::factory()->create(['num_filiacion' => 'MUL-00001']);

        $resultado = Cliente::buscar('MUL-00042')->get();

        $this->assertCount(1, $resultado);
        $this->assertEquals($cliente->id, $resultado->first()->id);
    }

    public function test_scope_buscar_por_telefono(): void
    {
        Cliente::factory()->create(['telefono' => '666123456']);
        Cliente::factory()->create(['telefono' => '999888777']);

        $resultado = Cliente::buscar('666123')->get();

        $this->assertCount(1, $resultado);
    }
}
```

- [ ] **Paso 2: Ejecutar**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit tests/Unit/Models/ClienteModelTest.php --testdox
```

Resultado esperado: 6 tests en verde.

- [ ] **Paso 3: Commit**

```bash
git add tests/Unit/Models/ClienteModelTest.php
git commit -m "test: agregar ClienteModelTest (6 tests)"
```

---

## Task 7: Unit — CitaModelTest

**Files:**
- Create: `tests/Unit/Models/CitaModelTest.php`

- [ ] **Paso 1: Crear `tests/Unit/Models/CitaModelTest.php`**

```php
<?php

namespace Tests\Unit\Models;

use App\Models\Cita;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_estado_label_para_todos_los_estados(): void
    {
        $estados = [
            'pendiente'     => 'Pendiente',
            'confirmada'    => 'Confirmada',
            'cancelada'     => 'Cancelada',
            'realizada'     => 'Realizada',
            'no_presentado' => 'No se presentó',
        ];

        foreach ($estados as $estado => $esperado) {
            $cita = new Cita(['estado' => $estado]);
            $this->assertSame($esperado, $cita->estado_label, "Fallo en estado: {$estado}");
        }
    }

    public function test_estado_color_para_todos_los_estados(): void
    {
        $colores = [
            'pendiente'     => '#f97316',
            'confirmada'    => '#3b82f6',
            'cancelada'     => '#ef4444',
            'realizada'     => '#4ade80',
            'no_presentado' => '#6b7280',
        ];

        foreach ($colores as $estado => $esperado) {
            $cita = new Cita(['estado' => $estado]);
            $this->assertSame($esperado, $cita->estado_color, "Fallo en estado: {$estado}");
        }
    }

    public function test_scope_del_mes_solo_devuelve_citas_de_ese_mes(): void
    {
        $cliente = Cliente::factory()->create();
        Cita::factory()->create(['cliente_id' => $cliente->id, 'fecha_hora' => '2026-05-15 10:00:00', 'estado' => 'confirmada']);
        Cita::factory()->create(['cliente_id' => $cliente->id, 'fecha_hora' => '2026-06-10 10:00:00', 'estado' => 'confirmada']);

        $resultado = Cita::delMes(2026, 5)->get();

        $this->assertCount(1, $resultado);
        $this->assertSame('2026-05-15', $resultado->first()->fecha_hora->format('Y-m-d'));
    }

    public function test_scope_proximas_excluye_pasadas_y_finalizadas(): void
    {
        $cliente = Cliente::factory()->create();

        // Solo esta debe aparecer
        Cita::factory()->create(['cliente_id' => $cliente->id, 'fecha_hora' => now()->addDays(2)->format('Y-m-d H:i:s'), 'estado' => 'pendiente']);
        // Pasada → excluida
        Cita::factory()->create(['cliente_id' => $cliente->id, 'fecha_hora' => now()->subDays(1)->format('Y-m-d H:i:s'), 'estado' => 'pendiente']);
        // Futura pero cancelada → excluida
        Cita::factory()->create(['cliente_id' => $cliente->id, 'fecha_hora' => now()->addDays(3)->format('Y-m-d H:i:s'), 'estado' => 'cancelada']);
        // Futura pero realizada → excluida
        Cita::factory()->create(['cliente_id' => $cliente->id, 'fecha_hora' => now()->addDays(3)->format('Y-m-d H:i:s'), 'estado' => 'realizada']);

        $resultado = Cita::proximas()->get();

        $this->assertCount(1, $resultado);
    }
}
```

- [ ] **Paso 2: Ejecutar**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit tests/Unit/Models/CitaModelTest.php --testdox
```

Resultado esperado: 4 tests en verde.

- [ ] **Paso 3: Ejecutar todos los Unit tests juntos**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit --testsuite Unit --testdox
```

Resultado esperado: 31 tests en verde.

- [ ] **Paso 4: Commit**

```bash
git add tests/Unit/Models/CitaModelTest.php
git commit -m "test: agregar CitaModelTest (4 tests)"
```

---

## Task 8: Feature — Auth/LoginTest

**Files:**
- Create: `tests/Feature/Auth/LoginTest.php`

- [ ] **Paso 1: Crear `tests/Feature/Auth/LoginTest.php`**

```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_redirige_a_dashboard(): void
    {
        $user = User::factory()->gestor()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
             ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_cliente_redirige_a_mis_citas(): void
    {
        $user = User::factory()->create(['role' => 'cliente']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
             ->assertRedirect(route('citas.mis-citas'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_credenciales_incorrectas_devuelven_error(): void
    {
        User::factory()->create(['email' => 'test@test.com']);

        $this->post('/login', ['email' => 'test@test.com', 'password' => 'wrong'])
             ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_cuenta_desactivada_no_puede_entrar(): void
    {
        $user = User::factory()->inactivo()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
             ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_usuario_autenticado_es_redirigido_desde_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertRedirect();
    }
}
```

- [ ] **Paso 2: Ejecutar**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit tests/Feature/Auth/LoginTest.php --testdox
```

Resultado esperado: 5 tests en verde.

- [ ] **Paso 3: Commit**

```bash
git add tests/Feature/Auth/LoginTest.php
git commit -m "test: agregar LoginTest (5 tests)"
```

---

## Task 9: Feature — Auth/RegisterTest

**Files:**
- Create: `tests/Feature/Auth/RegisterTest.php`

- [ ] **Paso 1: Crear `tests/Feature/Auth/RegisterTest.php`**

```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_valido_crea_usuario_cliente(): void
    {
        $this->post('/registro', [
            'name'                  => 'Juan García',
            'email'                 => 'juan@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('citas.mis-citas'));

        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
            'role'  => 'cliente',
        ]);
    }

    public function test_email_duplicado_falla_validacion(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->post('/registro', [
            'name'                  => 'Otro',
            'email'                 => 'existing@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors('email');
    }

    public function test_password_sin_confirmar_falla(): void
    {
        $this->post('/registro', [
            'name'                  => 'Juan',
            'email'                 => 'juan@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Different123!',
        ])->assertSessionHasErrors('password');
    }

    public function test_password_corto_falla(): void
    {
        $this->post('/registro', [
            'name'                  => 'Juan',
            'email'                 => 'juan@example.com',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');
    }
}
```

- [ ] **Paso 2: Ejecutar**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit tests/Feature/Auth/RegisterTest.php --testdox
```

Resultado esperado: 4 tests en verde.

- [ ] **Paso 3: Commit**

```bash
git add tests/Feature/Auth/RegisterTest.php
git commit -m "test: agregar RegisterTest (4 tests)"
```

---

## Task 10: Feature — Clientes/ClienteCrudTest

**Files:**
- Create: `tests/Feature/Clientes/ClienteCrudTest.php`

- [ ] **Paso 1: Crear `tests/Feature/Clientes/ClienteCrudTest.php`**

```php
<?php

namespace Tests\Feature\Clientes;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_puede_listar_clientes(): void
    {
        $gestor = User::factory()->gestor()->create();
        Cliente::factory()->count(3)->create();

        $this->actingAs($gestor)->get(route('clientes.index'))->assertOk();
    }

    public function test_listado_soporta_busqueda(): void
    {
        $gestor = User::factory()->gestor()->create();
        Cliente::factory()->create(['apellidos' => 'García']);
        Cliente::factory()->create(['apellidos' => 'López']);

        $this->actingAs($gestor)
             ->get(route('clientes.index', ['buscar' => 'García']))
             ->assertOk()
             ->assertSee('García')
             ->assertDontSee('López');
    }

    public function test_gestor_puede_ver_formulario_crear(): void
    {
        $gestor = User::factory()->gestor()->create();

        $this->actingAs($gestor)->get(route('clientes.create'))->assertOk();
    }

    public function test_store_crea_cliente_con_num_filiacion(): void
    {
        $gestor = User::factory()->gestor()->create();

        $this->actingAs($gestor)->post(route('clientes.store'), [
            'apellidos' => 'Pérez',
            'nombre'    => 'Ana',
        ])->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('clientes', ['apellidos' => 'Pérez']);
        $cliente = Cliente::where('apellidos', 'Pérez')->first();
        $this->assertSame('MUL-' . str_pad($cliente->id, 5, '0', STR_PAD_LEFT), $cliente->num_filiacion);
    }

    public function test_store_inicializa_32_dientes(): void
    {
        $gestor = User::factory()->gestor()->create();

        $this->actingAs($gestor)->post(route('clientes.store'), [
            'apellidos' => 'Pérez',
            'nombre'    => 'Ana',
        ]);

        $cliente = Cliente::where('apellidos', 'Pérez')->first();
        $this->assertSame(32, $cliente->dentadura()->count());
    }

    public function test_gestor_puede_ver_cualquier_ficha(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->get(route('clientes.show', $cliente))->assertOk();
    }

    public function test_cliente_solo_puede_ver_su_propia_ficha(): void
    {
        $user   = User::factory()->create(['role' => 'cliente']);
        $propio = Cliente::factory()->create(['user_id' => $user->id]);
        $ajeno  = Cliente::factory()->create();

        $this->actingAs($user)->get(route('clientes.show', $propio))->assertOk();
        $this->actingAs($user)->get(route('clientes.show', $ajeno))->assertForbidden();
    }

    public function test_no_autenticado_redirige_a_login(): void
    {
        $cliente = Cliente::factory()->create();

        $this->get(route('clientes.show', $cliente))->assertRedirect(route('login'));
    }

    public function test_gestor_puede_actualizar_cliente(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->put(route('clientes.update', $cliente), [
            'apellidos' => 'NuevoApellido',
            'nombre'    => 'NuevoNombre',
        ])->assertRedirect(route('clientes.show', $cliente));

        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'apellidos' => 'NuevoApellido']);
    }

    public function test_gestor_puede_hacer_soft_delete(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->delete(route('clientes.destroy', $cliente));

        $this->assertSoftDeleted('clientes', ['id' => $cliente->id]);
    }

    public function test_cliente_no_puede_acceder_al_crud(): void
    {
        $user = User::factory()->create(['role' => 'cliente']);

        $this->actingAs($user)->get(route('clientes.index'))->assertForbidden();
        $this->actingAs($user)->get(route('clientes.create'))->assertForbidden();
    }

    public function test_store_requiere_apellidos_y_nombre(): void
    {
        $gestor = User::factory()->gestor()->create();

        $this->actingAs($gestor)->post(route('clientes.store'), [])
             ->assertSessionHasErrors(['apellidos', 'nombre']);
    }
}
```

- [ ] **Paso 2: Ejecutar**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit tests/Feature/Clientes/ClienteCrudTest.php --testdox
```

Resultado esperado: 12 tests en verde.

- [ ] **Paso 3: Commit**

```bash
git add tests/Feature/Clientes/ClienteCrudTest.php
git commit -m "test: agregar ClienteCrudTest (12 tests)"
```

---

## Task 11: Feature — Clientes/DentaduraTest

**Files:**
- Create: `tests/Feature/Clientes/DentaduraTest.php`

- [ ] **Paso 1: Crear `tests/Feature/Clientes/DentaduraTest.php`**

```php
<?php

namespace Tests\Feature\Clientes;

use App\Models\Cliente;
use App\Models\Dentadura;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DentaduraTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_puede_actualizar_estado_diente(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        Dentadura::factory()->create(['cliente_id' => $cliente->id, 'num_diente' => '16']);

        $this->actingAs($gestor)->postJson(route('clientes.dentadura', $cliente), [
            'dientes' => [[
                'num_diente'      => '16',
                'estado_pieza'    => 'presente',
                'cara_vestibular' => 'caries',
            ]],
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('dentadura', [
            'cliente_id'      => $cliente->id,
            'num_diente'      => '16',
            'cara_vestibular' => 'caries',
        ]);
    }

    public function test_sano_se_guarda_como_null_en_bd(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->postJson(route('clientes.dentadura', $cliente), [
            'dientes' => [[
                'num_diente'      => '11',
                'cara_vestibular' => 'sano',
            ]],
        ]);

        $this->assertDatabaseHas('dentadura', [
            'cliente_id'      => $cliente->id,
            'num_diente'      => '11',
            'cara_vestibular' => null,
        ]);
    }

    public function test_cliente_no_puede_actualizar_dentadura(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->postJson(route('clientes.dentadura', $cliente), [
            'dientes' => [['num_diente' => '11', 'cara_vestibular' => 'caries']],
        ])->assertForbidden();
    }

    public function test_diente_inexistente_se_crea(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->postJson(route('clientes.dentadura', $cliente), [
            'dientes' => [['num_diente' => '21', 'estado_pieza' => 'ausente']],
        ]);

        $this->assertDatabaseHas('dentadura', [
            'cliente_id'   => $cliente->id,
            'num_diente'   => '21',
            'estado_pieza' => 'ausente',
        ]);
    }

    public function test_estado_pieza_invalido_falla_validacion(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->postJson(route('clientes.dentadura', $cliente), [
            'dientes' => [['num_diente' => '11', 'estado_pieza' => 'invalido']],
        ])->assertUnprocessable();
    }
}
```

- [ ] **Paso 2: Ejecutar**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit tests/Feature/Clientes/DentaduraTest.php --testdox
```

Resultado esperado: 5 tests en verde.

- [ ] **Paso 3: Commit**

```bash
git add tests/Feature/Clientes/DentaduraTest.php
git commit -m "test: agregar DentaduraTest (5 tests)"
```

---

## Task 12: Feature — Citas/CitaTest

**Files:**
- Create: `tests/Feature/Citas/CitaTest.php`

- [ ] **Paso 1: Crear `tests/Feature/Citas/CitaTest.php`**

```php
<?php

namespace Tests\Feature\Citas;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(NotificacionService::class)->shouldIgnoreMissing();
    }

    public function test_gestor_puede_ver_calendario(): void
    {
        $gestor = User::factory()->gestor()->create();

        $this->actingAs($gestor)->get(route('citas.calendario'))->assertOk();
    }

    public function test_cliente_puede_ver_mis_citas(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('citas.mis-citas'))->assertOk();
    }

    public function test_cliente_sin_ficha_recibe_404(): void
    {
        $user = User::factory()->create(['role' => 'cliente']);

        $this->actingAs($user)->get(route('citas.mis-citas'))->assertNotFound();
    }

    public function test_gestor_crea_cita_con_estado_confirmada(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->post(route('citas.store'), [
            'cliente_id' => $cliente->id,
            'fecha_hora' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'motivo'     => 'Revisión',
        ])->assertRedirect();

        $this->assertDatabaseHas('citas', ['cliente_id' => $cliente->id, 'estado' => 'confirmada']);
    }

    public function test_cliente_crea_cita_con_estado_pendiente(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('citas.store'), [
            'cliente_id' => $cliente->id,
            'fecha_hora' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'motivo'     => 'Dolor muela',
        ]);

        $this->assertDatabaseHas('citas', ['cliente_id' => $cliente->id, 'estado' => 'pendiente']);
    }

    public function test_fecha_pasada_falla_validacion(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->post(route('citas.store'), [
            'cliente_id' => $cliente->id,
            'fecha_hora' => now()->subDays(1)->format('Y-m-d H:i:s'),
            'motivo'     => 'Test',
        ])->assertSessionHasErrors('fecha_hora');
    }

    public function test_gestor_puede_actualizar_estado_cita(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $cita    = Cita::factory()->create(['cliente_id' => $cliente->id]);

        $this->actingAs($gestor)->put(route('citas.update', $cita), ['estado' => 'confirmada']);

        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'estado' => 'confirmada']);
    }

    public function test_gestor_puede_eliminar_cita(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $cita    = Cita::factory()->create(['cliente_id' => $cliente->id]);

        $this->actingAs($gestor)->delete(route('citas.destroy', $cita));

        $this->assertDatabaseMissing('citas', ['id' => $cita->id]);
    }

    public function test_api_mes_devuelve_json_fullcalendar(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        Cita::factory()->create([
            'cliente_id' => $cliente->id,
            'fecha_hora' => now()->startOfMonth()->addDays(5)->format('Y-m-d H:i:s'),
            'estado'     => 'confirmada',
        ]);

        $this->actingAs($gestor)
             ->getJson(route('api.citas.mes', ['year' => now()->year, 'month' => now()->month]))
             ->assertOk()
             ->assertJsonStructure([['id', 'title', 'start', 'end', 'color']]);
    }
}
```

- [ ] **Paso 2: Ejecutar**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit tests/Feature/Citas/CitaTest.php --testdox
```

Resultado esperado: 9 tests en verde.

- [ ] **Paso 3: Commit**

```bash
git add tests/Feature/Citas/CitaTest.php
git commit -m "test: agregar CitaTest (9 tests)"
```

---

## Task 13: Feature — Historial/HistorialTest

**Files:**
- Create: `tests/Feature/Historial/HistorialTest.php`

- [ ] **Paso 1: Crear `tests/Feature/Historial/HistorialTest.php`**

```php
<?php

namespace Tests\Feature\Historial;

use App\Models\Cliente;
use App\Models\HistorialClinico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_crea_entrada_con_saldo_auto(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->post(route('historial.store', $cliente), [
            'dia'         => 3,
            'mes'         => 5,
            'anio'        => 2026,
            'diagnostico' => 'Caries molar',
            'debe'        => 150,
            'haber'       => 50,
        ])->assertRedirect();

        $this->assertDatabaseHas('historial_clinico', [
            'cliente_id'  => $cliente->id,
            'diagnostico' => 'Caries molar',
            'saldo'       => 100,
        ]);
    }

    public function test_fecha_invalida_devuelve_error(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($gestor)->post(route('historial.store', $cliente), [
            'dia'         => 31,
            'mes'         => 2,
            'anio'        => 2026,
            'diagnostico' => 'Test',
        ])->assertSessionHasErrors('dia');
    }

    public function test_gestor_puede_actualizar_historial(): void
    {
        $gestor    = User::factory()->gestor()->create();
        $cliente   = Cliente::factory()->create();
        $historial = HistorialClinico::factory()->create([
            'cliente_id' => $cliente->id,
            'gestor_id'  => $gestor->id,
        ]);

        $this->actingAs($gestor)->put(route('historial.update', $historial), [
            'dia'         => 5,
            'mes'         => 5,
            'anio'        => 2026,
            'diagnostico' => 'Actualizado',
            'debe'        => 200,
            'haber'       => 100,
        ]);

        $this->assertDatabaseHas('historial_clinico', [
            'id'          => $historial->id,
            'diagnostico' => 'Actualizado',
            'saldo'       => 100,
        ]);
    }

    public function test_gestor_puede_eliminar_historial(): void
    {
        $gestor    = User::factory()->gestor()->create();
        $cliente   = Cliente::factory()->create();
        $historial = HistorialClinico::factory()->create(['cliente_id' => $cliente->id]);

        $this->actingAs($gestor)->delete(route('historial.destroy', $historial));

        $this->assertDatabaseMissing('historial_clinico', ['id' => $historial->id]);
    }

    public function test_cliente_no_puede_crear_historial(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('historial.store', $cliente), [
            'dia'         => 1,
            'mes'         => 1,
            'anio'        => 2026,
            'diagnostico' => 'Test',
        ])->assertForbidden();
    }
}
```

- [ ] **Paso 2: Ejecutar**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit tests/Feature/Historial/HistorialTest.php --testdox
```

Resultado esperado: 5 tests en verde.

- [ ] **Paso 3: Commit**

```bash
git add tests/Feature/Historial/HistorialTest.php
git commit -m "test: agregar HistorialTest (5 tests)"
```

---

## Task 14: Feature — Api/ApiAuthTest

**Files:**
- Create: `tests/Feature/Api/ApiAuthTest.php`

- [ ] **Paso 1: Crear `tests/Feature/Api/ApiAuthTest.php`**

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_devuelve_token_y_datos_usuario(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertOk()
          ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);
    }

    public function test_login_credenciales_incorrectas_devuelve_401(): void
    {
        User::factory()->create(['email' => 'test@test.com']);

        $this->postJson('/api/login', [
            'email'    => 'test@test.com',
            'password' => 'wrong',
        ])->assertUnauthorized();
    }

    public function test_login_cuenta_desactivada_devuelve_403(): void
    {
        $user = User::factory()->inactivo()->create();

        $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertForbidden();
    }

    public function test_logout_elimina_el_token(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('app-movil')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')
             ->assertOk()
             ->assertJson(['ok' => true]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_registrar_token_fcm(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson('/api/dispositivo/token', [
            'token_fcm'  => 'firebase_token_abc123',
            'plataforma' => 'android',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('dispositivos_push', [
            'user_id'   => $user->id,
            'token_fcm' => 'firebase_token_abc123',
        ]);
    }

    public function test_segundo_registro_mismo_token_hace_upsert(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson('/api/dispositivo/token', ['token_fcm' => 'abc123']);
        $this->withToken($token)->postJson('/api/dispositivo/token', ['token_fcm' => 'abc123']);

        $this->assertDatabaseCount('dispositivos_push', 1);
    }

    public function test_rutas_protegidas_requieren_token(): void
    {
        $this->getJson('/api/clientes')->assertUnauthorized();
        $this->getJson('/api/mis-citas')->assertUnauthorized();
    }
}
```

- [ ] **Paso 2: Ejecutar**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit tests/Feature/Api/ApiAuthTest.php --testdox
```

Resultado esperado: 7 tests en verde.

- [ ] **Paso 3: Commit**

```bash
git add tests/Feature/Api/ApiAuthTest.php
git commit -m "test: agregar ApiAuthTest (7 tests)"
```

---

## Task 15: Feature — Api/ApiClientesTest

**Files:**
- Create: `tests/Feature/Api/ApiClientesTest.php`

- [ ] **Paso 1: Crear `tests/Feature/Api/ApiClientesTest.php`**

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Cliente;
use App\Models\HistorialClinico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiClientesTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_puede_listar_clientes(): void
    {
        $gestor = User::factory()->gestor()->create();
        Cliente::factory()->count(3)->create();
        $token  = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/clientes')
             ->assertOk()
             ->assertJsonStructure(['data']);
    }

    public function test_cliente_no_puede_listar_clientes(): void
    {
        $user  = User::factory()->create(['role' => 'cliente']);
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/clientes')->assertForbidden();
    }

    public function test_gestor_puede_buscar_clientes(): void
    {
        $gestor = User::factory()->gestor()->create();
        Cliente::factory()->create(['apellidos' => 'García']);
        Cliente::factory()->create(['apellidos' => 'López']);
        $token  = $gestor->createToken('app')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/clientes?buscar=García');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_gestor_puede_ver_cualquier_cliente(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $token   = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$cliente->id}")->assertOk();
    }

    public function test_cliente_puede_ver_su_propio_perfil(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$cliente->id}")->assertOk();
    }

    public function test_cliente_no_puede_ver_perfil_ajeno(): void
    {
        $user   = User::factory()->create(['role' => 'cliente']);
        $ajeno  = Cliente::factory()->create();
        $token  = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$ajeno->id}")->assertForbidden();
    }

    public function test_gestor_puede_ver_historial(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $token   = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$cliente->id}/historial")->assertOk();
    }

    public function test_propietario_puede_ver_su_historial(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$cliente->id}/historial")->assertOk();
    }

    public function test_otro_cliente_no_puede_ver_historial_ajeno(): void
    {
        $user  = User::factory()->create(['role' => 'cliente']);
        $ajeno = Cliente::factory()->create();
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson("/api/clientes/{$ajeno->id}/historial")->assertForbidden();
    }
}
```

- [ ] **Paso 2: Ejecutar**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit tests/Feature/Api/ApiClientesTest.php --testdox
```

Resultado esperado: 9 tests en verde.

- [ ] **Paso 3: Commit**

```bash
git add tests/Feature/Api/ApiClientesTest.php
git commit -m "test: agregar ApiClientesTest (9 tests)"
```

---

## Task 16: Feature — Api/ApiCitasTest

**Files:**
- Create: `tests/Feature/Api/ApiCitasTest.php`

- [ ] **Paso 1: Crear `tests/Feature/Api/ApiCitasTest.php`**

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiCitasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(NotificacionService::class)->shouldIgnoreMissing();
    }

    public function test_mis_citas_devuelve_proximas_y_anteriores(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/mis-citas')
             ->assertOk()
             ->assertJsonStructure(['proximas', 'anteriores']);
    }

    public function test_mis_citas_sin_ficha_devuelve_404(): void
    {
        $user  = User::factory()->create(['role' => 'cliente']);
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/mis-citas')->assertNotFound();
    }

    public function test_gestor_puede_obtener_citas_del_mes(): void
    {
        $gestor = User::factory()->gestor()->create();
        $token  = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/citas/mes')->assertOk();
    }

    public function test_cliente_no_puede_obtener_citas_del_mes(): void
    {
        $user  = User::factory()->create(['role' => 'cliente']);
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/citas/mes')->assertForbidden();
    }

    public function test_cliente_crea_cita_como_pendiente(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson('/api/citas', [
            'fecha_hora' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'motivo'     => 'Revisión',
        ])->assertCreated();

        $this->assertDatabaseHas('citas', ['cliente_id' => $cliente->id, 'estado' => 'pendiente']);
    }

    public function test_gestor_crea_cita_como_confirmada(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $token   = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson('/api/citas', [
            'cliente_id' => $cliente->id,
            'fecha_hora' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'motivo'     => 'Revisión',
        ])->assertCreated();

        $this->assertDatabaseHas('citas', ['cliente_id' => $cliente->id, 'estado' => 'confirmada']);
    }

    public function test_fecha_pasada_falla_validacion(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson('/api/citas', [
            'fecha_hora' => now()->subDays(1)->format('Y-m-d H:i:s'),
            'motivo'     => 'Test',
        ])->assertUnprocessable();
    }

    public function test_gestor_puede_actualizar_estado_cita(): void
    {
        $gestor  = User::factory()->gestor()->create();
        $cliente = Cliente::factory()->create();
        $cita    = Cita::factory()->create(['cliente_id' => $cliente->id]);
        $token   = $gestor->createToken('app')->plainTextToken;

        $this->withToken($token)->patchJson("/api/citas/{$cita->id}/estado", [
            'estado' => 'confirmada',
        ])->assertOk();

        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'estado' => 'confirmada']);
    }

    public function test_cliente_no_puede_actualizar_estado_cita(): void
    {
        $user    = User::factory()->create(['role' => 'cliente']);
        $cliente = Cliente::factory()->create(['user_id' => $user->id]);
        $cita    = Cita::factory()->create(['cliente_id' => $cliente->id]);
        $token   = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->patchJson("/api/citas/{$cita->id}/estado", [
            'estado' => 'cancelada',
        ])->assertForbidden();
    }
}
```

- [ ] **Paso 2: Ejecutar**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit tests/Feature/Api/ApiCitasTest.php --testdox
```

Resultado esperado: 9 tests en verde.

- [ ] **Paso 3: Ejecutar la suite completa**

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe vendor/bin/phpunit --testdox
```

Resultado esperado: **96 tests, 0 failures**.

- [ ] **Paso 4: Commit final**

```bash
git add tests/Feature/Api/ApiCitasTest.php
git commit -m "test: agregar ApiCitasTest (9 tests) — suite completa 96 tests"
```

---

## Resumen de cobertura

| Área | Archivo | Tests |
|------|---------|-------|
| Unit | DentaduraModelTest | 17 |
| Unit | HistorialClinicoModelTest | 4 |
| Unit | ClienteModelTest | 6 |
| Unit | CitaModelTest | 4 |
| Feature | LoginTest | 5 |
| Feature | RegisterTest | 4 |
| Feature | ClienteCrudTest | 12 |
| Feature | DentaduraTest | 5 |
| Feature | CitaTest | 9 |
| Feature | HistorialTest | 5 |
| Feature | ApiAuthTest | 7 |
| Feature | ApiClientesTest | 9 |
| Feature | ApiCitasTest | 9 |
| **Total** | | **96 tests** |

> **Nota sobre `NotificacionService`:** Se mockea en `CitaTest` y `ApiCitasTest` para evitar llamadas reales a Firebase. El mock ignora todos los métodos con `shouldIgnoreMissing()`.
