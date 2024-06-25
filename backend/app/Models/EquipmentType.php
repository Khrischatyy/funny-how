<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EquipmentType extends Model
{
    use HasFactory;

    protected $table = 'equipment_type';

    protected $fillable = ['name', 'icon'];

    /**
     * Get the equipments for this type.
     */
    public function equipments()
    {
        return $this->hasMany(Equipment::class, 'equipment_type_id', 'id');
    }

    public function getIconAttribute($value)
    {
        return Storage::disk('s3')->url($value);
    }
}