<?php

namespace App\Services;

use App\Models\City;

class CityService
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    public function findOrCreateCity($city, $country)
    {
        $country = $this->countryService->findOrCreateCountry($country);

        return City::firstOrCreate([
            'name' => strtolower($city),
            'country_id' => $country->id
        ]);
    }
}
