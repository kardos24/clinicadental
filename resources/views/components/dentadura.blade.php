@props([
    'cliente' => null,
    'showLegend' => true
])

@php
    // Mapa de dientes del cliente
    $dientesMapa = \App\Models\Dentadura::mapaCliente($cliente->id);

    // Listas de dientes FDI
    $superiores = \App\Models\Dentadura::DIENTES_SUPERIORES;
    $inferiores = \App\Models\Dentadura::DIENTES_INFERIORES;

    // Clasificación de dientes FDI por tipo
    $incisivos = [11, 12, 21, 22, 31, 32, 41, 42];
    $caninos = [13, 23, 33, 43];
    $premolares = [14, 15, 24, 25, 34, 35, 44, 45];
    // El resto son molares: 16,17,18,26,27,28,36,37,38,46,47,48

    // Función para determinar tipo de diente
    $getTipoDiente = function($numDiente) {
        if (in_array($numDiente, [11, 12, 21, 22, 31, 32, 41, 42])) return 'incisivo';
        if (in_array($numDiente, [13, 23, 33, 43])) return 'canino';
        if (in_array($numDiente, [14, 15, 24, 25, 34, 35, 44, 45])) return 'premolar';
        return 'molar';
    };

    // Función para obtener estado del diente
    $getEstadoDiente = function($numDiente, $dientesMapa) {
        return $dientesMapa[$numDiente]['estado'] ?? null;
    };

    // Mapeo de colores según estado
    $coloresEstado = [
        'sano' => '#10b981',      // Green-500
        'picado' => '#f59e0b',    // Amber-500
        'caries' => '#ef4444',    // Red-500
        'partido' => '#3b82f6',   // Blue-500
        'caido' => '#6b7280',     // Gray-500
        'puente' => '#8b5cf6',    // Violet-500
        'sustituido' => '#eab308', // Yellow-500
    ];

    $etiquetasEstado = [
        'sano' => 'Sano',
        'picado' => 'Con obturación',
        'caries' => 'Caries',
        'partido' => 'Corona/Puente',
        'caido' => 'Diente extraído',
        'puente' => 'Puente dental',
        'sustituido' => 'Diente sustituido',
    ];
@endphp

<div class="dentadura-container">
    @if($showLegend)
    <header class="dentadura-header">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Odontograma - {{ $cliente->nombre_completo }}</h3>
        <div class="leyenda-grid flex flex-wrap gap-3 mb-4">
            @foreach($coloresEstado as $estado => $color)
            <div class="leyenda-item flex items-center gap-2">
                <span class="diente-indicador" style="background-color: {{ $color }}; width: 20px; height: 20px; border-radius: 4px; border: 1px solid #9ca3af;"></span>
                <span class="text-sm text-gray-700">{{ $etiquetasEstado[$estado] }}</span>
            </div>
            @endforeach
            <div class="leyenda-item flex items-center gap-2">
                <span class="diente-indicador" style="background-color: #e2e8f0; width: 20px; height: 20px; border-radius: 4px; border: 1px solid #9ca3af;"></span>
                <span class="text-sm text-gray-700">Sin registrar</span>
            </div>
        </div>
    </header>
    @endif

    <div class="dentadura-grid">
        <!-- Arcada Superior -->
        <div class="dentadura-quadrant">
            <h4 class="quadrant-title text-sm font-medium text-gray-700 mb-2 text-center">Arcada Superior</h4>
            <div class="dientes-grid">
                @foreach($superiores as $numDiente)
                    @php
                        $tipo = $getTipoDiente($numDiente);
                        $estado = $getEstadoDiente($numDiente, $dientesMapa);
                        $color = $estado ? ($coloresEstado[$estado] ?? '#e2e8f0') : '#e2e8f0';
                    @endphp
                    <x-diente-svg
                        :id="$numDiente"
                        :tipo="$tipo"
                        :estado="$estado"
                        :color="$color"
                    ></x-diente-svg>
                @endforeach
            </div>
        </div>

        <!-- Arcada Inferior -->
        <div class="dentadura-quadrant">
            <h4 class="quadrant-title text-sm font-medium text-gray-700 mb-2 text-center">Arcada Inferior</h4>
            <div class="dientes-grid">
                @foreach($inferiores as $numDiente)
                    @php
                        $tipo = $getTipoDiente($numDiente);
                        $estado = $getEstadoDiente($numDiente, $dientesMapa);
                        $color = $estado ? ($coloresEstado[$estado] ?? '#e2e8f0') : '#e2e8f0';
                    @endphp
                    <x-diente-svg
                        :id="$numDiente"
                        :tipo="$tipo"
                        :estado="$estado"
                        :color="$color"
                    ></x-diente-svg>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .dentadura-container {
        background-color: #f8fafc;
        border-radius: 0.5rem;
        padding: 1.5rem;
        max-width: 800px;
        margin: 0 auto;
    }

    .dentadura-header {
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .leyenda-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .leyenda-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .diente-indicador {
        border: 1px solid #9ca3af;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .dentadura-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .dentadura-quadrant {
        background: white;
        border-radius: 0.5rem;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .quadrant-title {
        color: #374151;
        padding: 0.5rem;
        background: #f3f4f6;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .dientes-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
        justify-content: center;
        align-items: center;
    }

    .tooltip {
        position: absolute;
        background: #1f2937;
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        white-space: nowrap;
        z-index: 100;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s, transform 0.2s;
        transform: translateY(8px);
    }

    .diente:hover .tooltip {
        opacity: 1;
        transform: translateY(0);
    }

    @media (max-width: 640px) {
        .dentadura-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
