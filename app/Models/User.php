<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'activo',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    // ─── Helpers de rol ───────────────────────────────────────────────────────

    public function isGestor(): bool
    {
        return $this->role === 'gestor';
    }

    public function isCliente(): bool
    {
        return $this->role === 'cliente';
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }

    public function dispositivosPush()
    {
        return $this->hasMany(DispositivoPush::class);
    }
}
