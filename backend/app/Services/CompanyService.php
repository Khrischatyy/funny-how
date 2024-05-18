<?php

namespace App\Services;

use App\Http\Requests\AddressRequest;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Repositories\CompanyRepository;
use Illuminate\Support\Str;

class CompanyService
{
    public function __construct(public CompanyRepository $companyRepository)
    {}

    public function getCompany(string $slug)
    {
        return $this->companyRepository->getCompanyBySlug($slug);
    }


    public function createNewCompany(AddressRequest $companyRequest)
    {
        if($companyRequest->hasFile('logo')) {
            $path = $companyRequest->file('logo')->store('public/images', 's3');
        } else {
            $path = null;
        }

        $company = Company::create([
            'name' => $companyRequest->company,
            'logo' => $path,
            'slug' => Str::slug($companyRequest->company)]);

        return $company;
    }
}
