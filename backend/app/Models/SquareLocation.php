<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SquareLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'address_id',
        'location_id',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}