<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomPrice extends Model
{
    protected $fillable = ['room_id', 'hours', 'total_price', 'price_per_hour', 'is_enabled'];

    public $timestamps = false;

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

}
