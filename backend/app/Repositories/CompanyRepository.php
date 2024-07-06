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
            }, 'addresses.prices', 'addresses.badges', 'addresses.photos', 'addresses.operatingHours'])
            ->firstOrFail();

        $company->addresses->each(function ($address) {
            $address->badges->each(function ($badge) {
                $badge->image_url = $badge->getImageAttribute($badge->image);
            });
        });

        if ($company->logo) {
            $company->logo_url = Storage::disk('s3')->url($company->logo);
        } else {
            $company->logo_url = null;
        }

        return $company;
    }
}
