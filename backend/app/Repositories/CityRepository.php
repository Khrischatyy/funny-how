<?php

namespace App\Repositories;

use App\Models\City;
use App\Repositories\Interfaces\CityRepositoryInterface;
use App\Repositories\Interfaces\CompanyRepositoryInterface;

class CityRepository implements CityRepositoryInterface
{
    public function getCitiesByCountryId(int $countryId)
    {
        return City::where('country_id', $countryId);
    }
}
