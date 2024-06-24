<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Badge extends Model
{
    protected $fillable = ['name', 'image'];

    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'address_badge');
    }

    public function getImageAttribute($value)
    {
        return Storage::disk('s3')->url($value);
    }
}