<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dentadura extends Model
{
    use HasFactory;

    protected $table = 'dentadura';

    protected $fillable = [
        'cliente_id',
        'num_diente',
        'estado_pieza',
        'cara_vestibular',
        'cara_lingual',
        'cara_mesial',
        'cara_distal',
        'cara_oclusal',
        'notas',
        'fecha_actualizacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_actualizacion' => 'date',
        ];
    }

    // ─── Constantes FDI ───────────────────────────────────────────────────────

    /**
     * Estados de la pieza dental completa.
     * Determinan el estado global del diente (si está presente, ausente, etc.).
     */
    const ESTADOS_PIEZA = [
        'presente'   => ['label' => 'Presente',   'color' => '#4ade80', 'icono' => '✓'],
        'ausente'    => ['label' => 'Ausente',    'color' => '#6b7280', 'icono' => '○'],
        'corona'     => ['label' => 'Corona',     'color' => '#a855f7', 'icono' => '♛'],
        'puente'     => ['label' => 'Puente',     'color' => '#3b82f6', 'icono' => 'P'],
        'implante'   => ['label' => 'Implante',   'color' => '#eab308', 'icono' => 'I'],
        'endodoncia' => ['label' => 'Endodoncia', 'color' => '#f97316', 'icono' => 'E'],
    ];

    /**
     * Estados posibles de cada cara del diente.
     * null significa cara sana / sin patología registrada.
     */
    const ESTADOS_CARA = [
        'sano'       => ['label' => 'Sano',       'color' => '#4ade80'],
        'caries'     => ['label' => 'Caries',     'color' => '#ef4444'],
        'obturacion' => ['label' => 'Obturación', 'color' => '#f97316'],
        'fractura'   => ['label' => 'Fractura',   'color' => '#a855f7'],
        'sellante'   => ['label' => 'Sellante',   'color' => '#06b6d4'],
        'desgaste'   => ['label' => 'Desgaste',   'color' => '#78716c'],
    ];

    /**
     * Caras del diente con terminología dental.
     *
     * - Vestibular: cara exterior (hacia labios en anteriores, hacia mejillas en posteriores)
     * - Lingual:    cara interior (hacia lengua en inferiores, "palatino" en superiores)
     * - Mesial:     cara lateral proximal hacia la línea media dental
     * - Distal:     cara lateral proximal opuesta a la línea media
     * - Oclusal:    superficie de masticación (solo premolares y molares; los incisivos/caninos
     *               tienen borde incisal que no se registra como cara con estado)
     */
    const CARAS = [
        'vestibular' => ['label' => 'Vestibular', 'descripcion' => 'Cara exterior (labial/bucal)'],
        'lingual'    => ['label' => 'Lingual',    'descripcion' => 'Cara interior (lingual/palatino)'],
        'mesial'     => ['label' => 'Mesial',     'descripcion' => 'Lateral hacia línea media'],
        'distal'     => ['label' => 'Distal',     'descripcion' => 'Lateral opuesto a línea media'],
        'oclusal'    => ['label' => 'Oclusal',    'descripcion' => 'Superficie de masticación'],
    ];

    /**
     * Dientes adultos en orden visual (arcada superior izquierda → derecha, luego inferior)
     */
    const DIENTES_SUPERIORES = [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28];
    const DIENTES_INFERIORES = [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38];

    /**
     * Dientes que tienen cara oclusal (premolares y molares).
     * Los incisivos (x1, x2) y caninos (x3) tienen borde incisal, no cara oclusal.
     */
    const DIENTES_CON_OCLUSAL = [
        14, 15, 16, 17, 18,
        24, 25, 26, 27, 28,
        34, 35, 36, 37, 38,
        44, 45, 46, 47, 48,
    ];

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Devuelve las caras aplicables a este diente.
     * Incisivos y caninos no tienen cara oclusal.
     */
    public function carasAplicables(): array
    {
        $caras = ['vestibular', 'lingual', 'mesial', 'distal'];

        if (in_array((int) $this->num_diente, self::DIENTES_CON_OCLUSAL)) {
            $caras[] = 'oclusal';
        }

        return $caras;
    }

    /**
     * Comprueba si este diente tiene cara oclusal.
     */
    public function tieneOclusal(): bool
    {
        return in_array((int) $this->num_diente, self::DIENTES_CON_OCLUSAL);
    }

    /**
     * Devuelve el estado de una cara concreta (o 'sano' si es null).
     */
    public function estadoCara(string $cara): string
    {
        $columna = "cara_{$cara}";

        return $this->{$columna} ?? 'sano';
    }

    /**
     * Devuelve el color correspondiente al estado de una cara.
     */
    public function colorCara(string $cara): string
    {
        $estado = $this->estadoCara($cara);

        return self::ESTADOS_CARA[$estado]['color'] ?? '#e2e8f0';
    }

    /**
     * Devuelve true si el diente está presente (no ausente).
     * Trata null como 'presente' (valor por defecto en BD).
     */
    public function estaPresente(): bool
    {
        return ($this->estado_pieza ?? 'presente') === 'presente';
    }

    /**
     * Determina si alguna cara tiene patología.
     */
    public function tienePatologia(): bool
    {
        foreach ($this->carasAplicables() as $cara) {
            $estado = $this->estadoCara($cara);
            if ($estado !== 'sano') {
                return true;
            }
        }

        return false;
    }

    /**
     * Devuelve un resumen del estado del diente para tooltips.
     */
    public function resumen(): string
    {
        $estadoPieza = $this->estado_pieza ?? 'presente';
        $pieza = self::ESTADOS_PIEZA[$estadoPieza]['label'] ?? $estadoPieza;

        if ($estadoPieza !== 'presente') {
            return $pieza;
        }

        $caras = [];
        foreach ($this->carasAplicables() as $cara) {
            $estado = $this->estadoCara($cara);
            if ($estado !== 'sano') {
                $label = self::CARAS[$cara]['label'];
                $estadoLabel = self::ESTADOS_CARA[$estado]['label'] ?? $estado;
                $caras[] = "{$label}: {$estadoLabel}";
            }
        }

        return $caras ? implode(' | ', $caras) : 'Sano';
    }

    // ─── Compatibilidad temporal ──────────────────────────────────────────────

    /**
     * Mantiene compatibilidad con código que use la antigua constante ESTADOS.
     * @deprecated Usar ESTADOS_PIEZA + ESTADOS_CARA
     */
    const ESTADOS = [
        'sano'       => ['label' => 'Sano',       'color' => '#4ade80', 'icono' => '✓'],
        'picado'     => ['label' => 'Picado',      'color' => '#f97316', 'icono' => '⚠'],
        'caries'     => ['label' => 'Caries',      'color' => '#ef4444', 'icono' => 'C'],
        'partido'    => ['label' => 'Partido',     'color' => '#a855f7', 'icono' => '✗'],
        'caido'      => ['label' => 'Caído',       'color' => '#6b7280', 'icono' => '○'],
        'puente'     => ['label' => 'Puente',      'color' => '#3b82f6', 'icono' => 'P'],
        'sustituido' => ['label' => 'Sustituido',  'color' => '#eab308', 'icono' => 'S'],
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // ─── Métodos estáticos ────────────────────────────────────────────────────

    /**
     * Devuelve mapa [num_diente => modelo] para un cliente
     */
    public static function mapaCliente(int $clienteId): array
    {
        return static::where('cliente_id', $clienteId)
                     ->get()
                     ->keyBy('num_diente')
                     ->toArray();
    }
}
