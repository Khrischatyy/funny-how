<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id', 'start_time', 'end_time', 'user_id', 'date', 'status_id', 'end_date', 'temporary_payment_link', 'temporary_payment_link_expires_at'
    ];

    public function status()
    {
        return $this->belongsTo(BookingStatus::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function address(){
        return $this->room->address;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

//    public function getDateAttribute($value)
//    {
//        return Carbon::parse($value)->format('j F');
//    }

    public function charge()
    {
        return $this->hasOne(Charge::class, 'booking_id');
    }
}
