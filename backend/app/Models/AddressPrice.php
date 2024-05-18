<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressPrice extends Model
{
    protected $fillable = ['address_id', 'hours', 'total_price', 'price_per_hour', 'is_enabled'];

    public $timestamps = false;

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
