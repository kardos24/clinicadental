# Odontograma Bucal Multifaz — Diseño de Especificación

**Fecha**: 2026-05-02  
**Rama**: `feature/odontograma-anatomico`  
**Objetivo**: Mejorar el componente `odontograma-bucal.blade.php` para mostrar tres vistas anatómicas de cada diente (trasera, base, frontal) con capacidad de edición por cara.

---

## 1. Visión General

El odontograma bucal actual muestra solo la vista frontal (vestibular) de cada diente. El nuevo diseño debe mostrar **tres secciones por diente**:

1. **Arriba (40%)**: Vista trasera (cara lingual)
2. **Centro (20%)**: Base del diente (cara oclusal) — solo molares y premolares
3. **Abajo (40%)**: Vista frontal (cara vestibular)

Cada sección se colorea según el estado de su cara específica (lingual, oclusal, vestibular). La interacción permanece igual: clickear cualquier parte abre el modal para editar todas las caras del diente.

---

## 2. Estructura Visual

### 2.1 Tipos de dientes y secciones

| Tipo | Secciones | Ejemplo |
|------|-----------|---------|
| **Molares** | Lingual + Oclusal + Vestibular | Dientes 16, 26, 36, 46 |
| **Premolares** | Lingual + Oclusal + Vestibular | Dientes 14, 15, 24, 25, 34, 35, 44, 45 |
| **Caninos** | Lingual + Vestibular | Dientes 13, 23, 33, 43 |
| **Incisivos** | Lingual + Vestibular | Dientes 11, 12, 21, 22, 31, 32, 41, 42 |

### 2.2 Proporciones de altura

Para dientes con 3 secciones (molares y premolares):
- Lingual: 40% del alto total
- Oclusal: 20% del alto total
- Vestibular: 40% del alto total

Para dientes con 2 secciones (caninos e incisivos):
- Lingual: 50% del alto total
- Vestibular: 50% del alto total

### 2.3 Estructura HTML por diente

**Para molares/premolares (3 secciones):**
```
<div class="odon-bucal-diente">
  <span class="odon-num">16</span>
  
  <!-- Cara lingual -->
  <x-diente-cara :num="16" cara="lingual" :dentadura="$dentadura" />
  
  <!-- Cara oclusal -->
  <x-diente-cara :num="16" cara="oclusal" :dentadura="$dentadura" />
  
  <!-- Cara vestibular -->
  <x-diente-cara :num="16" cara="vestibular" :dentadura="$dentadura" />
</div>
```

**Para caninos/incisivos (2 secciones):**
```
<div class="odon-bucal-diente">
  <span class="odon-num">13</span>
  
  <!-- Cara lingual -->
  <x-diente-cara :num="13" cara="lingual" :dentadura="$dentadura" />
  
  <!-- Cara vestibular -->
  <x-diente-cara :num="13" cara="vestibular" :dentadura="$dentadura" />
</div>
```

---

## 3. Componente Reutilizable: `_diente-cara.blade.php`

### 3.1 Props

```php
@props([
    'num'         => null,      // FDI número del diente (ej: 16)
    'cara'        => 'lingual',  // 'lingual', 'oclusal', 'vestibular'
    'dentadura'   => null,      // Colección keyed [num => Dentadura]
])
```

### 3.2 Responsabilidades

1. **Determinar tipo de diente** (incisivo/canino/premolar/molar)
2. **Obtener paths SVG** del tipo (corona + raíces)
3. **Obtener color de la cara específica**:
   - Si `estado_pieza = 'presente'` → color de `$d->{"cara_" . $cara}` (lingual, oclusal, vestibular)
   - Si `estado_pieza` es otro estado (ausente, corona, etc.) → color uniforme del estado
4. **Renderizar SVG** con la corona + raíces coloreadas
5. **Aplicar altura proporcional** según contexto (full 100% si es sección única, 40%/20% si hay múltiples)

### 3.3 Estructura interna

```php
@php
  $d = $dentadura[(string)$num] ?? null;
  $tipo = getTipo($num);  // incisivo, canino, premolar, molar
  $paths = $svgPaths[$tipo];  // corona, raices, surcos
  
  // Color: según cara específica
  $fill = getColorParaCara($d, $cara);
@endphp

<svg class="odon-svg odon-cara-{{ $cara }}" viewBox="0 0 32 72">
  <!-- Raíces -->
  @foreach($paths['raices'] as $rp)
    <path d="{{ $rp }}" fill="{{ $fill }}" />
  @endforeach
  
  <!-- Corona -->
  <path d="{{ $paths['corona'] }}" fill="{{ $fill }}" />
  
  <!-- Surcos decorativos -->
  @foreach($paths['surcos'] as $s)
    <line ... />
  @endforeach
</svg>
```

---

## 4. Coloreo y Lógica de Estado

### 4.1 Regla general

Para cada sección (cara), el color se determina así:

```php
$estadoPieza = $d->estado_pieza ?? 'presente';

if ($estadoPieza === 'presente' || $estadoPieza === null) {
    // Diente presente: colorea según la cara específica
    $estadoCara = $d->{"cara_" . $cara} ?? null;  // null = 'sano'
    $fill = Dentadura::ESTADOS_CARA[$estadoCara]['color'] ?? $colorMarfil;
} else {
    // Diente ausente/corona/puente/etc: color uniforme
    $fill = Dentadura::ESTADOS_PIEZA[$estadoPieza]['color'];
}
```

### 4.2 Casos especiales

| Estado | Comportamiento | Ejemplo |
|--------|----------------|---------|
| Presente | Cada cara se colorea individualmente | Lingual puede ser sano, vestibular caries |
| Ausente | Las 3 secciones muestran contorno punteado + X | Todas iguales |
| Corona | Las 3 secciones con color uniforme de corona | Todas iguales (color de estado) |
| Puente | Color uniforme de puente en todas las secciones | Todas iguales |
| Implante | Color uniforme de implante en todas las secciones | Todas iguales |
| Endodoncia | Color uniforme de endodoncia en todas las secciones | Todas iguales |

---

## 5. Interacción y Comportamiento

### 5.1 Click

Clickear **cualquier sección** (lingual, oclusal o vestibular) abre el modal completo:
- Llama a `abrirModalDiente(num)` (sin parámetro de cara)
- El modal muestra los 5 selects (cara_vestibular, cara_lingual, cara_mesial, cara_distal, cara_oclusal)
- Usuario elige qué cara editar

### 5.2 Hover

El contenedor `.odon-bucal-diente` mantiene el comportamiento actual:
- `transform: scale(1.14)`
- `z-index: 10`
- Todos los SVGs dentro se oscurecen con `filter: brightness(.88)`

### 5.3 Tooltip

Mantener tooltip en `.odon-bucal-diente` con el resumen:
- Diente presente: `"Diente 16 — Sano / Caries en vestibular..."`
- Diente ausente: `"Diente 16 — Ausente"`

---

## 6. Cambios de Código

### 6.1 Nuevos archivos

- **`resources/views/components/_diente-cara.blade.php`** — Sub-componente reutilizable para renderizar una sección (cara) de un diente

### 6.2 Archivos modificados

- **`resources/views/components/odontograma-bucal.blade.php`**:
  - Mover `$svgPaths`, `$getTipo` a `_diente-cara.blade.php` (reutilizable)
  - Mantener helper `$tdato` para la leyenda (información general del diente)
  - Actualizar loop de dientes para renderizar múltiples `<x-diente-cara>` por diente
    - Si el diente tiene oclusal (molares/premolares según `DIENTES_CON_OCLUSAL`): renderizar 3 secciones
    - Si el diente no tiene oclusal (caninos/incisivos): renderizar 2 secciones
  - Simplificar inline layout (la lógica de coloreo por cara se mueve al componente)

### 6.3 Lógica compartida

- Mantener `Dentadura::ESTADOS_PIEZA`, `Dentadura::ESTADOS_CARA`, `Dentadura::DIENTES_CON_OCLUSAL` en el modelo
- Reutilizar colores de `Dentadura::ESTADOS_PIEZA[$key]['color']` y `Dentadura::ESTADOS_CARA[$key]['color']`

### 6.4 Sin cambios

- ✅ Modal de edición (`modal-diente` en `clientes/show.blade.php`)
- ✅ JS de guardado (`guardarDiente()`)
- ✅ Rutas API y controladores
- ✅ Leyenda visual

---

## 7. Estilos CSS

### 7.1 Contenedor del diente

```css
.odon-bucal-diente {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1px;  /* Pequeño gap entre secciones */
    cursor: pointer;
    transition: transform .12s;
    position: relative;
    padding: 1px 0;
}
```

### 7.2 SVG por cara

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

/* Proporciones de altura */
.odon-cara-lingual,
.odon-cara-vestibular {
    height: calc(76px * 0.4);  /* 30.4px */
}

.odon-cara-oclusal {
    height: calc(76px * 0.2);  /* 15.2px */
}

/* Para dientes sin oclusal (caninos/incisivos) */
.odon-diente-sin-oclusal .odon-cara-lingual,
.odon-diente-sin-oclusal .odon-cara-vestibular {
    height: 38px;  /* 50% de 76px */
}
```

### 7.3 Arcada inferior — Volteo vertical

La arcada inferior se voltea con `transform: scaleY(-1)` en el contenedor `.odon-bucal-row` para que las raíces apunten hacia arriba:

```css
.odon-bucal-row:last-of-type {
    transform: scaleY(-1);
}
```

Esto volte todas las secciones (lingual, oclusal, vestibular) correctamente.

### 7.4 Hover en SVG

```css
.odon-bucal-diente:hover .odon-svg path[fill]:not([fill="none"]) {
    filter: brightness(.88);
}
```

---

## 8. Ejemplo de Renderización

### Arcada superior — Diente 16 (molar)

```html
<div class="odon-bucal-diente" title="Diente 16 — Sano">
  <span class="odon-num">16</span>
  
  <!-- Lingual (40%) -->
  <svg class="odon-svg odon-cara-lingual" viewBox="0 0 32 72" ...>
    <path d="..." fill="#f7f0e2" stroke="#374151" />  <!-- color de cara_lingual -->
  </svg>
  
  <!-- Oclusal (20%) -->
  <svg class="odon-svg odon-cara-oclusal" viewBox="0 0 32 72" ...>
    <path d="..." fill="#f7f0e2" stroke="#374151" />  <!-- color de cara_oclusal -->
  </svg>
  
  <!-- Vestibular (40%) -->
  <svg class="odon-svg odon-cara-vestibular" viewBox="0 0 32 72" ...>
    <path d="..." fill="#ef4444" stroke="#374151" />  <!-- color de cara_vestibular (caries) -->
  </svg>
</div>
```

---

## 9. Testing & Validación

### 9.1 Casos a verificar en navegador

- [ ] Molares/premolares muestran 3 secciones
- [ ] Caninos/incisivos muestran 2 secciones
- [ ] Cada sección colorea correctamente según su cara
- [ ] Click en cualquier sección abre modal completo
- [ ] Hover escala y oscurece todas las secciones
- [ ] Tooltip muestra resumen completo
- [ ] Leyenda se vuelve a renderizar correctamente
- [ ] Arcada inferior se visualiza correctamente (sin volteo visual confuso)
- [ ] Dientes ausentes muestran contorno punteado + X en todas las secciones
- [ ] Edición de cara individual sigue funcionando

### 9.2 Responsive

- [ ] En mobile (< 640px), el odontograma sigue siendo legible
- [ ] El SVG se escala correctamente sin distorsión

---

## 10. Implementación

Ver **plan de implementación** en `superpowers:writing-plans`.

---

## Notas

- Los paths SVG actuales (`$svgPaths`) son reutilizables y no necesitan cambios
- La lógica de coloreo se centraliza en el componente `_diente-cara`
- El componente es agnóstico del contexto (arcada superior/inferior) — se maneja con CSS
- No hay cambios en la BD ni en la lógica de guardado
