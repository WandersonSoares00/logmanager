<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeliAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'meli_user_id',
        'nickname',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'access_token' => 'encrypted', // Criptografa automaticamente ao salvar/descriptografa ao ler
        'refresh_token' => 'encrypted',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
