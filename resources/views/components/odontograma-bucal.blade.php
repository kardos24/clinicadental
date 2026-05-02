{{--
    Odontograma bucal — vista frontal anatómica (corona + raíz) por diente.
    Props:
      $cliente      — modelo Cliente
      $dentadura    — colección keyed [num_diente => Dentadura] (Dentadura::mapaCliente)
      $modoEdicion  — bool (default true). Si false, sin onclick (vista paciente)
--}}
@props(['cliente', 'dentadura', 'modoEdicion' => true])

@php
use App\Models\Dentadura;

/* ── Helper: datos visuales de un diente ────────────────────────────── */
$tdato = function(int $num) use ($dentadura) {
    $d     = $dentadura[(string)$num] ?? null;
    $pieza = $d?->estado_pieza ?? 'presente';
    $esP   = ($pieza === 'presente' || $pieza === null);

    if ($esP) {
        $ausente    = false;
        $icono      = '';
        $tooltip    = 'Diente '.$num . ($d ? ' — '.$d->resumen() : '');
    } else {
        $meta       = Dentadura::ESTADOS_PIEZA[$pieza] ?? [];
        $ausente    = $pieza === 'ausente';
        $icono      = $meta['icono'] ?? '';
        $tooltip    = 'Diente '.$num.' — '.($meta['label'] ?? $pieza);
    }

    return compact('ausente','icono','tooltip','esP','pieza');
};

$superiores = Dentadura::DIENTES_SUPERIORES;
$inferiores = Dentadura::DIENTES_INFERIORES;

/* Mapeo num → tipo */
$getTipo = fn(int $n) => match(true) {
    in_array($n, [11,12,21,22,31,32,41,42]) => 'incisivo',
    in_array($n, [13,23,33,43])             => 'canino',
    in_array($n, [14,15,24,25,34,35,44,45]) => 'premolar',
    default                                 => 'molar',
};

/* Determinar si un diente tiene cara oclusal */
$tieneOclusal = fn(int $n) => in_array($n, Dentadura::DIENTES_CON_OCLUSAL);
@endphp

<div class="odon-bucal-wrap">

    {{-- ── Leyenda ──────────────────────────────────────────────────────── --}}
    <div class="odon-bucal-leyenda">
        <div class="leyenda-grupo">
            <div class="leyenda-titulo">Estado de pieza</div>
            <div class="leyenda-items">
                @foreach(Dentadura::ESTADOS_PIEZA as $key => $est)
                <div class="leyenda-item">
                    <div class="leyenda-color" style="background:{{ $est['color'] }};"></div>
                    <span>{{ $est['icono'] }} {{ $est['label'] }}</span>
                </div>
                @endforeach
                <div class="leyenda-item">
                    <div class="leyenda-color" style="background:#f7f0e2;border:1px solid #d1c7b0;"></div>
                    <span>Sano</span>
                </div>
            </div>
        </div>
        <div class="leyenda-grupo">
            <div class="leyenda-titulo">Cara vestibular</div>
            <div class="leyenda-items">
                @foreach(Dentadura::ESTADOS_CARA as $key => $est)
                    @if($key !== 'sano')
                    <div class="leyenda-item">
                        <div class="leyenda-color" style="background:{{ $est['color'] }};"></div>
                        <span>{{ $est['label'] }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Arcada superior ─────────────────────────────────────────────── --}}
    <p class="odon-bucal-label">◀ Arcada superior ▶</p>
    <div class="odon-bucal-row">
        @foreach($superiores as $i => $num)
            @if($i === 8)<div class="odon-bucal-sep"></div>@endif
            @php
                $tipo  = $getTipo($num);
                $td    = $tdato($num);
                $tieneOcl = $tieneOclusal($num);
            @endphp
            <div class="odon-bucal-diente{{ $td['ausente'] ? ' odon-ausente' : '' }}{{ !$tieneOcl ? ' odon-diente-sin-oclusal' : '' }}"
                 title="{{ $td['tooltip'] }}"
                 @if($modoEdicion && auth()->user()->isGestor())
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
    </div>

    {{-- Línea divisoria arcos --}}
    <div class="odon-bucal-divider"></div>

    {{-- ── Arcada inferior ─────────────────────────────────────────────── --}}
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
                 @if($modoEdicion && auth()->user()->isGestor())
                     onclick="abrirModalDiente({{ $num }})"
                 @endif>

                {{-- Cara lingual --}}
                <x-diente-cara :num="$num" cara="lingual" :dentadura="$dentadura" />

                {{-- Cara oclusal (solo si el diente la tiene) --}}
                @if($tieneOcl)
                <x-diente-cara :num="$num" cara="oclusal" :dentadura="$dentadura" />
                @endif

                {{-- Cara vestibular --}}
                <x-diente-cara :num="$num" cara="vestibular" :dentadura="$dentadura" />

                {{-- Número abajo --}}
                <span class="odon-num">{{ $num }}</span>
            </div>
        @endforeach
    </div>
    <p class="odon-bucal-label">◀ Arcada inferior ▶</p>

    @if($modoEdicion && auth()->user()->isGestor())
    <p style="text-align:center;font-size:.78rem;color:var(--texto-med);margin-top:.5rem;">
        Haz clic en cualquier diente para editar su estado.
    </p>
    @endif
</div>

@push('styles')
<style>
/* ── Odontograma bucal ─────────────────────────────────────────────── */
.odon-bucal-wrap {
    background: #fff;
}
.odon-bucal-leyenda {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
    margin-bottom: .75rem;
    padding-bottom: .75rem;
    border-bottom: 1px solid var(--gris-borde, #e2e8f0);
}
.odon-bucal-label {
    text-align: center;
    font-size: .72rem;
    color: var(--texto-med, #64748b);
    margin: .4rem 0 2px;
}
.odon-bucal-row {
    display: flex;
    justify-content: center;
    align-items: flex-end;   /* coronas se tocan en la línea media */
    gap: 2px;
    flex-wrap: nowrap;
}
.odon-bucal-sep {
    width: 10px;
    flex-shrink: 0;
}
.odon-bucal-divider {
    border-top: 2px dashed var(--gris-borde, #e2e8f0);
    margin: 4px auto;
    width: 92%;
}
/* Arcada inferior: alinear hacia arriba (coronas al centro) */
.odon-bucal-row:last-of-type,
.odon-bucal-row + .odon-bucal-row {
    align-items: flex-start;
}
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
.odon-bucal-diente:hover {
    transform: scale(1.14);
    z-index: 10;
}
.odon-ausente {
    opacity: .4;
}
.odon-diente-sin-oclusal {
    /* Dientes que no tienen cara oclusal (incisivos, caninos) */
}
.odon-num {
    font-size: .6rem;
    font-weight: 700;
    color: #475569;
    line-height: 1.2;
    user-select: none;
}
.odon-svg {
    display: block;
    overflow: visible;
    filter: drop-shadow(0 1px 1px rgba(0,0,0,.12));
}
/* Arcada inferior: voltear verticalmente para que las raíces apunten arriba */
.odon-svg-flip {
    transform: scaleY(-1);
}
.odon-bucal-diente:hover .odon-svg path[fill]:not([fill="none"]) {
    filter: brightness(.88);
}
</style>
@endpush
