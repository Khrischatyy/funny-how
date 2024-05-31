<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'address_id', 'start_time', 'end_time', 'user_id', 'total_cost', 'date', 'status_id',
    ];

    public function status()
    {
        return $this->belongsTo(BookingStatus::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

}
