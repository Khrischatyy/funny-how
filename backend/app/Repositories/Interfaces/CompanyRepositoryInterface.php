<?php

namespace App\Repositories\Interfaces;

interface CompanyRepositoryInterface
{
    public function getCompaniesByCityId(int $cityId);
}
