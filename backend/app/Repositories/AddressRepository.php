<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\City;
use App\Models\Company;
use App\Models\OperatingHour;
use App\Repositories\Interfaces\AddressRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class AddressRepository implements AddressRepositoryInterface
{
    public function getAddressByCityId(int $cityId): Collection
    {
        return Address::where('city_id', $cityId)
            ->with(['badges', 'photos', 'prices', 'company', 'operatingHours'])
            ->get();
    }

    public function getMyAddresses(int $companyId): Collection
    {
        return Address::where('company_id', $companyId)
            ->with(['badges', 'photos', 'prices', 'company', 'operatingHours'])
            ->get();
    }

    public function getAddressesByCompanySlug(string $slug): Collection
    {
        $company = Company::where('slug', $slug)->firstOrFail();

        return Address::where('company_id', $company->id)
            ->with(['badges', 'photos', 'prices', 'company', 'operatingHours'])
            ->get();
    }

    public function getAddressById(int $addressId): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder
    {
        return Address::with(['badges', 'photos', 'prices', 'company', 'operatingHours'])
            ->findOrFail($addressId);
    }

}
