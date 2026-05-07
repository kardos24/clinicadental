{{--
    Partial reutilizable: SVG interior de un diente — vista oclusal (desde arriba).
    Variables esperadas: $t (array de $toothData), $num (FDI number int)
--}}
@php
    $shapes = [
        'molar'    => 'M 20,4 C 25,3 32,4 35,7 C 38,10 37,17 36,20 C 37,23 38,30 35,33 C 32,36 25,37 20,36 C 15,37 8,36 5,33 C 2,30 3,23 4,20 C 3,17 2,10 5,7 C 8,4 15,3 20,4 Z',
        'premolar' => 'M 20,5 C 27,5 34,11 34,20 C 34,29 27,35 20,35 C 13,35 6,29 6,20 C 6,11 13,5 20,5 Z',
        'canino'   => 'M 20,4 C 25,4 36,13 36,20 C 36,27 25,36 20,36 C 15,36 4,27 4,20 C 4,13 15,4 20,4 Z',
        'incisivo' => 'M 13,4 L 27,4 C 31,4 34,7 34,11 L 34,29 C 34,33 31,36 27,36 L 13,36 C 9,36 6,33 6,29 L 6,11 C 6,7 9,4 13,4 Z',
    ];
    $shape = $shapes[$t['tipo']] ?? $shapes['molar'];

    $grooveMolar = $t['esPresente'] && $t['tipo'] === 'molar';
    $groovePre   = $t['esPresente'] && $t['tipo'] === 'premolar';
    $grooveCan   = $t['esPresente'] && $t['tipo'] === 'canino';

    $esGestor  = auth()->user()->isGestor();
    $tieneOcl  = $t['tieneOcl'];

    /*
     * Para dientes sin cara oclusal (incisivos y caninos) usamos cuatro triángulos
     * que convergen en el centro (20,20) — el centro queda cubierto sin sección propia.
     * Para dientes con oclusal usamos los trapezoides clásicos + rect central.
     */
    if ($tieneOcl) {
        $polyV   = '0,0 40,0 30,10 10,10';
        $polyIzq = '0,0 10,10 10,30 0,40';
        $polyDer = '30,10 40,0 40,40 30,30';
        $polyL   = '10,30 30,30 40,40 0,40';
    } else {
        $polyV   = '0,0 40,0 20,20';
        $polyIzq = '0,0 0,40 20,20';
        $polyDer = '40,0 40,40 20,20';
        $polyL   = '0,40 40,40 20,20';
    }
@endphp

<svg class="diente-svg" viewBox="0 0 40 40" width="46" height="46">

    <defs>
        <clipPath id="tc-{{ $num }}">
            <path d="{{ $shape }}"/>
        </clipPath>
    </defs>

    <g clip-path="url(#tc-{{ $num }})">
        @if($esGestor)
            <polygon class="cara-svg" points="{{ $polyV }}"   fill="{{ $t['cV'] }}"
                     onclick="abrirModalCaraDiente({{ $num }},'vestibular')"/>
            <polygon class="cara-svg" points="{{ $polyIzq }}" fill="{{ $t['cIzq'] }}"
                     onclick="abrirModalCaraDiente({{ $num }},'{{ $t['caraIzq'] }}')"/>
            <polygon class="cara-svg" points="{{ $polyDer }}" fill="{{ $t['cDer'] }}"
                     onclick="abrirModalCaraDiente({{ $num }},'{{ $t['caraDer'] }}')"/>
            <polygon class="cara-svg" points="{{ $polyL }}"   fill="{{ $t['cL'] }}"
                     onclick="abrirModalCaraDiente({{ $num }},'lingual')"/>
            @if($tieneOcl)
                <rect class="cara-svg" x="10" y="10" width="20" height="20" fill="{{ $t['cO'] }}"
                      onclick="abrirModalCaraDiente({{ $num }},'oclusal')"/>
            @endif
        @else
            <polygon points="{{ $polyV }}"   fill="{{ $t['cV'] }}"   stroke="#94a3b8" stroke-width=".6"/>
            <polygon points="{{ $polyIzq }}" fill="{{ $t['cIzq'] }}" stroke="#94a3b8" stroke-width=".6"/>
            <polygon points="{{ $polyDer }}" fill="{{ $t['cDer'] }}" stroke="#94a3b8" stroke-width=".6"/>
            <polygon points="{{ $polyL }}"   fill="{{ $t['cL'] }}"   stroke="#94a3b8" stroke-width=".6"/>
            @if($tieneOcl)
                <rect x="10" y="10" width="20" height="20" fill="{{ $t['cO'] }}" stroke="#94a3b8" stroke-width=".6"/>
            @endif
        @endif
    </g>

    {{-- Surcos decorativos --}}
    @if($grooveMolar)
        <line x1="20" y1="9"  x2="20" y2="31" stroke="#64748b" stroke-width=".8" pointer-events="none"/>
        <line x1="9"  y1="20" x2="31" y2="20" stroke="#64748b" stroke-width=".8" pointer-events="none"/>
        <circle cx="20" cy="20" r="1.2" fill="#64748b" fill-opacity=".5" pointer-events="none"/>
    @elseif($groovePre)
        <line x1="20" y1="9" x2="20" y2="31" stroke="#64748b" stroke-width=".8" pointer-events="none"/>
        <circle cx="14" cy="20" r=".9" fill="#64748b" fill-opacity=".45" pointer-events="none"/>
        <circle cx="26" cy="20" r=".9" fill="#64748b" fill-opacity=".45" pointer-events="none"/>
    @elseif($grooveCan)
        <line x1="20" y1="7" x2="20" y2="33" stroke="#64748b" stroke-width=".7" stroke-dasharray="1,2" pointer-events="none"/>
    @endif

    <path d="{{ $shape }}" fill="none" stroke="#334155" stroke-width="1.4" class="borde-diente"/>

</svg>
