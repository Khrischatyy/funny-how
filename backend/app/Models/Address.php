<?php

namespace App\Models;

use App\Services\BookingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @method static findOrFail(int $addressId)
 * @method static create(array $array)
 */
class Address extends Model
{

    use HasFactory;

    protected $fillable = ['latitude', 'longitude', 'street', 'city_id', 'company_id', 'is_favorite'];

    protected $appends = ['is_favorite'];

    public function equipments()
    {
        return $this->belongsToMany(Equipment::class, 'address_equipment', 'address_id', 'equipment_id')
            ->withPivot('address_id', 'equipment_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'address_badge', 'address_id', 'badge_id')
            ->withPivot('address_id', 'badge_id');
    }

    public function prices()
    {
        return $this->hasMany(AddressPrice::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function photos()
    {
        return $this->hasMany(AddressPhoto::class, 'address_id', 'id');
    }

    public function operatingHours()
    {
        return $this->hasMany(OperatingHour::class);
    }

    public function getIsFavoriteAttribute()
    {
        $userId = Auth::id();
        return $this->favoriteByUsers()->where('user_id', $userId)->exists();
    }

    public function favoriteByUsers()
    {
        return $this->belongsToMany(User::class, 'favorite_studios', 'address_id', 'user_id');
    }
}
