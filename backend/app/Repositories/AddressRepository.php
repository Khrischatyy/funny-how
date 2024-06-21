<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\City;
use App\Models\OperatingHour;
use App\Repositories\Interfaces\AddressRepositoryInterface;
use Illuminate\Support\Collection;

class AddressRepository implements AddressRepositoryInterface
{
    public function getAddressByCityId(int $cityId)
    {
        return Address::where('city_id', $cityId)
            ->with(['company', 'badges', 'photos', 'prices'])
            ->get();
    }

    public function getMyAddresses(int $companyId): Collection
    {
        return Address::where('company_id', $companyId)
            ->with(['badges', 'photos', 'prices'])
            ->get();
    }

    public function getOperatingHoursByAddressIds(Collection $addressIds): Collection
    {
        return OperatingHour::whereIn('address_id', $addressIds)->get()->groupBy('address_id');
    }
}
