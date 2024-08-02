<?php

namespace App\Models;

use App\Services\BookingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * @method static findOrFail(int $addressId)
 * @method static create(array $array)
 */
class Address extends Model
{

    use HasFactory;

    protected $fillable = ['latitude', 'longitude', 'street', 'city_id', 'company_id', 'is_favorite', 'slug', 'timezone', 'available_balance'];

    protected $appends = ['is_favorite', 'is_complete', 'stripe_account_id'];

    protected $casts = [
        'square_capabilities' => 'array',
    ];

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
        return $this->hasMany(AddressPrice::class)->where('is_enabled', true);
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

    public function getIsCompleteAttribute()
    {
        $hasPrices = $this->prices()->exists();
        $hasOperatingHours = $this->operatingHours()->exists();
        $hasStripeAccountId = $this->stripe_account_id !== null;
        
        return $hasPrices && $hasOperatingHours && ($hasStripeAccountId || $this->company->adminCompany->user->payment_gateway === 'square');
    }

    public function getStripeAccountIdAttribute()
    {
        return $this->company->adminCompany->user->stripe_account_id;
    }

    public function favoriteByUsers()
    {
        return $this->belongsToMany(User::class, 'favorite_studios', 'address_id', 'user_id');
    }

    public function squareLocations()
    {
        return $this->hasMany(SquareLocation::class);
    }

    public function squareFirstLocation()
    {
        return $this->squareLocations()->first();
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
