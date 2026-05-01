<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'num_filiacion', 'apellidos', 'nombre', 'edad',
        'profesion', 'direccion', 'cp', 'telefono',
        'observaciones', 'user_id',
    ];

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->apellidos}, {$this->nombre}";
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function historialClinico()
    {
        return $this->hasMany(HistorialClinico::class)
                    ->orderBy('anio', 'desc')
                    ->orderBy('mes', 'desc')
                    ->orderBy('dia', 'desc');
    }

    public function dentadura()
    {
        return $this->hasMany(Dentadura::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class)->orderBy('fecha_hora', 'desc');
    }

    public function citasFuturas()
    {
        return $this->hasMany(Cita::class)
                    ->where('fecha_hora', '>=', now())
                    ->whereIn('estado', ['pendiente', 'confirmada'])
                    ->orderBy('fecha_hora');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('apellidos', 'like', "%{$termino}%")
              ->orWhere('nombre', 'like', "%{$termino}%")
              ->orWhere('num_filiacion', 'like', "%{$termino}%")
              ->orWhere('telefono', 'like', "%{$termino}%");
        });
    }

    // ─── Métodos de negocio ───────────────────────────────────────────────────

    public function getSaldoTotalAttribute(): float
    {
        return (float) $this->historialClinico()->sum('saldo');
    }

    public function generarNumFiliacion(): string
    {
        return 'MUL-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }
}
