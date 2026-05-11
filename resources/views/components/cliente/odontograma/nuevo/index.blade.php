@props(['cliente', 'dentadura'])

@php
    $dentaduraJson = json_encode($dentadura->mapWithKeys(function ($d, $key) {
        return [$key => [
            'estado_pieza'    => $d->estado_pieza    ?? 'presente',
            'cara_vestibular' => $d->cara_vestibular ?? 'sano',
            'cara_lingual'    => $d->cara_lingual    ?? 'sano',
            'cara_mesial'     => $d->cara_mesial     ?? 'sano',
            'cara_distal'     => $d->cara_distal     ?? 'sano',
            'cara_oclusal'    => $d->cara_oclusal    ?? 'sano',
            'tiene_oclusal'   => in_array((int)$d->num_diente, \App\Models\Dentadura::DIENTES_CON_OCLUSAL),
        ]];
    }));
@endphp

<div class="odon-nuevo-wrap">
    <div class="odon-nuevo-body">
        <div class="odon-nuevo-board" id="odon-nuevo-board"></div>

        @if(auth()->user()->isGestor())
        <div class="odon-nuevo-panel" id="odon-nuevo-panel">
            <div class="odon-psec">
                <div class="odon-phdr">Diente seleccionado</div>
                <div id="odon-sel-info" style="color:#6b4020;font-size:.78rem;opacity:.6">
                    — Haz clic en un diente —
                </div>
                <div class="odon-face-chips" id="odon-fchips"></div>
                <div class="odon-mode-row" id="odon-mrow" style="display:none">
                    <div class="odon-mbtn odon-mbtn-on" id="odon-btn-cara"
                         onclick="odonSetMode('cara')">Por cara</div>
                    <div class="odon-mbtn" id="odon-btn-pieza"
                         onclick="odonSetMode('pieza')">Pieza entera</div>
                </div>
            </div>
            <div class="odon-psec">
                <div class="odon-phdr">Estado</div>
                <div class="odon-states" id="odon-states">
                    <span style="opacity:.5;font-size:.78rem">Selecciona un diente primero</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="odon-nuevo-legend" id="odon-nuevo-legend"></div>
</div>

@push('scripts')
<script>
window.odontogramaNuevoUrl    = '{{ route('clientes.dentadura', $cliente) }}';
window.odontogramaNuevoData   = {!! $dentaduraJson !!};
window.odontogramaNuevoGestor = {{ auth()->user()->isGestor() ? 'true' : 'false' }};
</script>
<script src="{{ asset('js/odontograma-nuevo.js') }}"></script>
@endpush
