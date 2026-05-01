<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dentadura extends Model
{
    use HasFactory;

    protected $table = 'dentadura';

    protected $fillable = [
        'cliente_id', 'num_diente', 'estado', 'notas', 'fecha_actualizacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_actualizacion' => 'date',
        ];
    }

    // ─── Constantes FDI ───────────────────────────────────────────────────────

    const ESTADOS = [
        'sano'       => ['label' => 'Sano',       'color' => '#4ade80', 'icono' => '✓'],
        'picado'     => ['label' => 'Picado',      'color' => '#f97316', 'icono' => '⚠'],
        'caries'     => ['label' => 'Caries',      'color' => '#ef4444', 'icono' => 'C'],
        'partido'    => ['label' => 'Partido',     'color' => '#a855f7', 'icono' => '✗'],
        'caido'      => ['label' => 'Caído',       'color' => '#6b7280', 'icono' => '○'],
        'puente'     => ['label' => 'Puente',      'color' => '#3b82f6', 'icono' => 'P'],
        'sustituido' => ['label' => 'Sustituido',  'color' => '#eab308', 'icono' => 'S'],
    ];

    /**
     * Dientes adultos en orden visual (arcada superior izquierda → derecha, luego inferior)
     */
    const DIENTES_SUPERIORES = [18,17,16,15,14,13,12,11, 21,22,23,24,25,26,27,28];
    const DIENTES_INFERIORES = [48,47,46,45,44,43,42,41, 31,32,33,34,35,36,37,38];

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
