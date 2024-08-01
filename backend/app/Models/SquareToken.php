<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SquareToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // или другое поле для ассоциации
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $dates = ['expires_at'];

    public function user() // пример для связывания с моделью User
    {
        return $this->belongsTo(User::class);
    }
}