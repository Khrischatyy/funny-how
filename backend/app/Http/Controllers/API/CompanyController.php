<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CompanyRequest;
use App\Models\Address;
use App\Models\Company;
use App\Repositories\CompanyRepository;
use App\Services\CompanyService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class CompanyController extends BaseController
{
    public function __construct(
        public CompanyRepository $companyRepository,
        public CompanyService $companyService,
    )
    {}

    public function getCompany(string $slug): JsonResponse
    {
        try {
            $company = $this->companyService->getCompany($slug);

            return $this->sendResponse($company, 'Company received successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 404);
        }
    }

    public function getRegisterCompany(string $slug): JsonResponse
    {
        try {
            $company = $this->companyService->getCompany($slug);

            // Проверяем, что пользователь имеет право на просмотр компании
            $this->authorize('view', $company);

            return $this->sendResponse($company, 'Company received successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 404);
        }
    }
}
