<?php

namespace App\Services;


use App\Http\Requests\CountryRequest;
use App\Models\Country;

class CountryService
{
    public function findOrCreateCountry($country)
    {
        $country = strtolower($country);

        return Country::firstOrCreate([
            'name' => $country,
        ]);
    }
}
