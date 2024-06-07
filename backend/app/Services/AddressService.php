<?php

namespace App\Services;

use App\Http\Requests\AddressRequest;
use App\Models\Address;

class AddressService
{
    public function createAddress(AddressRequest $addressRequest, $city, $company)
    {
        return Address::create([
            'street' => $addressRequest->street,
            'longitude' => $addressRequest->longitude,
            'latitude' => $addressRequest->latitude,
            'city_id' => $city->id,
            'company_id' => $company->id,
        ]);
    }
}
