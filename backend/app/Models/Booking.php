<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'address_id', 'start_time', 'end_time', 'user_id', 'date', 'status_id', 'end_date', 'temporary_payment_link', 'temporary_payment_link_expires_at'
    ];

    public function status()
    {
        return $this->belongsTo(BookingStatus::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function charge()
    {
        return $this->hasOne(Charge::class, 'booking_id');
    }
}
