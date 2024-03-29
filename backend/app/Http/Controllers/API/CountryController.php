<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Repositories\CountryRepository;

class CountryController extends BaseController
{
    public function getCountries(CountryRepository $countryRepository): \Illuminate\Http\JsonResponse
    {
        $countries = $countryRepository->all();

        return $this->sendResponse($countries, 'Countries received');
    }
}
