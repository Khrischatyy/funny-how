<?php

namespace App\Services;

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
}
