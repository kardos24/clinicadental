<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialClinico extends Model
{
    use HasFactory;

    protected $table = 'historial_clinico';

    protected $fillable = [
        'cliente_id', 'dia', 'mes', 'anio', 'signo', 'diagnostico',
        'num_sesiones', 'importe', 'tratamiento_realizado', 'recibo',
        'debe', 'haber', 'saldo', 'gestor_id',
    ];

    protected function casts(): array
    {
        return [
            'importe' => 'decimal:2',
            'debe'    => 'decimal:2',
            'haber'   => 'decimal:2',
            'saldo'   => 'decimal:2',
        ];
    }

    // ─── Boot: calcular saldo automáticamente ─────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $registro) {
            $registro->saldo = $registro->debe - $registro->haber;
        });
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getFechaFormateadaAttribute(): string
    {
        return "{$this->dia}/{$this->mes}/{$this->anio}";
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }
}
