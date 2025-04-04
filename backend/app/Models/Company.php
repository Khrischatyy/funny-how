<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'logo', 'slug'];
    protected $appends = ['logo_url'];

    public function cities(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(City::class,'company_city');
    }

    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function adminCompany()
    {
        return $this->hasOne(AdminCompany::class, 'company_id');
    }

    public function getLogoUrlAttribute()
    {
        if($this->logo)
            return Storage::disk('s3')->url($this->logo);
        else
            return '';
    }
}
