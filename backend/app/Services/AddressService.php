<?php

namespace App\Services;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Models\AdminCompany;
use Illuminate\Support\Facades\Auth;

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

    public function getMyAddresses(): Collection
    {
        $user = Auth::user();
        $companies = AdminCompany::where('admin_id', $user->id)->pluck('company_id');
        return Address::whereIn('company_id', $companies)->get();
    }
}
