<?php

namespace App\Models;

use App\Services\BookingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Room extends Model
{

    use HasFactory;

    protected $fillable = ['name', 'address_id'];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function prices()
    {
        return $this->hasMany(RoomPrice::class)->where('is_enabled', true);
    }

    public function photos()
    {
        return $this->hasMany(RoomPhoto::class, 'room_id', 'id');
    }
}
