# Odontograma Bucal Multifaz — Plan de Implementación

> **Para agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Actualizar el componente `odontograma-bucal.blade.php` para mostrar tres vistas anatómicas por diente (lingual, oclusal, vestibular) con un nuevo componente reutilizable `_diente-cara.blade.php`.

**Architecture:** 
- Crear un nuevo componente `_diente-cara.blade.php` que renderiza una sección (cara) de un diente
- Mantener la lógica de paths SVG en el componente (reutilizable)
- Actualizar `odontograma-bucal.blade.php` para llamar al componente 2-3 veces por diente
- Agregar CSS para proporciones 40%/20%/40%

**Tech Stack:** Laravel Blade, SVG, CSS Flexbox

---

## Archivo Map

**Nuevos archivos:**
- `resources/views/components/_diente-cara.blade.php` (150 líneas aprox)

**Archivos modificados:**
- `resources/views/components/odontograma-bucal.blade.php` (actualizar loops y CSS)

---

## Task 1: Crear el componente base `_diente-cara.blade.php`

**Files:**
- Create: `resources/views/components/_diente-cara.blade.php`

- [ ] **Step 1: Crear archivo vacío y agregar props**

Crea `resources/views/components/_diente-cara.blade.php` con:

```php
{{--
    Renderiza una sección (cara) de un diente — lingual, oclusal o vestibular.
    Props:
      $num       — FDI número del diente (ej: 16)
      $cara      — 'lingual', 'oclusal', 'vestibular'
      $dentadura — colección keyed [num => Dentadura]
--}}
@props(['num' => null, 'cara' => 'lingual', 'dentadura' => null])

@php
use App\Models\Dentadura;

// TODO: Agregar lógica aquí
@endphp

<div>Placeholder</div>
```

- [ ] **Step 2: Agregar helper para obtener tipo de diente**

Dentro del bloque `@php`, agrega:

```php
/* Helper: determinar tipo de diente */
$getTipo = function(int $n) {
    return match(true) {
        in_array($n, [11,12,21,22,31,32,41,42]) => 'incisivo',
        in_array($n, [13,23,33,43])             => 'canino',
        in_array($n, [14,15,24,25,34,35,44,45]) => 'premolar',
        default                                 => 'molar',
    };
};

$tipo = $getTipo((int)$num);
```

- [ ] **Step 3: Agregar paths SVG (igual que en odontograma-bucal)**

Dentro del bloque `@php`, agrega después de `$tipo`:

```php
/* ── Paths SVG (viewBox 0 0 32 72) ────────────────────────────────── */
$svgPaths = [
    'incisivo' => [
        'corona' => 'M 6,32 L 6,15 C 6,9 9,5 12,4 L 20,4 C 23,5 26,9 26,15 L 26,32 Z',
        'raices' => [
            'M 8,32 C 8,44 10,57 12,63 C 13,67 14,69 16,70 C 18,69 19,67 20,63 C 22,57 24,44 24,32 Z',
        ],
        'surcos' => [],
    ],

    'canino' => [
        'corona' => 'M 7,32 L 7,19 C 7,13 10,8 13,5 L 16,2 L 19,5 C 22,8 25,13 25,19 L 25,32 Z',
        'raices' => [
            'M 9,32 C 9,45 11,58 13,65 C 14,68 15,70 16,71 C 17,70 18,68 19,65 C 21,58 23,45 23,32 Z',
        ],
        'surcos' => [
            ['type'=>'line','x1'=>16,'y1'=>4,'x2'=>16,'y2'=>28,'sw'=>0.6],
        ],
    ],

    'premolar' => [
        'corona' => 'M 5,32 L 5,17 C 5,11 7,7 10,5 C 12,3 14,3 16,4 C 18,3 20,3 22,5 C 25,7 27,11 27,17 L 27,32 Z',
        'raices' => [
            'M 7,32 C 7,42 8,53 9,60 C 10,64 11,67 13,67 C 14,67 15,64 15,60 C 16,53 16,42 16,32 Z',
            'M 17,32 C 17,41 18,51 19,57 C 20,61 21,63 22,63 C 23,63 24,61 24,57 C 25,51 25,41 25,32 Z',
        ],
        'surcos' => [
            ['type'=>'line','x1'=>16,'y1'=>4,'x2'=>16,'y2'=>26,'sw'=>0.7],
        ],
    ],

    'molar' => [
        'corona' => 'M 2,32 L 2,17 C 2,11 4,7 7,5 C 8,3 10,2 12,2 C 14,2 15,4 16,5 C 17,4 18,2 20,2 C 22,2 24,3 25,5 C 28,7 30,11 30,17 L 30,32 Z',
        'raices' => [
            'M 3,32 C 3,42 5,54 6,61 C 7,65 9,67 10,67 C 11,67 13,65 13,61 C 14,54 14,43 14,32 Z',
            'M 18,32 C 18,43 18,54 19,61 C 20,65 22,67 23,67 C 24,67 26,65 27,61 C 28,54 29,42 29,32 Z',
        ],
        'surcos' => [
            ['type'=>'line','x1'=>16,'y1'=>5,'x2'=>16,'y2'=>28,'sw'=>0.7],
            ['type'=>'line','x1'=>7,'y1'=>18,'x2'=>25,'y2'=>18,'sw'=>0.5],
        ],
    ],
];

$paths = $svgPaths[$tipo];
```

- [ ] **Step 4: Agregar colores base**

Dentro del bloque `@php`, agrega después de `$paths`:

```php
/* ── Colores ────────────────────────────────────────────────────── */
$colorMarfil  = '#f7f0e2';   // diente sano
$strokeDiente = '#374151';   // contorno diente
$strokeRaiz   = '#9ca3af';   // contorno raíz
```

- [ ] **Step 5: Agregar lógica de coloreo**

Dentro del bloque `@php`, agrega después de los colores:

```php
/* ── Obtener color para la cara específica ────────────────────────── */
$d = $dentadura[(string)$num] ?? null;
$estadoPieza = $d?->estado_pieza ?? 'presente';
$esPresente = ($estadoPieza === 'presente' || $estadoPieza === null);

if ($esPresente) {
    // Diente presente: colorea según la cara específica
    $estadoCara = $d->{"cara_" . $cara} ?? null;  // null = 'sano'
    $fill = (Dentadura::ESTADOS_CARA[$estadoCara]['color'] ?? $colorMarfil);
    $ausente = false;
} else {
    // Diente ausente/corona/puente/etc: color uniforme
    $meta = Dentadura::ESTADOS_PIEZA[$estadoPieza] ?? [];
    $fill = $meta['color'] ?? '#6b7280';
    $ausente = ($estadoPieza === 'ausente');
}

$raizFill = $esPresente ? '#e8dece' : $fill;  // raíz más clara si sano
```

- [ ] **Step 6: Reemplazar placeholder con SVG**

Reemplaza `<div>Placeholder</div>` con:

```php
<svg class="odon-svg odon-cara-{{ $cara }}" viewBox="0 0 32 72" width="34" height="76"
     xmlns="http://www.w3.org/2000/svg">

    {{-- Raíces --}}
    @foreach($paths['raices'] as $rp)
    <path d="{{ $rp }}"
          fill="{{ $raizFill }}"
          stroke="{{ $strokeRaiz }}"
          stroke-width="0.9"
          stroke-linejoin="round"/>
    @endforeach

    {{-- Corona --}}
    @if($ausente)
        {{-- Contorno punteado + X --}}
        <path d="{{ $paths['corona'] }}"
              fill="none"
              stroke="#94a3b8"
              stroke-width="0.8"
              stroke-dasharray="2,2"/>
        <line x1="10" y1="10" x2="22" y2="28" stroke="#94a3b8" stroke-width="1.2"/>
        <line x1="22" y1="10" x2="10" y2="28" stroke="#94a3b8" stroke-width="1.2"/>
    @else
        <path d="{{ $paths['corona'] }}"
              fill="{{ $fill }}"
              stroke="{{ $strokeDiente }}"
              stroke-width="1.1"
              stroke-linejoin="round"/>

        {{-- Surcos decorativos --}}
        @foreach($paths['surcos'] as $s)
            @if($s['type'] === 'line')
            <line x1="{{ $s['x1'] }}" y1="{{ $s['y1'] }}"
                  x2="{{ $s['x2'] }}" y2="{{ $s['y2'] }}"
                  stroke="{{ $strokeDiente }}"
                  stroke-width="{{ $s['sw'] }}"
                  stroke-opacity="0.45"
                  pointer-events="none"/>
            @endif
        @endforeach
    @endif

</svg>
```

- [ ] **Step 7: Commit**

```bash
git add resources/views/components/_diente-cara.blade.php
git commit -m "feat: crear componente _diente-cara para renderizar caras de dientes"
```

---

## Task 2: Actualizar loops en `odontograma-bucal.blade.php` — Arcada superior

**Files:**
- Modify: `resources/views/components/odontograma-bucal.blade.php:158-232` (arcada superior)

- [ ] **Step 1: Crear helper para determinar si tiene oclusal**

En el bloque `@php` (línea ~116), agrega después de `$getTipo`:

```php
/* Determinar si un diente tiene cara oclusal */
$tieneOclusal = fn(int $n) => in_array($n, Dentadura::DIENTES_CON_OCLUSAL);
```

- [ ] **Step 2: Reemplazar loop de arcada superior**

Reemplaza las líneas 160-232 (todo el loop de `@foreach($superiores as $i => $num)` con el siguiente):

```php
@foreach($superiores as $i => $num)
    @if($i === 8)<div class="odon-bucal-sep"></div>@endif
    @php
        $tipo  = $getTipo($num);
        $td    = $tdato($num);
        $tieneOcl = $tieneOclusal($num);
    @endphp
    <div class="odon-bucal-diente{{ $td['ausente'] ? ' odon-ausente' : '' }}{{ !$tieneOcl ? ' odon-diente-sin-oclusal' : '' }}"
         title="{{ $td['tooltip'] }}"
         @if(auth()->user()->isGestor())
             onclick="abrirModalDiente({{ $num }})"
         @endif>

        {{-- Número arriba --}}
        <span class="odon-num">{{ $num }}</span>

        {{-- Cara lingual --}}
        <x-diente-cara :num="$num" cara="lingual" :dentadura="$dentadura" />

        {{-- Cara oclusal (solo si el diente la tiene) --}}
        @if($tieneOcl)
        <x-diente-cara :num="$num" cara="oclusal" :dentadura="$dentadura" />
        @endif

        {{-- Cara vestibular --}}
        <x-diente-cara :num="$num" cara="vestibular" :dentadura="$dentadura" />
    </div>
@endforeach
```

- [ ] **Step 3: Verificar que se removen los `$svgPaths` y helpers del scope**

Busca en `odontograma-bucal.blade.php` la sección `/* ── Paths SVG ──... */` (alrededor de línea 47-110) y elimina TODO ese bloque (los comentarios + la definición `$svgPaths = [...]`).

También elimina la variable `$strokeDiente`, `$strokeRaiz`, `$colorMarfil` si aparecen en el scope principal.

- [ ] **Step 4: Commit**

```bash
git add resources/views/components/odontograma-bucal.blade.php
git commit -m "feat: actualizar arcada superior para renderizar múltiples caras con x-diente-cara"
```

---

## Task 3: Actualizar loops en `odontograma-bucal.blade.php` — Arcada inferior

**Files:**
- Modify: `resources/views/components/odontograma-bucal.blade.php:238-302` (arcada inferior)

- [ ] **Step 1: Reemplazar loop de arcada inferior**

Reemplaza las líneas 238-302 (todo el loop de arcada inferior) con:

```php
<div class="odon-bucal-row">
    @foreach($inferiores as $i => $num)
        @if($i === 8)<div class="odon-bucal-sep"></div>@endif
        @php
            $tipo  = $getTipo($num);
            $td    = $tdato($num);
            $tieneOcl = $tieneOclusal($num);
        @endphp
        <div class="odon-bucal-diente{{ $td['ausente'] ? ' odon-ausente' : '' }}{{ !$tieneOcl ? ' odon-diente-sin-oclusal' : '' }}"
             title="{{ $td['tooltip'] }}"
             @if(auth()->user()->isGestor())
                 onclick="abrirModalDiente({{ $num }})"
             @endif>

            {{-- Cara lingual (se verá debajo con scaleY(-1)) --}}
            <x-diente-cara :num="$num" cara="lingual" :dentadura="$dentadura" />

            {{-- Cara oclusal (solo si el diente la tiene) --}}
            @if($tieneOcl)
            <x-diente-cara :num="$num" cara="oclusal" :dentadura="$dentadura" />
            @endif

            {{-- Cara vestibular (se verá arriba con scaleY(-1)) --}}
            <x-diente-cara :num="$num" cara="vestibular" :dentadura="$dentadura" />

            {{-- Número abajo --}}
            <span class="odon-num">{{ $num }}</span>
        </div>
    @endforeach
</div>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/odontograma-bucal.blade.php
git commit -m "feat: actualizar arcada inferior para renderizar múltiples caras con x-diente-cara"
```

---

## Task 4: Actualizar CSS en `odontograma-bucal.blade.php`

**Files:**
- Modify: `resources/views/components/odontograma-bucal.blade.php:311-389` (bloque `@push('styles')`)

- [ ] **Step 1: Reemplazar estilos de `.odon-bucal-diente`**

Busca `.odon-bucal-diente {` y reemplaza TODO el bloque (incluyendo cierre `}`) con:

```css
.odon-bucal-diente {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1px;
    cursor: pointer;
    transition: transform .12s;
    position: relative;
    padding: 1px 0;
}
```

- [ ] **Step 2: Agregar estilos para cada cara**

Después del cierre de `.odon-bucal-diente`, agrega:

```css
.odon-svg {
    display: block;
    overflow: visible;
    filter: drop-shadow(0 1px 1px rgba(0,0,0,.12));
    flex-shrink: 0;
}

.odon-cara-lingual,
.odon-cara-oclusal,
.odon-cara-vestibular {
    width: 34px;
}

/* Proporciones de altura — 3 secciones */
.odon-cara-lingual,
.odon-cara-vestibular {
    height: calc(76px * 0.4);  /* 40% */
}

.odon-cara-oclusal {
    height: calc(76px * 0.2);  /* 20% */
}

/* Para dientes sin oclusal (caninos/incisivos) — 2 secciones */
.odon-diente-sin-oclusal .odon-cara-lingual,
.odon-diente-sin-oclusal .odon-cara-vestibular {
    height: 38px;  /* 50% de 76px */
}
```

- [ ] **Step 3: Verificar que `.odon-svg-flip` se puede remover**

Si existe una clase `.odon-svg-flip` que solo se usaba en la arcada inferior anterior, puedes dejarla (no hace daño) o removerla. El volteo ahora se hace en `.odon-bucal-row:last-of-type` con `transform: scaleY(-1)`.

- [ ] **Step 4: Actualizar `.odon-bucal-row:last-of-type`**

Busca `.odon-bucal-row:last-of-type` y asegúrate de que tiene:

```css
.odon-bucal-row:last-of-type,
.odon-bucal-row + .odon-bucal-row {
    align-items: flex-start;
    transform: scaleY(-1);
}
```

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/odontograma-bucal.blade.php
git commit -m "feat: agregar CSS para proporciones de altura de caras (40%/20%/40%)"
```

---

## Task 5: Verificación en navegador — Ficha de cliente

**Files:**
- Test: `http://127.0.0.1:8080/clientes/{id}` (ficha de cualquier cliente con dentadura)

- [ ] **Step 1: Lanzar servidor Laragon**

Asegúrate de que Apache está corriendo:

```powershell
# Si no está corriendo, inicia Laragon o ejecuta:
& "C:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18\bin\httpd.exe"
```

- [ ] **Step 2: Limpiar vistas compiladas (importante después de cambios en componentes)**

```bash
php artisan view:clear
```

- [ ] **Step 3: Abrir ficha de cliente**

Navega a `http://127.0.0.1:8080/clientes/1` (o cualquier cliente existente)

Verifica que aparezca el odontograma bucal con las tres secciones por diente.

- [ ] **Step 4: Verificar molares/premolares tienen 3 secciones**

Busca un molar (ej. 16, 26, 36, 46) o premolar (14, 15, 24, 25, etc.) en el odontograma.

Debe mostrar:
- Arriba (más pequeño, gris): Lingual
- Medio (muy pequeño, gris): Oclusal
- Abajo (más grande, gris): Vestibular

- [ ] **Step 5: Verificar caninos/incisivos tienen 2 secciones**

Busca un canino (13, 23, 33, 43) o incisivo (11, 12, 21, 22, etc.).

Debe mostrar:
- Arriba (50%): Lingual
- Abajo (50%): Vestibular

(Sin sección oclusal en el medio)

- [ ] **Step 6: Verificar coloreo**

Abre el modal de un diente para ver su estado. Luego mira cada sección:

- Si cara lingual es "caries": la sección lingual debería ser roja
- Si cara vestibular es "sano": la sección vestibular debería ser beige
- etc.

- [ ] **Step 7: Verificar click abre modal**

Haz clic en cualquier sección de un diente. Debe abrir el modal completo con los 5 selects (cara_vestibular, cara_lingual, cara_mesial, cara_distal, cara_oclusal).

- [ ] **Step 8: Verificar hover**

Pasa el mouse sobre un diente. Debe escalar (más grande) y oscurecerse ligeramente.

- [ ] **Step 9: Verificar arcada inferior**

Verifica que la arcada inferior se vea correctamente invertida (raíces arriba).

- [ ] **Step 10: Editar una cara**

En el modal, cambia el estado de una cara (ej. cara_vestibular = "caries"). Guarda.

Verifica que:
- El color de la sección correspondiente cambió en el odontograma
- Las otras secciones no cambiaron
- El tooltip actualiza el resumen

- [ ] **Step 11: Commit (opcional si no hay cambios de código)**

```bash
# Si todo anda bien y no hay cambios pendientes:
git status
```

---

## Task 6: Verificación visual — Comparar con referencia

**Files:**
- Reference: `https://clinic-cloud.com/hubfs/Imported_Blog_Media/ejemplo-de-odontograma-2-1.jpg`

- [ ] **Step 1: Abrir referencia visual**

Abre la URL de referencia en otra pestaña.

- [ ] **Step 2: Comparar estructura**

En la referencia deberías ver:
- Dientes con **vista frontal** (clara) + **vista trasera** (más opaca/sombreada) debajo
- En molares, la **base del diente** visible en el medio
- Todos los dientes con raíz visible

En tu implementación, verifica que:
- Hay 3 secciones por molar/premolar
- Hay 2 secciones por canino/incisivo
- La proporción visual es similar (no demasiado grande la base)

- [ ] **Step 3: Verificar colores aplicados**

En la referencia, si un diente tiene caries, toda la sección frontal es roja.

En tu implementación:
- Si solo la cara vestibular tiene caries → solo la sección vestibular es roja
- Si la cara lingual también tiene caries → ambas secciones (lingual + vestibular) son rojas
- Esto es **correcto** porque muestra más detalle de estado por cara

- [ ] **Step 4: Verificar interacción**

En la referencia no hay interacción (es estática). En tu caso:
- Clickear cualquier sección abre el modal para editar
- Esto es **mejoría** sobre la referencia

---

## Task 7: Casos edge — Dientes ausentes y estados especiales

**Files:**
- Test: `http://127.0.0.1:8080/clientes/{id}` (ficha de cliente)

- [ ] **Step 1: Crear un diente ausente**

Abre un cliente, edita un diente y cambia `estado_pieza = "ausente"`. Guarda.

- [ ] **Step 2: Verificar diente ausente**

En el odontograma, todas las 3 secciones (o 2 si es canino) deben mostrar:
- Contorno punteado (no relleno sólido)
- Una X en el centro
- Tooltip: "Diente XX — Ausente"

- [ ] **Step 3: Crear diente con corona**

Edita otro diente y cambia `estado_pieza = "corona"`. Guarda.

- [ ] **Step 4: Verificar diente con corona**

Todas las secciones deben mostrar el color uniforme de "corona" (gris/marrón según ESTADOS_PIEZA).

- [ ] **Step 5: Verificar que edición sigue funcionando**

Abre el modal de un diente con corona/ausente. Verifica que puedas cambiar el estado_pieza de vuelta a "presente".

- [ ] **Step 6: Commit (si hay cambios)**

```bash
git status
```

---

## Task 8: Responsive — Mobile

**Files:**
- Test: `http://127.0.0.1:8080/clientes/{id}` (ficha de cliente)

- [ ] **Step 1: Abrir DevTools**

En Chrome/Firefox, abre DevTools (F12) y ve a **Device Toolbar** (Ctrl+Shift+M).

- [ ] **Step 2: Ver en mobile (375px de ancho)**

Selecciona "iPhone 12" o un dispositivo con ~375px de ancho.

- [ ] **Step 3: Verificar odontograma legible**

El odontograma debe seguir siendo clickeable y visible. Los dientes pueden ser pequeños pero no ilegibles.

Si los dientes son demasiado pequeños (< 20px de ancho), verifica que el CSS `.odon-bucal-diente` escala bien en mobile.

- [ ] **Step 4: Verificar scroll**

En mobile, el odontograma puede necesitar scroll horizontal. Verifica que se puede ver completo.

- [ ] **Step 5: Commit (si hay cambios CSS)**

```bash
git status
```

---

## Task 9: Clean-up — Remover código antiguo si es necesario

**Files:**
- Modify: `resources/views/components/odontograma-bucal.blade.php`

- [ ] **Step 1: Verificar que no quedan referencias a `$svgPaths` en odontograma-bucal**

Busca en el archivo por `$svgPaths`. No debería haber ninguna (todas deberían estar en `_diente-cara.blade.php`).

Si encuentras referencias, elimínalas.

- [ ] **Step 2: Verificar que `$getTipo` se usa solo para helpers (leyenda)**

`$getTipo` se puede mantener en `odontograma-bucal.blade.php` si se usa en otra parte, pero para los loops debería estar en `_diente-cara.blade.php`.

Verifica que no hay conflictos de definición.

- [ ] **Step 3: Commit final**

```bash
git add resources/views/components/odontograma-bucal.blade.php resources/views/components/_diente-cara.blade.php
git commit -m "refactor: clean-up — remover código duplicado de odontograma-bucal"
```

---

## Task 10: Validación final — Checklist de spec

**Files:**
- Reference: `docs/superpowers/specs/2026-05-02-odontograma-bucal-multifaz-design.md`

- [ ] **Step 1: Verificar todos los casos de la spec**

Abre la spec y revisa cada requirimiento:

- [ ] ✅ Molares/premolares muestran 3 secciones (lingual 40% + oclusal 20% + vestibular 40%)
- [ ] ✅ Caninos/incisivos muestran 2 secciones (lingual 50% + vestibular 50%)
- [ ] ✅ Cada sección colorea según su cara (lingual, oclusal, vestibular)
- [ ] ✅ Click en cualquier sección abre modal completo con todas las caras
- [ ] ✅ Hover escala y oscurece
- [ ] ✅ Tooltip muestra resumen
- [ ] ✅ Dientes ausentes muestran contorno punteado + X
- [ ] ✅ Edición de cara individual sigue funcionando
- [ ] ✅ Arcada inferior se voltea correctamente
- [ ] ✅ Responsive en mobile

- [ ] **Step 2: Commit final**

```bash
git status
git log --oneline | head -10
```

---

## Notas de Implementación

1. **Reutilización**: El componente `_diente-cara.blade.php` es completamente reutilizable — puede ser usado en otros odontogramas sin modificación.

2. **Coloreo**: La lógica de coloreo está centralizada en `_diente-cara.blade.php`. Cambios futuros al coloreo solo requieren modificar ese archivo.

3. **SVG paths**: Los paths SVG son idénticos a los originales. No se modifican, solo se reutilizan.

4. **CSS proporciones**: Las alturas se calculan con `calc()` para que sean escalables si cambias el tamaño base (76px).

5. **Arcada inferior**: El volteo `scaleY(-1)` en el contenedor `.odon-bucal-row` voltea también todos los SVGs interiores, que es el comportamiento deseado.

6. **Performance**: Múltiples SVGs por diente (2-3) aumentan el DOM ligeramente, pero sigue siendo aceptable para 32 dientes × ~2.5 promedio = ~80 SVGs. No hay impacto visible.
