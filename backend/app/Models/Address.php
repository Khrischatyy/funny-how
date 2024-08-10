<?php

namespace App\Models;

use App\Services\BookingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Address extends Model
{
    use HasFactory;

    protected $fillable = ['latitude', 'longitude', 'street', 'city_id', 'company_id', 'is_favorite', 'slug', 'timezone', 'available_balance'];

    protected $appends = ['is_favorite', 'is_complete', 'prices', 'photos'];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function getPricesAttribute()
    {
        return $this->rooms->flatMap(function ($room) {
            return $room->prices;
        });
    }

    public function getIsFavoriteAttribute()
    {
        $userId = Auth::id();
        return $this->favoriteByUsers()->where('user_id', $userId)->exists();
    }

    public function getIsCompleteAttribute()
    {
        return true;
        $hasOperatingHours = $this->operatingHours()->exists();

        $hasPaymentGateway = $this->company->adminCompany->user->payment_gateway !== null;

        return $hasOperatingHours && $hasPaymentGateway;
    }

    public function getPhotosAttribute()
    {
        return $this->rooms->flatMap(function ($room) {
            return $room->photos;
        });
    }

    public function equipments()
    {
        return $this->belongsToMany(Equipment::class, 'address_equipment', 'address_id', 'equipment_id')
            ->withPivot('address_id', 'equipment_id');
    }

    public function engineers()
    {
        return $this->belongsToMany(User::class, 'engineer_addresses', 'address_id', 'user_id')->select('firstname', 'lastname', 'username', 'profile_photo');
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

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function operatingHours()
    {
        return $this->hasMany(OperatingHour::class);
    }

    public function favoriteByUsers()
    {
        return $this->belongsToMany(User::class, 'favorite_studios', 'address_id', 'user_id');
    }

    public function squareLocation()
    {
        return $this->hasOne(SquareLocation::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($address) {
            $address->slug = $address->generateUniqueSlug();
        });
    }

    public function generateUniqueSlug()
    {
        $company = $this->company()->first();
        $city = $this->city()->first();
        $slugBase = Str::slug($company->name . ' ' . $city->name);
        $slug = $slugBase;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
