<?php

namespace App\Repositories;

use App\Exceptions\OperatingHourException;
use App\Models\City;
use App\Models\Company;
use App\Repositories\Interfaces\CompanyRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class CompanyRepository implements CompanyRepositoryInterface
{
    public function getCompaniesByCityId(int $cityId)
    {
        return City::whereId($cityId)->with('companies.addresses.badges')->paginate();
    }

    public function getCompanyBySlug(string $slug)
    {
        $company = Company::where('slug', $slug)
            ->with(['addresses' => function($q) {
                $q->with('badges');
            }])
            ->firstOrFail();

        $company->logo_url = Storage::disk('s3')->url($company->logo);

        return $company;
    }
}
