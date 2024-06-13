<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AddressPhoto extends Model
{
    use HasFactory;

    protected $table = 'address_photos';

    protected $appends = ['url'];

    protected $fillable = [
        'address_id', 'path', 'index'
    ];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function getUrlAttribute()
    {
        return Storage::disk('s3')->url($this->path);
    }
}