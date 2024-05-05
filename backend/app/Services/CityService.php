<?php

namespace App\Services;


use App\Http\Requests\CityRequest;
use App\Http\Requests\CountryRequest;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;

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
