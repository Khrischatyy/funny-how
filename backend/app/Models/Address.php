<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = ['latitude', 'longitude', 'street', 'city_id', 'company_id', 'works_till', 'works_since'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function equipment()
    {
        return $this->belongsToMany(Equipment::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'address_badge');
    }
}
