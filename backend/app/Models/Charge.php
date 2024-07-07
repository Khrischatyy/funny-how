<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'stripe_charge_id',
        'amount',
        'currency',
        'status',
        'refund_id',
        'refund_status',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
