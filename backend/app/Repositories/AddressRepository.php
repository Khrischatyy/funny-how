<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\City;
use App\Repositories\Interfaces\AddressRepositoryInterface;

class AddressRepository implements AddressRepositoryInterface
{
    public function getAddressByCityId(int $cityId)
    {
        return Address::where('city_id', $cityId)->with(['company', 'badges']);
    }
}
