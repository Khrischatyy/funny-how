<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RoomPhoto extends Model
{
    use HasFactory;

    protected $table = 'room_photos';

    public $timestamps = false;

    protected $fillable = [
        'room_id', 'path', 'index'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}