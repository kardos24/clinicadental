@push('styles')
<style>
/* ── ODONTOGRAMA MULTI-CARA ────────────────────────────────────────────── */
.odontograma { background:#fff; border-radius:12px; padding:1.25rem 1.5rem; border:1px solid var(--gray-200); }
.dientes-row { display:flex; justify-content:center; gap:3px; margin:6px 0; flex-wrap:nowrap; }
.diente-wrap {
    display:flex; flex-direction:column; align-items:center; gap:1px;
    cursor:default; transition:transform .12s; position:relative;
}
.diente-wrap:hover { transform:scale(1.18); z-index:10; }
.diente-wrap.ausente { opacity:.35; }
.diente-num { font-size:.65rem; color:#475569; font-weight:700; line-height:1.3; user-select:none; }
.diente-svg { display:block; overflow:visible; border-radius:3px; }
/* Caras individuales clicables */
.diente-svg .cara-svg {
    stroke:#94a3b8; stroke-width:.7; cursor:pointer;
    transition:filter .1s, stroke .1s;
}
.diente-svg .cara-svg:hover {
    filter:brightness(.72) saturate(1.3);
    stroke:#1e3a8a; stroke-width:1.2;
}
/* Cara no-oclusal (incisivos/caninos): decorativa, no clicable */
.diente-svg .cara-nooclusal { stroke:#cbd5e1; stroke-width:.5; stroke-dasharray:2,2; pointer-events:none; }
/* Cara resaltada al abrir modal */
.cara-field.cara-highlight > select {
    outline:2px solid #2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.2);
}
.diente-pieza-icon {
    position:absolute; top:50%; left:50%; transform:translate(-50%,-52%);
    font-size:.75rem; font-weight:800; color:#fff; text-shadow:0 1px 3px rgba(0,0,0,.65);
    pointer-events:none; line-height:1;
}
.diente-separador { width:12px; flex-shrink:0; }
/* Leyendas */
.leyenda-grupo { margin-bottom:.4rem; }
.leyenda-titulo { font-size:.66rem; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:.5px; margin-bottom:.2rem; }
.leyenda-items { display:flex; gap:.5rem; flex-wrap:wrap; }
.leyenda-item { display:flex; align-items:center; gap:.3rem; font-size:.72rem; color:#475569; }
.leyenda-color { width:12px; height:12px; border-radius:2px; flex-shrink:0; border:1px solid #cbd5e1; }
/* Modal diente multi-cara */
.pieza-estado-section { border-bottom:1px solid var(--gray-200); padding-bottom:.75rem; margin-bottom:.75rem; }
.cara-grid { display:grid; grid-template-columns:1fr 1fr; gap:.6rem; margin-top:.5rem; }
.cara-field label { font-size:.78rem; font-weight:600; color:var(--text-light); display:block; margin-bottom:.15rem; }
.cara-field select { width:100%; }
.cara-field.full-width { grid-column:1/-1; }
.caras-disabled { opacity:.35; pointer-events:none; }
#modal-diente select.form-control { width:100%; }
</style>
@endpush

{{-- ODONTOGRAMA MULTI-CARA ────────────────────────────────────────────── --}}
@php
    // Closure para calcular los colores de cada cara del diente
    $sanoColor = '#e8ecf1';
    $toothData = function($num) use ($dentadura, $sanoColor) {
        $d = $dentadura["$num"] ?? null;
        $pieza = $d?->estado_pieza ?? 'presente';
        $esPresente = ($pieza === 'presente');
        $tieneOcl = in_array($num, \App\Models\Dentadura::DIENTES_CON_OCLUSAL);
        $cuadrante = (int) floor($num / 10);
        $mesialDer = in_array($cuadrante, [1, 4]);

        $tipo = match(true) {
            in_array($num, [11,12,21,22,31,32,41,42]) => 'incisivo',
            in_array($num, [13,23,33,43])             => 'canino',
            in_array($num, [14,15,24,25,34,35,44,45]) => 'premolar',
            default                                   => 'molar',
        };

        if ($esPresente) {
            $gc = function($cara) use ($d, $sanoColor) {
                $val = $d ? $d->{"cara_{$cara}"} : null;
                if (!$val) return $sanoColor;
                return \App\Models\Dentadura::ESTADOS_CARA[$val]['color'] ?? $sanoColor;
            };
            $cV = $gc('vestibular'); $cL = $gc('lingual');
            $cM = $gc('mesial');     $cD = $gc('distal');
            $cO = $tieneOcl ? $gc('oclusal') : '#f1f5f9';
        } else {
            $pColor = \App\Models\Dentadura::ESTADOS_PIEZA[$pieza]['color'] ?? '#6b7280';
            $cV = $cL = $cM = $cD = $cO = $pColor;
        }

        return [
            'pieza' => $pieza, 'esPresente' => $esPresente, 'tieneOcl' => $tieneOcl,
            'icono' => !$esPresente ? (\App\Models\Dentadura::ESTADOS_PIEZA[$pieza]['icono'] ?? '') : '',
            'titulo' => "Diente {$num}" . ($d ? ' — ' . $d->resumen() : ''),
            'cV'     => $cV, 'cL' => $cL, 'cO' => $cO,
            'cIzq'   => $mesialDer ? $cD : $cM,
            'cDer'   => $mesialDer ? $cM : $cD,
            // Nombres de cara para el onclick individual
            'caraIzq' => $mesialDer ? 'distal' : 'mesial',
            'caraDer' => $mesialDer ? 'mesial' : 'distal',
            'tipo'    => $tipo,
        ];
    };
@endphp

<div class="card odontograma">
    <div class="card-header" style="flex-direction:column;align-items:flex-start;">
        <h3 class="card-title" style="margin-bottom:.75rem;">🦷 Odontograma</h3>
        <div style="display:flex;gap:2rem;flex-wrap:wrap;">
            <div class="leyenda-grupo">
                <div class="leyenda-titulo">Estado de pieza</div>
                <div class="leyenda-items">
                    @foreach(App\Models\Dentadura::ESTADOS_PIEZA as $key => $est)
                    <div class="leyenda-item">
                        <div class="leyenda-color" style="background:{{ $est['color'] }};"></div>
                        <span>{{ $est['label'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="leyenda-grupo">
                <div class="leyenda-titulo">Estado por cara</div>
                <div class="leyenda-items">
                    <div class="leyenda-item">
                        <div class="leyenda-color" style="background:{{ $sanoColor }};"></div>
                        <span>Sano</span>
                    </div>
                    @foreach(App\Models\Dentadura::ESTADOS_CARA as $key => $est)
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
    </div>

    {{-- Arcada superior --}}
    <p style="text-align:center;font-size:.72rem;color:var(--text-light);margin:.75rem 0 2px;">◀ Arcada superior ▶</p>
    <div class="dientes-row">
        @foreach(App\Models\Dentadura::DIENTES_SUPERIORES as $i => $num)
            @if($i === 8)<div class="diente-separador"></div>@endif
            @php $t = $toothData($num); @endphp
            <div class="diente-wrap {{ $t['pieza'] === 'ausente' ? 'ausente' : '' }}"
                 title="{{ $t['titulo'] }}">
                <span class="diente-num">{{ $num }}</span>
                @include('components._diente-inner', ['t' => $t, 'num' => $num])
                @if($t['icono'])<span class="diente-pieza-icon">{{ $t['icono'] }}</span>@endif
            </div>
        @endforeach
    </div>

    <div style="border-top:2px dashed var(--gray-200);margin:6px auto;width:90%;"></div>

    {{-- Arcada inferior --}}
    <div class="dientes-row">
        @foreach(App\Models\Dentadura::DIENTES_INFERIORES as $i => $num)
            @if($i === 8)<div class="diente-separador"></div>@endif
            @php $t = $toothData($num); @endphp
            <div class="diente-wrap {{ $t['pieza'] === 'ausente' ? 'ausente' : '' }}"
                 title="{{ $t['titulo'] }}">
                @include('components._diente-inner', ['t' => $t, 'num' => $num])
                @if($t['icono'])<span class="diente-pieza-icon">{{ $t['icono'] }}</span>@endif
                <span class="diente-num">{{ $num }}</span>
            </div>
        @endforeach
    </div>
    <p style="text-align:center;font-size:.72rem;color:var(--text-light);margin-top:2px;">◀ Arcada inferior ▶</p>

    @if(auth()->user()->isGestor())
    <p style="text-align:center;font-size:.78rem;color:var(--text-light);margin-top:.75rem;">
        Haz clic en cualquier diente para editar su estado.
    </p>
    @endif
</div>

{{-- ODONTOGRAMA BUCAL (vista anatómica frontal) ─────────────────────── --}}
<div class="card odontograma" id="odontograma-bucal-card">
    <div class="card-header" style="flex-direction:column;align-items:flex-start;">
        <h3 class="card-title" style="margin-bottom:.25rem;">🦷 Odontograma anatómico</h3>
        <p style="font-size:.75rem;color:var(--text-light);margin:0;">
            Vista bucal (frontal) — corona coloreada según estado de la cara vestibular.
        </p>
    </div>
    <x-odontograma-bucal
        :cliente="$cliente"
        :dentadura="$dentadura"
        :modoEdicion="true"
    />
</div>

@push('scripts')
@php
    $dentaduraJson = json_encode($dentadura->mapWithKeys(function ($d, $key) {
        return [$key => [
            'estado_pieza'    => $d->estado_pieza ?? 'presente',
            'cara_vestibular' => $d->cara_vestibular ?? 'sano',
            'cara_lingual'    => $d->cara_lingual ?? 'sano',
            'cara_mesial'     => $d->cara_mesial ?? 'sano',
            'cara_distal'     => $d->cara_distal ?? 'sano',
            'cara_oclusal'    => $d->cara_oclusal ?? 'sano',
            'notas'           => $d->notas ?? '',
            'tiene_oclusal'   => in_array((int) $d->num_diente, \App\Models\Dentadura::DIENTES_CON_OCLUSAL),
        ]];
    }));
@endphp
<script>
window.dentaduraUrl  = '{{ route('clientes.dentadura', $cliente) }}';
window.dentaduraData = {!! $dentaduraJson !!};
</script>
<script src="{{ asset('js/odontograma.js') }}"></script>
@endpush
