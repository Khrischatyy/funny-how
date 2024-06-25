<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Support\Facades\Storage;

class CompanyRepository
{
    public function getCompanyBySlug(string $slug)
    {
        $company = Company::where('slug', mb_strtolower($slug))
            ->with(['addresses.badges' => function ($query) {
                $query->select(['badges.*']);
            }])
            ->firstOrFail();


        $company->addresses->each(function ($address) {
            $address->badges->each(function ($badge) {
                $badge->image_url = $badge->getImageAttribute($badge->image);
            });
        });

        $company->logo_url = '/logo.png';
            //TODO:Включить когда оплатим AWS
        //$company->logo_url = $company->logoStorage::disk('s3')->url($company->logo);

        return $company;
    }
}
