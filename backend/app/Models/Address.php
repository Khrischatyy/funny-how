<?php

namespace App\Models;

use App\Services\BookingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Address extends Model
{
    use HasFactory;

    protected $fillable = ['latitude', 'longitude', 'street', 'city_id', 'company_id'];

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

    public function prices()
    {
        return $this->hasMany(AddressPrice::class);
    }

    public function photos()
    {
        return $this->hasMany(AddressPhoto::class);
    }

    public function workingHours()
    {
        // Получаем операционные часы из BookingService
        $bookingService = new BookingService();
        return $bookingService->getOperatingHours($this->id, now());
    }
}
