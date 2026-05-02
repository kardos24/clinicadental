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

/* ── Colores ────────────────────────────────────────────────────── */
$colorMarfil  = '#f7f0e2';   // diente sano
$strokeDiente = '#374151';   // contorno diente
$strokeRaiz   = '#9ca3af';   // contorno raíz

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
@endphp

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
