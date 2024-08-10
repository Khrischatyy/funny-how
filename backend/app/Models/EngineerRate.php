<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EngineerRate extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'rate_per_hour'];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}