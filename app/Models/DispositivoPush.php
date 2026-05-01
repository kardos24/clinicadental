<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispositivoPush extends Model
{
    protected $table = 'dispositivos_push';

    protected $fillable = ['user_id', 'token_fcm', 'plataforma'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
