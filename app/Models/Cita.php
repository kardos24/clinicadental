<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';

    protected $fillable = [
        'cliente_id', 'fecha_hora', 'duracion_minutos', 'motivo',
        'estado', 'notas', 'recordatorio_enviado', 'gestor_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora'          => 'datetime',
            'recordatorio_enviado'=> 'boolean',
        ];
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getFechaHoraFormateadaAttribute(): string
    {
        return $this->fecha_hora->format('d/m/Y H:i');
    }

    public function getEstadoLabelAttribute(): string
    {
        return match($this->estado) {
            'pendiente'    => 'Pendiente',
            'confirmada'   => 'Confirmada',
            'cancelada'    => 'Cancelada',
            'realizada'    => 'Realizada',
            'no_presentado'=> 'No se presentó',
            default        => $this->estado,
        };
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'pendiente'    => '#f97316',
            'confirmada'   => '#3b82f6',
            'cancelada'    => '#ef4444',
            'realizada'    => '#4ade80',
            'no_presentado'=> '#6b7280',
            default        => '#6b7280',
        };
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

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeProximas($query)
    {
        return $query->where('fecha_hora', '>=', now())
                     ->whereIn('estado', ['pendiente', 'confirmada'])
                     ->orderBy('fecha_hora');
    }

    public function scopeDelDia($query, Carbon $fecha)
    {
        return $query->whereDate('fecha_hora', $fecha->toDateString())
                     ->orderBy('fecha_hora');
    }

    public function scopeDelMes($query, int $year, int $month)
    {
        return $query->whereYear('fecha_hora', $year)
                     ->whereMonth('fecha_hora', $month)
                     ->orderBy('fecha_hora');
    }
}
