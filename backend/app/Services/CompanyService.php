<?php

namespace App\Services;

use App\Http\Requests\AddressRequest;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Repositories\CompanyRepository;

class CompanyService
{
    public function __construct(public CompanyRepository $companyRepository)
    {}

    public function getCompany(string $slug)
    {
        $slug = mb_strtolower($slug);

        //TODO сделать исключение и обработать ошибку если компания не найдена

        return $this->companyRepository->getCompanyBySlug($slug);
    }


    public function createNewCompany(AddressRequest $companyRequest)
    {
        if($companyRequest->hasFile('logo')) {
            $path = $companyRequest->file('logo')->store('public/images');
        } else {
            $path = null;
        }

        return Company::create(['name' => $companyRequest->company, 'logo' => $path]);
    }
}
