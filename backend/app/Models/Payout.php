<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payout_id',
        'amount',
        'currency',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}