@props([
    'id' => null,          // Número FDI del diente
    'tipo' => 'molar',     // incisivo, canino, premolar, molar
    'estado' => null,      // Estado del diente
    'color' => '#e2e8f0'   // Color según estado
])

@php
    // Mapeo de nombres de dientes en español
    $nombresDientes = [
        // Incisivos
        11 => 'Incisivo central superior derecho',
        12 => 'Incisivo lateral superior derecho',
        21 => 'Incisivo central superior izquierdo',
        22 => 'Incisivo lateral superior izquierdo',
        31 => 'Incisivo central inferior izquierdo',
        32 => 'Incisivo lateral inferior izquierdo',
        41 => 'Incisivo central inferior derecho',
        42 => 'Incisivo lateral inferior derecho',
        // Caninos
        13 => 'Canino superior derecho',
        23 => 'Canino superior izquierdo',
        33 => 'Canino inferior izquierdo',
        43 => 'Canino inferior derecho',
        // Premolares
        14 => 'Primer premolar superior derecho',
        15 => 'Segundo premolar superior derecho',
        24 => 'Primer premolar superior izquierdo',
        25 => 'Segundo premolar superior izquierdo',
        34 => 'Primer premolar inferior izquierdo',
        35 => 'Segundo premolar inferior izquierdo',
        44 => 'Primer premolar inferior derecho',
        45 => 'Segundo premolar inferior derecho',
        // Molares
        16 => 'Primer molar superior derecho',
        17 => 'Segundo molar superior derecho',
        18 => 'Tercer molar (sapiencia) superior derecho',
        26 => 'Primer molar superior izquierdo',
        27 => 'Segundo molar superior izquierdo',
        28 => 'Tercer molar (sapiencia) superior izquierdo',
        36 => 'Primer molar inferior izquierdo',
        37 => 'Segundo molar inferior izquierdo',
        38 => 'Tercer molar (sapiencia) inferior izquierdo',
        46 => 'Primer molar inferior derecho',
        47 => 'Segundo molar inferior derecho',
        48 => 'Tercer molar (sapiencia) inferior derecho',
    ];

    $nombresTipos = [
        'incisivo' => 'Incisivo',
        'canino' => 'Canino',
        'premolar' => 'Premolar',
        'molar' => 'Molar',
    ];

    $etiquetasEstado = [
        'sano' => 'Diente sano',
        'picado' => 'Con obturación',
        'caries' => 'Con caries',
        'partido' => 'Con corona/puente',
        'caido' => 'Diente extraído',
        'puente' => 'Puente dental',
        'sustituido' => 'Diente sustituido',
    ];

    $estadoTexto = $estado ? ($etiquetasEstado[$estado] ?? 'Sin registrar') : 'Sin registrar';
@endphp

<div class="diente relative group cursor-pointer">
    <div class="tooltip" style="left: 50%; transform: translateX(-50%);">
        <div class="font-medium">Diente {{ $id }}</div>
        <div class="text-gray-300">{{ $nombresTipos[$tipo] }} - {{ $estadoTexto }}</div>
    </div>

    <svg
        class="diente-svg"
        viewBox="0 0 40 40"
        width="36"
        height="36"
        style="background-color: {{ $color }}"
        xmlns="http://www.w3.org/2000/svg"
    >
        <!-- Número del diente -->
        <text
            x="20"
            y="26"
            text-anchor="middle"
            font-size="14"
            font-weight="bold"
            font-family="Arial, sans-serif"
            fill="{{ in_array($estado, ['caido', null]) ? '#6b7280' : '#1f2937' }}"
        >
            {{ $id }}
        </text>

        <!-- Forma del diente según tipo -->
        @if($tipo === 'incisivo')
            <!-- Incisivo: forma cuadrada/simple -->
            <path
                d="M 12 8 L 28 8 L 32 14 L 32 28 L 26 32 L 14 32 L 8 28 L 8 14 Z"
                fill="none"
                stroke="#374151"
                stroke-width="1.5"
                stroke-linejoin="round"
            />
            <!-- Línea cervical -->
            <line x1="10" y1="14" x2="30" y2="14" stroke="#374151" stroke-width="0.5" stroke-opacity="0.5" />

        @elseif($tipo === 'canino')
            <!-- Canino: forma triangular/puntiaguda -->
            <path
                d="M 16 10 L 24 10 L 28 16 L 29 24 L 27 30 L 22 33 L 18 33 L 13 30 L 11 24 L 12 16 Z"
                fill="none"
                stroke="#374151"
                stroke-width="1.5"
                stroke-linejoin="round"
            />
            <!-- Línea cervical -->
            <line x1="13" y1="16" x2="27" y2="16" stroke="#374151" stroke-width="0.5" stroke-opacity="0.5" />

        @elseif($tipo === 'premolar')
            <!-- Premolar: forma con 2 cúspides -->
            <path
                d="M 14 10 L 26 10 L 28 14 L 27 18 L 28 22 L 26 26 L 24 28 L 22 26 L 20 28 L 18 26 L 16 28 L 14 26 L 12 22 L 13 18 L 12 14 Z"
                fill="none"
                stroke="#374151"
                stroke-width="1.5"
                stroke-linejoin="round"
            />
            <!-- Surco central -->
            <line x1="20" y1="12" x2="20" y2="24" stroke="#374151" stroke-width="0.5" stroke-opacity="0.4" />
            <!-- Línea cervical -->
            <line x1="14" y1="14" x2="26" y2="14" stroke="#374151" stroke-width="0.5" stroke-opacity="0.5" />

        @else
            <!-- Molar: forma compleja con 3-4 cúspides -->
            <path
                d="M 14 8 L 26 8 L 28 12 L 28 16 L 30 18 L 30 22 L 28 26 L 28 30 L 26 33 L 22 34 L 18 34 L 14 33 L 12 30 L 10 26 L 10 22 L 12 18 L 12 16 L 10 12 Z"
                fill="none"
                stroke="#374151"
                stroke-width="1.5"
                stroke-linejoin="round"
            />
            <!-- Cúspides principales -->
            <circle cx="18" cy="22" r="1.5" fill="#374151" fill-opacity="0.3" />
            <circle cx="22" cy="22" r="1.5" fill="#374151" fill-opacity="0.3" />
            <circle cx="16" cy="16" r="1.2" fill="#374151" fill-opacity="0.3" />
            <circle cx="24" cy="16" r="1.2" fill="#374151" fill-opacity="0.3" />
            <!-- Surcos -->
            <path d="M 18 14 Q 20 18 22 14" fill="none" stroke="#374151" stroke-width="0.5" stroke-opacity="0.4" />
            <path d="M 18 26 Q 20 22 22 26" fill="none" stroke="#374151" stroke-width="0.5" stroke-opacity="0.4" />
            <!-- Línea cervical -->
            <line x1="14" y1="12" x2="26" y2="12" stroke="#374151" stroke-width="0.5" stroke-opacity="0.5" />
        @endif
    </svg>
</div>
