@props(['dentadura', 'modoEdicion' => true])

@php
use App\Models\Dentadura;

$tdato = function(int $num) use ($dentadura) {
    $d     = $dentadura[(string)$num] ?? null;
    $pieza = $d?->estado_pieza ?? 'presente';
    $esP   = ($pieza === 'presente' || $pieza === null);

    if ($esP) {
        $ausente = false;
        $tooltip = 'Diente '.$num . ($d ? ' — '.$d->resumen() : '');
    } else {
        $meta    = Dentadura::ESTADOS_PIEZA[$pieza] ?? [];
        $ausente = $pieza === 'ausente';
        $tooltip = 'Diente '.$num.' — '.($meta['label'] ?? $pieza);
    }

    return compact('ausente','tooltip');
};

$superiores = Dentadura::DIENTES_SUPERIORES;
$inferiores = Dentadura::DIENTES_INFERIORES;
@endphp

<div class="card odontograma" id="odontograma-bucal-card">
    <div class="card-header" style="flex-direction:column;align-items:flex-start;">
        <h3 class="card-title" style="margin-bottom:.25rem;">🦷 Odontograma anatómico</h3>
        <p style="font-size:.75rem;color:var(--text-light);margin:0;">
            Vista bucal (frontal) — corona coloreada según estado de la cara vestibular.
        </p>
    </div>

    <div class="odon-bucal-wrap">

        {{-- Leyenda --}}
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
                        <div class="leyenda-color" style="background:#f5eedd;border:1px solid #c8b89a;"></div>
                        <span>Sano</span>
                    </div>
                </div>
            </div>
            <div class="leyenda-grupo">
                <div class="leyenda-titulo">Cara vestibular / lingual</div>
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

        {{-- Etiqueta arcada superior --}}
        <p class="odon-bucal-label">◀ Arcada superior ▶</p>

        <div class="odon-bucal-scroll">
          <div class="odon-bucal-inner">

            {{-- Arcada superior --}}
            <div class="odon-bucal-row odon-row-superior">
                @foreach($superiores as $i => $num)
                    @if($i === 8)<div class="odon-bucal-sep"></div>@endif
                    @php $td = $tdato($num); @endphp
                    <div class="odon-bucal-diente{{ $td['ausente'] ? ' odon-ausente' : '' }}"
                         title="{{ $td['tooltip'] }}"
                         @if($modoEdicion && auth()->user()->isGestor())
                             onclick="abrirModalDiente({{ $num }})"
                         @endif>
                        <span class="odon-num">{{ $num }}</span>
                        <x-cliente.odontograma.anatomico.diente :num="$num" :dentadura="$dentadura" arcada="superior" />
                    </div>
                @endforeach
            </div>

            {{-- Línea oclusal central --}}
            <div class="odon-bucal-divider"></div>

            {{-- Arcada inferior --}}
            <div class="odon-bucal-row odon-row-inferior">
                @foreach($inferiores as $i => $num)
                    @if($i === 8)<div class="odon-bucal-sep"></div>@endif
                    @php $td = $tdato($num); @endphp
                    <div class="odon-bucal-diente{{ $td['ausente'] ? ' odon-ausente' : '' }}"
                         title="{{ $td['tooltip'] }}"
                         @if($modoEdicion && auth()->user()->isGestor())
                             onclick="abrirModalDiente({{ $num }})"
                         @endif>
                        <x-cliente.odontograma.anatomico.diente :num="$num" :dentadura="$dentadura" arcada="inferior" />
                        <span class="odon-num">{{ $num }}</span>
                    </div>
                @endforeach
            </div>

          </div>{{-- /.odon-bucal-inner --}}
        </div>{{-- /.odon-bucal-scroll --}}

        <p class="odon-bucal-label">◀ Arcada inferior ▶</p>

        @if($modoEdicion && auth()->user()->isGestor())
        <p class="odon-bucal-hint">Haz clic en cualquier diente para editar su estado.</p>
        @endif
    </div>
</div>

@push('styles')
<style>
/* ── Odontograma bucal ─────────────────────────────────────────────────────── */
.odon-bucal-wrap {
    background: #fff;
}

.odon-bucal-leyenda {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
    margin-bottom: .75rem;
    padding-bottom: .75rem;
    border-bottom: 1px solid var(--gray-200);
}

.odon-bucal-label {
    text-align: center;
    font-size: .72rem;
    color: var(--text-light);
    margin: .4rem 0 2px;
}

.odon-bucal-hint {
    text-align: center;
    font-size: .78rem;
    color: var(--text-light);
    margin-top: .5rem;
}

.odon-bucal-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: .25rem;
}

.odon-bucal-inner {
    min-width: 610px;
    padding: 0 4px;
}

.odon-bucal-row {
    display: flex;
    justify-content: center;
    gap: 2px;
    flex-wrap: nowrap;
}

.odon-row-superior {
    align-items: flex-end;
}

.odon-row-inferior {
    align-items: flex-start;
}

.odon-bucal-sep {
    width: 10px;
    flex-shrink: 0;
    align-self: stretch;
}

.odon-bucal-divider {
    border-top: 2px dashed var(--gray-200);
    margin: 0 auto;
    width: 94%;
}

.odon-bucal-diente {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1px;
    cursor: pointer;
    transition: transform .12s;
    position: relative;
    padding: 0;
}

.odon-bucal-diente:hover {
    transform: scale(1.14);
    z-index: 10;
}

.odon-ausente {
    opacity: .38;
}

.odon-num {
    font-size: .58rem;
    font-weight: 700;
    color: #475569;
    line-height: 1.3;
    user-select: none;
    white-space: nowrap;
}

.odon-svg {
    display: block;
    overflow: visible;
    filter: drop-shadow(0 1px 2px rgba(0,0,0,.13));
    flex-shrink: 0;
}

.odon-diente-inf {
    transform: scaleY(-1);
    display: block;
}

@media (max-width: 640px) {
    .odon-svg.odon-diente-bucal {
        width: 26px;
        height: 77px;
    }
    .odon-bucal-sep {
        width: 6px;
    }
    .odon-num {
        font-size: .52rem;
    }
    .odon-bucal-leyenda {
        gap: 1rem;
    }
}

@media (max-width: 420px) {
    .odon-svg.odon-diente-bucal {
        width: 22px;
        height: 65px;
    }
}
</style>
@endpush
