{{--
    Diente anatómico — vista bucal (frontal).
    Raíces arriba + corona dividida: lingual (interior) arriba, vestibular (exterior) abajo.
    La arcada inferior aplica scaleY(-1) en CSS para invertir la orientación.
    Props:
      $num       — FDI número (ej: 16)
      $dentadura — colección keyed [num => Dentadura]
      $arcada    — 'superior' | 'inferior'
--}}
@props(['num' => null, 'dentadura' => null, 'arcada' => 'inferior'])

@php
use App\Models\Dentadura;

$num  = (int)$num;
$tipo = match(true) {
    in_array($num, [11,12,21,22,31,32,41,42]) => 'incisivo',
    in_array($num, [13,23,33,43])             => 'canino',
    in_array($num, [14,15,24,25,34,35,44,45]) => 'premolar',
    default                                   => 'molar',
};

$d           = $dentadura[(string)$num] ?? null;
$estadoPieza = $d?->estado_pieza ?? 'presente';
$esPresente  = ($estadoPieza === 'presente' || $estadoPieza === null);
$ausente     = ($estadoPieza === 'ausente');

$ivory    = '#f5eedd';
$raizFill = '#e2d4b6';

if ($esPresente) {
    $lingEst  = $d?->cara_lingual    ?? null;
    $vestEst  = $d?->cara_vestibular ?? null;
    $fillLing = isset(Dentadura::ESTADOS_CARA[$lingEst])
        ? Dentadura::ESTADOS_CARA[$lingEst]['color'] : $ivory;
    $fillVest = isset(Dentadura::ESTADOS_CARA[$vestEst])
        ? Dentadura::ESTADOS_CARA[$vestEst]['color'] : $ivory;
    $fillRaiz = $raizFill;
} else {
    $meta     = Dentadura::ESTADOS_PIEZA[$estadoPieza] ?? [];
    $c        = $meta['color'] ?? '#6b7280';
    $fillLing = $fillVest = $c;
    $fillRaiz = $ausente ? 'none' : $c;
}

$sh = [
    'incisivo' => [
        'raices' => [
            'M 17,2 C 15,2 13,7 13,20 L 13,36 L 21,36 L 21,20 C 21,7 19,2 17,2 Z',
        ],
        'corona' => 'M 11,36 L 23,36 C 24,36 25,39 25,48 L 25,63 C 25,72 23,80 21,86 '
                  . 'C 19,90 18,92 17,93 C 16,92 15,90 13,86 C 11,80 9,72 9,63 '
                  . 'L 9,48 C 9,39 10,36 11,36 Z',
        'split'  => 63,
        'body'   => null,
        'surcos' => [],
    ],
    'canino' => [
        'raices' => [
            'M 17,1 C 15,1 11,7 11,24 L 11,36 L 23,36 L 23,24 C 23,7 19,1 17,1 Z',
        ],
        'corona' => 'M 8,36 C 7,36 6,40 6,55 L 6,68 C 6,78 8,86 12,91 '
                  . 'C 14,94 16,96 17,97 C 18,96 20,94 22,91 '
                  . 'C 26,86 28,78 28,68 L 28,55 C 28,40 27,36 26,36 Z',
        'split'  => 63,
        'body'   => null,
        'surcos' => [
            ['x1'=>17,'y1'=>50,'x2'=>17,'y2'=>90,'sw'=>0.6,'dash'=>true],
        ],
    ],
    'premolar' => [
        'raices' => [
            'M 11,1 C 9,1 7,6 7,22 L 7,36 L 16,36 L 16,22 C 16,6 13,1 11,1 Z',
            'M 23,1 C 21,1 19,6 19,22 L 19,36 L 27,36 L 27,22 C 27,6 25,1 23,1 Z',
        ],
        'corona' => 'M 5,36 L 29,36 C 30,36 31,40 31,50 L 31,64 C 31,74 29,83 27,88 '
                  . 'C 24,92 21,94 17,95 C 13,94 10,92 7,88 '
                  . 'C 5,83 3,74 3,64 L 3,50 C 3,40 4,36 5,36 Z',
        'split'  => 63,
        'body'   => null,
        'surcos' => [
            ['x1'=>17,'y1'=>48,'x2'=>17,'y2'=>88,'sw'=>0.65,'dash'=>false],
        ],
    ],
    'molar' => [
        'raices' => [
            'M 9,0 C 7,0 4,6 4,22 L 4,36 L 15,36 L 15,22 C 15,6 12,0 9,0 Z',
            'M 25,0 C 23,0 20,6 20,22 L 20,36 L 30,36 L 30,22 C 30,6 27,0 25,0 Z',
        ],
        'corona' => 'M 2,36 L 32,36 C 33,36 33,42 33,50 L 33,78 C 33,85 32,89 30,90 '
                  . 'L 4,90 C 2,89 1,85 1,78 L 1,50 C 1,42 1,36 2,36 Z',
        'split'  => 60,
        'body'   => ['y1' => 60, 'y2' => 72],
        'surcos' => [
            ['x1'=>17,'y1'=>48,'x2'=>17,'y2'=>82,'sw'=>0.7,'dash'=>false],
            ['x1'=>6, 'y1'=>66,'x2'=>28,'y2'=>66,'sw'=>0.5,'dash'=>false],
        ],
    ],
];

$s            = $sh[$tipo];
$strokeCorona = '#374151';
$strokeRaiz   = '#a08060';
$isInf        = ($arcada === 'inferior');
@endphp

<svg class="odon-svg odon-diente-bucal{{ $isInf ? ' odon-diente-inf' : '' }}"
     viewBox="0 0 34 100" width="34" height="100"
     xmlns="http://www.w3.org/2000/svg">

    <defs>
        <clipPath id="clip-b{{ $num }}">
            <path d="{{ $s['corona'] }}"/>
        </clipPath>
    </defs>

    {{-- Raíces (siempre en la parte superior del SVG) --}}
    @if(!$ausente)
        @foreach($s['raices'] as $rp)
        <path d="{{ $rp }}"
              fill="{{ $fillRaiz }}"
              stroke="{{ $strokeRaiz }}"
              stroke-width="0.9"
              stroke-linejoin="round"/>
        @endforeach
    @endif

    {{-- Corona --}}
    @if($ausente)
        <path d="{{ $s['corona'] }}" fill="none"
              stroke="#94a3b8" stroke-width="0.8" stroke-dasharray="2,2"/>
        <line x1="11" y1="44" x2="23" y2="82" stroke="#94a3b8" stroke-width="1.1"/>
        <line x1="23" y1="44" x2="11" y2="82" stroke="#94a3b8" stroke-width="1.1"/>
    @else
        {{-- Sección lingual (interior — arriba) --}}
        <rect x="0" y="36" width="34" height="{{ $s['split'] - 36 }}"
              clip-path="url(#clip-b{{ $num }})"
              fill="{{ $fillLing }}"/>

        @if($s['body'])
            <rect x="0" y="{{ $s['body']['y1'] }}" width="34"
                  height="{{ $s['body']['y2'] - $s['body']['y1'] }}"
                  clip-path="url(#clip-b{{ $num }})"
                  fill="{{ $ivory }}"/>

            <rect x="0" y="{{ $s['body']['y2'] }}" width="34" height="30"
                  clip-path="url(#clip-b{{ $num }})"
                  fill="{{ $fillVest }}"/>
        @else
            <rect x="0" y="{{ $s['split'] }}" width="34" height="37"
                  clip-path="url(#clip-b{{ $num }})"
                  fill="{{ $fillVest }}"/>
        @endif

        {{-- Surcos decorativos --}}
        @foreach($s['surcos'] as $sg)
        <line x1="{{ $sg['x1'] }}" y1="{{ $sg['y1'] }}"
              x2="{{ $sg['x2'] }}" y2="{{ $sg['y2'] }}"
              stroke="{{ $strokeCorona }}" stroke-width="{{ $sg['sw'] }}"
              stroke-opacity="0.3"
              pointer-events="none"
              @if($sg['dash']) stroke-dasharray="1.5,2.5" @endif />
        @endforeach

        {{-- Contorno de la corona --}}
        <path d="{{ $s['corona'] }}"
              fill="none"
              stroke="{{ $strokeCorona }}"
              stroke-width="1.1"
              stroke-linejoin="round"/>

        {{-- Línea divisoria lingual / cuerpo o lingual / vestibular --}}
        <line x1="0" y1="{{ $s['split'] }}" x2="34" y2="{{ $s['split'] }}"
              clip-path="url(#clip-b{{ $num }})"
              stroke="{{ $strokeCorona }}"
              stroke-width="0.5"
              stroke-opacity="0.35"
              pointer-events="none"/>

        @if($s['body'])
        <line x1="0" y1="{{ $s['body']['y2'] }}" x2="34" y2="{{ $s['body']['y2'] }}"
              clip-path="url(#clip-b{{ $num }})"
              stroke="{{ $strokeCorona }}"
              stroke-width="0.5"
              stroke-opacity="0.35"
              pointer-events="none"/>
        @endif
    @endif

</svg>
