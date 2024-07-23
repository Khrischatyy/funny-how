<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AddressPhoto extends Model
{
    use HasFactory;

    protected $table = 'address_photos';

    public $timestamps = false;

    protected $fillable = [
        'address_id', 'path', 'index'
    ];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}