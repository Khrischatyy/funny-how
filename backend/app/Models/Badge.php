<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Badge extends Model
{
    protected $fillable = ['name', 'icon'];

    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'address_badge');
    }

    public function getImageUrlAttribute()
    {
        return Storage::disk('s3')->url($this->image);
    }
}
