<?php

namespace App\Repositories\Interfaces;

interface CityRepositoryInterface
{
    public function getCitiesByCountryId(int $countryId);
}
