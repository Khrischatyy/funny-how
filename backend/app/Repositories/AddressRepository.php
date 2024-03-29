<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\City;
use App\Repositories\Interfaces\AddressRepositoryInterface;

class AddressRepository implements AddressRepositoryInterface
{
    public function getAddressByCityId(int $cityId)
    {
        return Address::where('city_id', $cityId)->with('company');
    }

    public function getAddressByCompanyId(int $companyId)
    {
        return Address::where('company_id', $companyId)->with(['company', 'bookings'])->first();
    }
}
