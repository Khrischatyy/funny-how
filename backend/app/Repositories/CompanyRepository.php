<?php

namespace App\Repositories;

use App\Exceptions\OperatingHourException;
use App\Models\City;
use App\Models\Company;
use App\Repositories\Interfaces\CompanyRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CompanyRepository implements CompanyRepositoryInterface
{
    /**
     * Get companies by city ID with their addresses and badges.
     *
     * @param int $cityId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getCompaniesByCityId(int $cityId)
    {
        $city = City::whereId($cityId)
            ->with(['companies.addresses.badges' => function ($query) {
                $query->select(['badges.*']);
            }])
            ->paginate();

        // Map badges to include the S3 URLs and hide the pivot attribute
        $city->companies->each(function ($company) {
            $company->addresses->each(function ($address) {
                $address->badges->each(function ($badge) {
                    $badge->image_url = $badge->getImageUrlAttribute();
                    $badge->makeHidden('pivot');
                });
            });
        });

        return $city;
    }

    public function getCompanyBySlug(string $slug)
    {
        $company = Company::where('slug', $slug)
            ->with(['addresses.badges' => function ($query) {
                $query->select(['badges.*']);
            }])
            ->firstOrFail();


        $company->addresses->each(function ($address) {
            $address->badges->each(function ($badge) {
                $badge->image_url = $badge->getImageUrlAttribute();
            });
        });

        $company->logo_url = Storage::disk('s3')->url($company->logo);

        return $company;
    }
}
