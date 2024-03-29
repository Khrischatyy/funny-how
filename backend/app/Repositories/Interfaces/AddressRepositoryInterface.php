<?php

namespace App\Repositories\Interfaces;

interface AddressRepositoryInterface
{
    public function getAddressByCityId(int $cityId);
    public function getAddressByCompanyId(int $companyId);
}
