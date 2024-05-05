<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Repositories\CompanyRepository;
use App\Services\CompanyService;

class CompanyController extends BaseController
{
    public function __construct(
        public CompanyRepository $companyRepository,
        public CompanyService $companyService,
    )
    {}

    public function getCompaniesByCityId($cityId): \Illuminate\Http\JsonResponse
    {
        $companies = $this->companyRepository->getCompaniesByCityId($cityId);

        return $this->sendResponse($companies, 'Companies received');
    }

    public function getCompany(string $slug): \Illuminate\Http\JsonResponse
    {
        $company = $this->companyService->getCompany($slug);

        return $this->sendResponse($company, 'Company received');
    }

    public function getCompanyAddressesInCity(int $cityId, int $companyId)
    {
        return Company::where('id', $companyId)->with(['addresses' => function($q){
            $q->where('city_id', 1)->with('badges');
        }])->get();
    }

    public function createNewCompany(CompanyRequest $companyRequest)
    {
        if($companyRequest->hasFile('logo')) {
            $path = $companyRequest->file('logo')->store('public/images');
        } else {
            $path = null;
        }

        $company = Company::create(['name' => $companyRequest->name, 'logo' => $path]);

        return $this->sendResponse($company, 'Company successfully created');
    }
}
